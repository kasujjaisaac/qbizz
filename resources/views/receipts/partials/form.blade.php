@php
    $selectedSource = old('receipt_source', $selectedInvoice ? 'invoice' : 'standalone');
    $selectedInvoiceId = old('invoice_id', $selectedInvoice?->id);
    $receiptNumberValue = old('receipt_number', $receipt->receipt_number);
    $receiptNumberMode = old('receipt_number_mode', 'auto');
    $receiptDateValue = old('payment_date', $receipt->payment_date instanceof \Carbon\CarbonInterface ? $receipt->payment_date->toDateString() : $receipt->payment_date);
    $defaultAmount = old('amount_received', $selectedInvoice ? number_format($selectedInvoice->balanceDue(), 2, '.', '') : '');
    $invoiceOptions = $openInvoices->map(fn ($invoice) => [
        'id' => (string) $invoice->id,
        'invoice_number' => $invoice->invoice_number,
        'customer_name' => $invoice->customer_name,
        'status' => $invoice->isPartial() ? 'Partial' : 'Active',
        'issue_date' => $invoice->issue_date->format('M d, Y'),
        'total_amount' => (float) $invoice->total_amount,
        'paid_amount' => $invoice->amountPaid(),
        'balance_due' => $invoice->balanceDue(),
    ])->values();
@endphp

<div
    x-data="{
        receiptNumberMode: @js($receiptNumberMode),
        defaultReceiptNumber: @js((string) $receipt->receipt_number),
        source: @js($selectedSource),
        selectedInvoiceId: @js($selectedInvoiceId ? (string) $selectedInvoiceId : ''),
        amountReceived: @js((string) $defaultAmount),
        invoices: @js($invoiceOptions),
        selectedInvoice() {
            return this.invoices.find((invoice) => invoice.id === String(this.selectedInvoiceId)) ?? null;
        },
        formatMoney(value) {
            return new Intl.NumberFormat('en-UG', {
                style: 'currency',
                currency: 'UGX',
                maximumFractionDigits: 0,
            }).format(Number(value || 0));
        },
        balanceAfter() {
            const invoice = this.selectedInvoice();
            const amount = Number(this.amountReceived || 0);

            if (!invoice) {
                return 0;
            }

            return Math.max(Number(invoice.balance_due) - amount, 0);
        },
        syncReceiptNumberMode(value) {
            this.receiptNumberMode = value === this.defaultReceiptNumber ? 'auto' : 'manual';
        },
    }"
    class="space-y-8"
