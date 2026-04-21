<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('quotations.index', [
            'quotations' => $user->quotations()
                ->withCount('items')
                ->with('convertedInvoice')
                ->latest('issue_date')
                ->latest('id')
                ->paginate(10),
            'quotationCount' => $user->quotations()->count(),
            'pendingCount' => $user->quotations()->pending()->count(),
            'acceptedCount' => $user->quotations()->accepted()->count(),
            'convertedCount' => $user->quotations()->converted()->count(),
            'quotedTotal' => (float) $user->quotations()->sum('total_amount'),
        ]);
    }

    public function create(Request $request): View
    {
        $businessProfile = $request->user()->businessProfile;

        return view('quotations.create', [
            'quotation' => new Quotation([
                'quotation_number' => $businessProfile ? $this->nextQuotationNumber($businessProfile) : '',
                'issue_date' => now()->toDateString(),
                'valid_until' => now()->addDays(14)->toDateString(),
            ]),
        ]);
    }

    public function store(QuotationRequest $request): RedirectResponse
    {
        $quotation = DB::transaction(function () use ($request): Quotation {
            $user = $request->user();
            $businessProfile = $this->lockBusinessProfileForUpdate($user);
            $payload = $request->validated();
            $items = $payload['items'];
            unset($payload['items']);
            $payload['quotation_number'] = $request->usesAutomaticQuotationNumber()
                ? $this->nextQuotationNumber($businessProfile)
                : (string) $payload['quotation_number'];

            $quotation = Quotation::create([
                ...$payload,
                'user_id' => $user->id,
                'business_profile_id' => $businessProfile->id,
                'status' => Quotation::STATUS_DRAFT,
                'total_amount' => $this->calculateTotal($items),
            ]);

            $this->syncItems($quotation, $items);

            return $quotation;
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation created successfully.');
    }

    public function show(Request $request, Quotation $quotation): View
    {
        $this->ensureOwnership($request, $quotation);

        $quotation->load(['items', 'businessProfile', 'convertedInvoice']);

        return view('quotations.show', [
            'quotation' => $quotation,
        ]);
    }

    public function edit(Request $request, Quotation $quotation): View|RedirectResponse
    {
        $this->ensureOwnership($request, $quotation);

        if ($quotation->isConverted()) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Converted quotations are kept as records and can no longer be edited.');
        }

        $quotation->load('items');

        return view('quotations.edit', [
            'quotation' => $quotation,
        ]);
    }

    public function update(QuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->ensureOwnership($request, $quotation);

        if ($quotation->isConverted()) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Converted quotations are kept as records and can no longer be edited.');
        }

        DB::transaction(function () use ($request, $quotation): void {
            $payload = $request->validated();
            $items = $payload['items'];
            unset($payload['items']);

            $quotation->update([
                ...$payload,
                'total_amount' => $this->calculateTotal($items),
            ]);

            $this->syncItems($quotation, $items);
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation updated successfully.');
    }

    public function send(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->ensureOwnership($request, $quotation);

        if ($quotation->isConverted()) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Converted quotations are kept as records and cannot be marked as sent.');
        }

        $quotation->update([
            'status' => Quotation::STATUS_SENT,
        ]);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation marked as sent.');
    }

    public function accept(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->ensureOwnership($request, $quotation);

        if ($quotation->isConverted()) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'This quotation has already been converted to an invoice.');
        }

        $quotation->update([
            'status' => Quotation::STATUS_ACCEPTED,
        ]);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation accepted and ready for invoicing.');
    }

    public function reject(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->ensureOwnership($request, $quotation);

        if ($quotation->isConverted()) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'This quotation has already been converted to an invoice.');
        }

        $quotation->update([
            'status' => Quotation::STATUS_REJECTED,
        ]);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation marked as rejected.');
    }

    public function convert(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->ensureOwnership($request, $quotation);

        if ($quotation->isConverted() && $quotation->convertedInvoice) {
            return redirect()
                ->route('invoices.show', $quotation->convertedInvoice)
                ->with('status', 'This quotation has already been converted to an invoice.');
        }

        if ($quotation->isRejected()) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Rejected quotations cannot be converted to invoices.');
        }

        if (! $quotation->isAccepted()) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Accept the quotation before converting it to an invoice.');
        }

        $quotation->loadMissing('items');

        $invoice = DB::transaction(function () use ($request, $quotation): Invoice {
            $businessProfile = $this->lockBusinessProfileForUpdate($request->user());

            $invoice = Invoice::create([
                'user_id' => $request->user()->id,
                'business_profile_id' => $businessProfile->id,
                'invoice_number' => $this->nextInvoiceNumber($businessProfile),
                'customer_name' => $quotation->customer_name,
                'customer_email' => $quotation->customer_email,
                'customer_phone' => $quotation->customer_phone,
                'customer_address' => $quotation->customer_address,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'notes' => $quotation->notes,
                'status' => Invoice::STATUS_ACTIVE,
                'total_amount' => $quotation->total_amount,
            ]);

            $invoice->items()->createMany(
                $quotation->items->values()->map(function ($item, int $index): array {
                    $quantity = (float) $item->quantity;
                    $unitPrice = (float) $item->unit_price;

                    return [
                        'description' => $item->description,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $quantity * $unitPrice,
                        'position' => $index + 1,
                    ];
                })->all()
            );

            $quotation->update([
                'status' => Quotation::STATUS_CONVERTED,
                'converted_invoice_id' => $invoice->id,
                'converted_at' => now(),
            ]);

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Quotation converted to invoice. You can still review the invoice before sending it.');
    }

    public function print(Request $request, Quotation $quotation): View
    {
        $this->ensureOwnership($request, $quotation);

        $quotation->load(['items', 'businessProfile', 'convertedInvoice']);

        return view('quotations.print', [
            'quotation' => $quotation,
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
    protected function syncItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();

        $quotation->items()->createMany(
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

    protected function nextQuotationNumber(BusinessProfile $businessProfile): string
    {
        $latestQuotationNumber = $businessProfile->quotations()->latest('id')->value('quotation_number');

        if ($latestQuotationNumber && preg_match('/(\d+)$/', $latestQuotationNumber, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = $businessProfile->quotations()->count() + 1;
        }

        return sprintf('QT-%s-%04d', now()->format('Y'), $nextNumber);
    }

    protected function nextInvoiceNumber(BusinessProfile $businessProfile): string
    {
        $latestInvoiceNumber = $businessProfile->invoices()->latest('id')->value('invoice_number');

        if ($latestInvoiceNumber && preg_match('/(\d+)$/', $latestInvoiceNumber, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = $businessProfile->invoices()->count() + 1;
        }

        return sprintf('INV-%s-%04d', now()->format('Y'), $nextNumber);
    }

    protected function ensureOwnership(Request $request, Quotation $quotation): void
    {
        abort_unless($quotation->business_profile_id === $request->user()->business_profile_id, 403);
    }
}
