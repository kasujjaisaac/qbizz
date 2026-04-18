<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiptRequest;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('receipts.index', [
            'receipts' => $user->receipts()
                ->with(['invoice'])
                ->latest('payment_date')
                ->latest('id')
                ->paginate(10),
            'receiptCount' => $user->receipts()->count(),
            'receiptTotal' => (float) $user->receipts()->sum('amount_received'),
            'linkedReceiptCount' => $user->receipts()->whereNotNull('invoice_id')->count(),
            'standaloneReceiptCount' => $user->receipts()->whereNull('invoice_id')->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $selectedInvoice = null;

        if ($request->filled('invoice')) {
            $selectedInvoice = $user->invoices()
                ->open()
                ->withCount('receipts')
                ->findOrFail((int) $request->query('invoice'));
        }

        return view('receipts.create', [
            'receipt' => new Receipt([
                'receipt_number' => $this->nextReceiptNumber($user),
                'payment_date' => now()->toDateString(),
            ]),
            'openInvoices' => $user->invoices()
                ->open()
                ->withCount(['items', 'receipts'])
                ->latest('issue_date')
                ->latest('id')
                ->get(),
            'selectedInvoice' => $selectedInvoice,
        ]);
    }

    public function store(ReceiptRequest $request): RedirectResponse
    {
        $receipt = DB::transaction(function () use ($request): Receipt {
            $user = $request->user();
            $payload = $request->validated();
            $businessProfile = $user->businessProfile;
            $amountReceived = round((float) $payload['amount_received'], 2);

            if ($payload['receipt_source'] === 'invoice') {
                /** @var Invoice $invoice */
                $invoice = $user->invoices()
                    ->open()
                    ->lockForUpdate()
                    ->findOrFail((int) $payload['invoice_id']);

                $balanceBefore = $invoice->balanceDue();

                if ($amountReceived > $balanceBefore) {
                    throw ValidationException::withMessages([
                        'amount_received' => 'The receipt amount can not be greater than the invoice balance.',
                    ]);
                }

                $balanceAfter = max(round($balanceBefore - $amountReceived, 2), 0);

                $receipt = $user->receipts()->create([
                    'business_profile_id' => $businessProfile->id,
                    'invoice_id' => $invoice->id,
                    'receipt_number' => $payload['receipt_number'],
                    'payer_name' => $invoice->customer_name,
                    'payer_email' => $invoice->customer_email,
                    'payer_phone' => $invoice->customer_phone,
                    'payer_address' => $invoice->customer_address,
                    'amount_received' => $amountReceived,
                    'payment_date' => $payload['payment_date'],
                    'payment_method' => $payload['payment_method'] ?? null,
                    'reference_number' => $payload['reference_number'] ?? null,
                    'notes' => $payload['notes'] ?? null,
                    'invoice_total_snapshot' => $invoice->total_amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'issued_at' => now(),
                ]);

                $this->syncInvoicePaymentState($invoice, $amountReceived, (string) $payload['payment_date']);

                return $receipt;
            }

            return $user->receipts()->create([
                'business_profile_id' => $businessProfile->id,
                'receipt_number' => $payload['receipt_number'],
                'payer_name' => $payload['payer_name'],
                'payer_email' => $payload['payer_email'] ?? null,
                'payer_phone' => $payload['payer_phone'] ?? null,
                'payer_address' => $payload['payer_address'] ?? null,
                'amount_received' => $amountReceived,
                'payment_date' => $payload['payment_date'],
                'payment_method' => $payload['payment_method'] ?? null,
                'reference_number' => $payload['reference_number'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'issued_at' => now(),
            ]);
        });

        return redirect()
            ->route('receipts.show', $receipt)
            ->with('status', 'Receipt created successfully.');
    }

    public function show(Request $request, Receipt $receipt): View
    {
        $this->ensureOwnership($request, $receipt);

        $receipt->load(['invoice', 'businessProfile']);

        return view('receipts.show', [
            'receipt' => $receipt,
        ]);
    }

    public function print(Request $request, Receipt $receipt): View
    {
        $this->ensureOwnership($request, $receipt);

        $receipt->load(['invoice', 'businessProfile']);

        return view('receipts.print', [
            'receipt' => $receipt,
        ]);
    }

    protected function nextReceiptNumber(User $user): string
    {
        $latestReceiptNumber = $user->receipts()->latest('id')->value('receipt_number');

        if ($latestReceiptNumber && preg_match('/(\d+)$/', $latestReceiptNumber, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = $user->receipts()->count() + 1;
        }

        return sprintf('RCP-%s-%04d', now()->format('Y'), $nextNumber);
    }

    protected function syncInvoicePaymentState(Invoice $invoice, float $amountReceived, string $paymentDate): void
    {
        $newPaidAmount = round($invoice->amountPaid() + $amountReceived, 2);
        $invoiceTotal = round((float) $invoice->total_amount, 2);
        $status = Invoice::STATUS_PARTIAL;
        $settledAt = null;

        if ($newPaidAmount <= 0) {
            $status = Invoice::STATUS_ACTIVE;
            $newPaidAmount = 0;
        } elseif ($newPaidAmount >= $invoiceTotal) {
            $status = Invoice::STATUS_SETTLED;
            $newPaidAmount = $invoiceTotal;
            $settledAt = $paymentDate;
        }

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'status' => $status,
            'settled_at' => $settledAt,
        ]);
    }

    protected function ensureOwnership(Request $request, Receipt $receipt): void
    {
        abort_unless($receipt->user_id === $request->user()->id, 403);
    }
}
