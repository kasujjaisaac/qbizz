@php
    $itemRows = old('items', isset($invoice) && $invoice->exists
        ? $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
        ])->all()
        : [
            ['description' => '', 'quantity' => 1, 'unit_price' => ''],
        ]);
    $issueDateValue = old('issue_date', $invoice->issue_date instanceof \Carbon\CarbonInterface ? $invoice->issue_date->toDateString() : $invoice->issue_date);
    $dueDateValue = old('due_date', $invoice->due_date instanceof \Carbon\CarbonInterface ? $invoice->due_date->toDateString() : $invoice->due_date);
@endphp

<div
    x-data="{
        items: @js(count($itemRows) > 0 ? $itemRows : [['description' => '', 'quantity' => 1, 'unit_price' => '']]),
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        lineTotal(item) {
            return (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
        },
        grandTotal() {
            return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
        },
        formatMoney(value) {
            return new Intl.NumberFormat('en-UG', {
                style: 'currency',
                currency: 'UGX',
                maximumFractionDigits: 0,
            }).format(value || 0);
        },
    }"
    class="space-y-8"
>
    @if ($errors->any())
        <div class="border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold text-rose-800">Please review the invoice details before saving.</p>
            <ul class="mt-2 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
        <section class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="invoice_number" class="block text-sm font-semibold text-slate-900">Invoice number</label>
                    <input id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number', $invoice->invoice_number) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" required>
                </div>

                <div>
                    <label for="issue_date" class="block text-sm font-semibold text-slate-900">Issue date</label>
                    <input id="issue_date" name="issue_date" type="date" value="{{ $issueDateValue }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" required>
                </div>

                <div>
                    <label for="customer_name" class="block text-sm font-semibold text-slate-900">Customer name</label>
                    <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $invoice->customer_name) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" required>
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-semibold text-slate-900">Due date</label>
                    <input id="due_date" name="due_date" type="date" value="{{ $dueDateValue }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                </div>

                <div>
                    <label for="customer_email" class="block text-sm font-semibold text-slate-900">Customer email</label>
                    <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email', $invoice->customer_email) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                </div>

                <div>
                    <label for="customer_phone" class="block text-sm font-semibold text-slate-900">Customer phone</label>
                    <input id="customer_phone" name="customer_phone" type="text" value="{{ old('customer_phone', $invoice->customer_phone) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                </div>

                <div class="sm:col-span-2">
                    <label for="customer_address" class="block text-sm font-semibold text-slate-900">Customer address</label>
                    <textarea id="customer_address" name="customer_address" rows="4" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">{{ old('customer_address', $invoice->customer_address) }}</textarea>
                </div>
            </div>
        </section>

        <section class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Invoice total</p>
            <p class="mt-4 break-words text-3xl font-semibold text-slate-950 sm:text-4xl" x-text="formatMoney(grandTotal())"></p>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                The grand total is calculated automatically from your invoice items and will update as you type.
            </p>

            <div class="mt-6 border border-slate-200 bg-slate-50 p-4">
                <label for="notes" class="block text-sm font-semibold text-slate-900">Notes for the customer</label>
                <textarea id="notes" name="notes" rows="6" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </section>
    </div>

    <section class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Invoice items</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-950">Billable lines</h2>
            </div>

            <button type="button" @click="addItem()" class="inline-flex items-center justify-center rounded-none border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                Add item
            </button>
        </div>

        <div class="mt-6 space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="grid gap-4 border border-slate-200 p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.6fr)_120px_160px_100px_48px]">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Description</label>
                        <input x-model="item.description" :name="`items[${index}][description]`" type="text" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Qty</label>
                        <input x-model="item.quantity" :name="`items[${index}][quantity]`" type="number" min="0.01" step="0.01" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Unit price</label>
                        <input x-model="item.unit_price" :name="`items[${index}][unit_price]`" type="number" min="0" step="0.01" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Line total</label>
                        <div class="mt-2 flex h-11 items-center border border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-900" x-text="formatMoney(lineTotal(item))"></div>
                    </div>

                    <div class="flex items-end sm:col-span-2 lg:col-span-1 lg:justify-end">
                        <button type="button" @click="removeItem(index)" class="inline-flex h-11 w-full items-center justify-center border border-slate-200 bg-white text-slate-500 transition hover:border-rose-300 hover:text-rose-600 sm:w-11">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('invoices.index') }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto">
            Cancel
        </a>
        <button type="submit" class="inline-flex w-full items-center justify-center rounded-none border border-slate-900 bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto">
            {{ $submitLabel }}
        </button>
    </div>
</div>
