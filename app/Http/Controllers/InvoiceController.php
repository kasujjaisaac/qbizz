<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('invoices.index', [
            'activeInvoices' => $user->invoices()
                ->open()
                ->withCount(['items', 'receipts'])
                ->latest('issue_date')
                ->latest('id')
                ->paginate(10),
            'activeTotal' => (float) $user->invoices()->open()->sum(DB::raw('total_amount - paid_amount')),
            'activeCount' => $user->invoices()->open()->count(),
        ]);
    }

    public function settled(Request $request): View
    {
        $user = $request->user();

        return view('invoices.settled', [
            'settledInvoices' => $user->invoices()
                ->settled()
                ->withCount(['items', 'receipts'])
                ->with('latestReceipt')
                ->latest('settled_at')
                ->latest('id')
                ->paginate(10),
            'settledTotal' => (float) $user->receipts()
                ->whereHas('invoice', fn ($query) => $query->settled())
                ->sum('amount_received'),
            'settledCount' => $user->invoices()->settled()->count(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('invoices.create', [
            'invoice' => new Invoice([
                'invoice_number' => $this->nextInvoiceNumber($request->user()),
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
            ]),
        ]);
    }

    public function store(InvoiceRequest $request): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request): Invoice {
            $user = $request->user();
            $payload = $request->validated();
            $items = $payload['items'];
            unset($payload['items']);

            $invoice = $user->invoices()->create([
                ...$payload,
                'business_profile_id' => $user->businessProfile->id,
                'status' => Invoice::STATUS_ACTIVE,
                'total_amount' => $this->calculateTotal($items),
            ]);

            $this->syncItems($invoice, $items);

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Invoice created successfully.');
    }

    public function show(Request $request, Invoice $invoice): View
    {
        $this->ensureOwnership($request, $invoice);

        $invoice->load(['items', 'businessProfile', 'receipts']);

        return view('invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Request $request, Invoice $invoice): View|RedirectResponse
    {
        $this->ensureOwnership($request, $invoice);

        if ($invoice->isSettled() || $invoice->hasPayments()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('status', 'Invoices with recorded payments are kept as records and can no longer be edited.');
        }

        $invoice->load('items');

        return view('invoices.edit', [
            'invoice' => $invoice,
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureOwnership($request, $invoice);

        if ($invoice->isSettled() || $invoice->hasPayments()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('status', 'Invoices with recorded payments are kept as records and can no longer be edited.');
        }

        DB::transaction(function () use ($request, $invoice): void {
            $payload = $request->validated();
            $items = $payload['items'];
            unset($payload['items']);

            $invoice->update([
                ...$payload,
                'total_amount' => $this->calculateTotal($items),
            ]);

            $this->syncItems($invoice, $items);
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Invoice updated successfully.');
    }

    public function settle(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureOwnership($request, $invoice);

        if ($invoice->isSettled()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('status', 'This invoice is already settled.');
        }

        return redirect()
            ->route('receipts.create', ['invoice' => $invoice->id])
            ->with('status', 'Issue a receipt to record payment for this invoice.');
    }

    public function print(Request $request, Invoice $invoice): View
    {
        $this->ensureOwnership($request, $invoice);

        $invoice->load(['items', 'businessProfile', 'receipts']);

        return view('invoices.print', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * @param  array<int, array{description: string, quantity: numeric-string|int|float, unit_price: numeric-string|int|float}>  $items
     */
    protected function calculateTotal(array $items): float
    {
        return collect($items)->sum(function (array $item): float {
            return (float) $item['quantity'] * (float) $item['unit_price'];
        });
    }

    /**
     * @param  array<int, array{description: string, quantity: numeric-string|int|float, unit_price: numeric-string|int|float}>  $items
     */
    protected function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        $invoice->items()->createMany(
            collect($items)->values()->map(function (array $item, int $index): array {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];

                return [
                    'description' => $item['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $quantity * $unitPrice,
                    'position' => $index + 1,
                ];
            })->all()
        );
    }

    protected function nextInvoiceNumber(User $user): string
    {
        $latestInvoiceNumber = $user->invoices()->latest('id')->value('invoice_number');

        if ($latestInvoiceNumber && preg_match('/(\d+)$/', $latestInvoiceNumber, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = $user->invoices()->count() + 1;
        }

        return sprintf('INV-%s-%04d', now()->format('Y'), $nextNumber);
    }

    protected function ensureOwnership(Request $request, Invoice $invoice): void
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);
    }
}