>
    @if ($errors->any())
        <div class="border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold text-rose-800">Please review the receipt details before saving.</p>
            <ul class="mt-2 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
        <section class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="receipt_number" class="block text-sm font-semibold text-slate-900">Receipt number</label>
                    <input type="hidden" name="receipt_number_mode" x-model="receiptNumberMode">
                    <input id="receipt_number" name="receipt_number" type="text" value="{{ $receiptNumberValue }}" @input="syncReceiptNumberMode($event.target.value)" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" required>
                    <p class="mt-2 text-xs leading-5 text-slate-500">Leave the suggested number unchanged and the final receipt number will be assigned safely when you save. Type your own number if you want to override it.</p>
                </div>

                <div>
                    <label for="payment_date" class="block text-sm font-semibold text-slate-900">Payment date</label>
                    <input id="payment_date" name="payment_date" type="date" value="{{ $receiptDateValue }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" required>
                </div>

                <div class="sm:col-span-2">
                    <label for="receipt_source" class="block text-sm font-semibold text-slate-900">Receipt source</label>
                    <select id="receipt_source" name="receipt_source" x-model="source" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                        <option value="invoice">From invoice</option>
                        <option value="standalone">No invoice</option>
                    </select>
                </div>

                <div class="sm:col-span-2" x-show="source === 'invoice'" x-cloak>
                    <label for="invoice_id" class="block text-sm font-semibold text-slate-900">Choose invoice</label>
                    <select id="invoice_id" name="invoice_id" x-model="selectedInvoiceId" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                        <option value="">Select an open invoice</option>
                        @foreach ($openInvoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->invoice_number }} · {{ $invoice->customer_name }} · Balance @ugx($invoice->balanceDue())
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('invoice_id')" class="mt-2" />
                </div>

                <div x-show="source === 'standalone'">
                    <label for="payer_name" class="block text-sm font-semibold text-slate-900">Payer name</label>
                    <input id="payer_name" name="payer_name" type="text" value="{{ old('payer_name') }}" :disabled="source === 'invoice'" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                    <x-input-error :messages="$errors->get('payer_name')" class="mt-2" />
                </div>

                <div x-show="source === 'standalone'">
                    <label for="payer_email" class="block text-sm font-semibold text-slate-900">Payer email</label>
                    <input id="payer_email" name="payer_email" type="email" value="{{ old('payer_email') }}" :disabled="source === 'invoice'" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                    <x-input-error :messages="$errors->get('payer_email')" class="mt-2" />
                </div>

                <div x-show="source === 'standalone'">
                    <label for="payer_phone" class="block text-sm font-semibold text-slate-900">Payer phone</label>
                    <input id="payer_phone" name="payer_phone" type="text" value="{{ old('payer_phone') }}" :disabled="source === 'invoice'" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                    <x-input-error :messages="$errors->get('payer_phone')" class="mt-2" />
                </div>

                <div>
                    <label for="amount_received" class="block text-sm font-semibold text-slate-900">Amount received</label>
                    <input id="amount_received" name="amount_received" type="number" min="0.01" step="0.01" x-model="amountReceived" value="{{ old('amount_received', $defaultAmount) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" required>
                    <x-input-error :messages="$errors->get('amount_received')" class="mt-2" />
                </div>

                <div class="sm:col-span-2" x-show="source === 'standalone'">
                    <label for="payer_address" class="block text-sm font-semibold text-slate-900">Payer address</label>
                    <textarea id="payer_address" name="payer_address" rows="4" :disabled="source === 'invoice'" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">{{ old('payer_address') }}</textarea>
                    <x-input-error :messages="$errors->get('payer_address')" class="mt-2" />
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-semibold text-slate-900">Payment method</label>
                    <input id="payment_method" name="payment_method" type="text" value="{{ old('payment_method') }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                </div>

                <div>
                    <label for="reference_number" class="block text-sm font-semibold text-slate-900">Reference number</label>
                    <input id="reference_number" name="reference_number" type="text" value="{{ old('reference_number') }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                    <x-input-error :messages="$errors->get('reference_number')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Receipt summary</p>
            <p class="mt-4 break-words text-3xl font-semibold text-slate-950 sm:text-4xl" x-text="formatMoney(amountReceived)"></p>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                Record a payment against an invoice or issue a standalone receipt for money received without an invoice.
            </p>

            <div class="mt-6 border border-slate-200 bg-slate-50 p-4" x-show="source === 'invoice'" x-cloak>
                <template x-if="selectedInvoice()">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Selected invoice</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950" x-text="selectedInvoice().invoice_number"></p>
                            <p class="mt-1 text-sm text-slate-600" x-text="selectedInvoice().customer_name"></p>
                        </div>

                        <div class="grid gap-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-3">
                                <span>Status</span>
                                <span class="font-semibold text-slate-900" x-text="selectedInvoice().status"></span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Issue date</span>
                                <span class="font-semibold text-slate-900" x-text="selectedInvoice().issue_date"></span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Invoice total</span>
                                <span class="font-semibold text-slate-900" x-text="formatMoney(selectedInvoice().total_amount)"></span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Already paid</span>
                                <span class="font-semibold text-slate-900" x-text="formatMoney(selectedInvoice().paid_amount)"></span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Current balance</span>
                                <span class="font-semibold text-slate-900" x-text="formatMoney(selectedInvoice().balance_due)"></span>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-3">
                                <span>Balance after receipt</span>
                                <span class="font-semibold text-slate-900" x-text="formatMoney(balanceAfter())"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="! selectedInvoice()">
                    <p class="text-sm leading-6 text-slate-500">Choose an open invoice to see the current balance before issuing the receipt.</p>
                </template>
            </div>

            <div class="mt-6 border border-slate-200 bg-slate-50 p-4">
                <label for="notes" class="block text-sm font-semibold text-slate-900">Notes</label>
                <textarea id="notes" name="notes" rows="6" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>
        </section>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('receipts.index') }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto">
            Cancel
        </a>
        <button type="submit" class="inline-flex w-full items-center justify-center rounded-none border border-slate-900 bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto">
            {{ $submitLabel }}
        </button>
    </div>
</div>
