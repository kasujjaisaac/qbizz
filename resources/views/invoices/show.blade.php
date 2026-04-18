<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Invoices</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $invoice->invoice_number }}</h1>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap">
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex w-full items-center justify-center rounded-none border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto">
                    Print invoice
                </a>

                @if (! $invoice->hasPayments() && ! $invoice->isSettled())
                    <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto">
                        Edit invoice
                    </a>
                @endif

                @if (! $invoice->isSettled())
                    <a href="{{ route('receipts.create', ['invoice' => $invoice->id]) }}" class="inline-flex w-full items-center justify-center rounded-none border border-emerald-600 bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
                        Issue receipt
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Status</p>
                <p class="mt-4 text-2xl font-semibold {{ $invoice->isSettled() ? 'text-emerald-600' : ($invoice->isPartial() ? 'text-amber-600' : 'text-sky-700') }}">
                    {{ $invoice->isSettled() ? 'Settled' : ($invoice->isPartial() ? 'Partial' : 'Active') }}
                </p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Issue date</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">{{ $invoice->issue_date->format('M d, Y') }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Invoice total</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">@ugx($invoice->total_amount)</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Paid amount</p>
                <p class="mt-4 text-2xl font-semibold text-emerald-600">@ugx($invoice->paid_amount)</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Balance due</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">@ugx($invoice->balanceDue())</p>
            </article>
        </section>

        <section class="border border-slate-200 bg-white p-4 shadow-sm sm:p-8">
            <div class="flex flex-col gap-6 border-b border-slate-200 pb-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    @if ($invoice->businessProfile->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($invoice->businessProfile->logo_path) }}" alt="Business logo" class="h-16 w-16 object-contain">
                    @endif

                    <div>
                        <h2 class="text-2xl font-semibold text-slate-950">{{ $invoice->businessProfile->business_name }}</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $invoice->businessProfile->formattedAddress() }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $invoice->businessProfile->contact_email }}{{ $invoice->businessProfile->phone ? ' · '.$invoice->businessProfile->phone : '' }}</p>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Bill to</p>
                        <div class="mt-3 text-sm leading-7 text-slate-600">
                            <p class="font-semibold text-slate-900">{{ $invoice->customer_name }}</p>
                            @if ($invoice->customer_email)
                                <p>{{ $invoice->customer_email }}</p>
                            @endif
                            @if ($invoice->customer_phone)
                                <p>{{ $invoice->customer_phone }}</p>
                            @endif
                            @if ($invoice->customer_address)
                                <p class="whitespace-pre-line">{{ $invoice->customer_address }}</p>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Invoice details</p>
                        <div class="mt-3 space-y-2 text-sm text-slate-600">
                            <p><span class="font-semibold text-slate-900">Number:</span> {{ $invoice->invoice_number }}</p>
                            <p><span class="font-semibold text-slate-900">Issue date:</span> {{ $invoice->issue_date->format('M d, Y') }}</p>
                            <p><span class="font-semibold text-slate-900">Due date:</span> {{ optional($invoice->due_date)->format('M d, Y') ?: 'Not set' }}</p>
                            <p><span class="font-semibold text-slate-900">Paid amount:</span> @ugx($invoice->paid_amount)</p>
                            <p><span class="font-semibold text-slate-900">Balance due:</span> @ugx($invoice->balanceDue())</p>
                            @if ($invoice->settled_at)
                                <p><span class="font-semibold text-slate-900">Settled at:</span> {{ $invoice->settled_at->format('M d, Y g:i A') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3 md:hidden">
                @foreach ($invoice->items as $item)
                    <article class="border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-slate-900">{{ $item->description }}</h3>
                            <span class="font-semibold text-slate-900">@ugx($item->line_total)</span>
                        </div>
                        <dl class="mt-3 grid gap-2 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-3">
                                <dt>Quantity</dt>
                                <dd>{{ number_format((float) $item->quantity, 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt>Unit price</dt>
                                <dd>@ugx($item->unit_price)</dd>
                            </div>
                        </dl>
                    </article>
                @endforeach
                <div class="flex items-center justify-between border-t border-slate-200 pt-4 text-base font-semibold text-slate-950">
                    <span>Total</span>
                    <span>@ugx($invoice->total_amount)</span>
                </div>
            </div>

            <div class="mt-6 hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Unit price</th>
                            <th class="px-4 py-3">Line total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-4 text-slate-700">{{ $item->description }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ number_format((float) $item->quantity, 2) }}</td>
                                <td class="px-4 py-4 text-slate-600">@ugx($item->unit_price)</td>
                                <td class="px-4 py-4 font-semibold text-slate-900">@ugx($item->line_total)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-right text-sm font-semibold text-slate-700">Total</td>
                            <td class="px-4 py-4 text-lg font-semibold text-slate-950">@ugx($invoice->total_amount)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($invoice->notes)
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Notes</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $invoice->notes }}</p>
                </div>
            @endif

            @if ($invoice->receipts->isNotEmpty())
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Payments received</p>
                            <h3 class="mt-2 text-xl font-semibold text-slate-950">Receipt history</h3>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3 md:hidden">
                        @foreach ($invoice->receipts as $receipt)
                            <article class="border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <a href="{{ route('receipts.show', $receipt) }}" class="font-semibold text-slate-900 transition hover:text-sky-700">
                                            {{ $receipt->receipt_number }}
                                        </a>
                                        <p class="mt-1 text-sm text-slate-600">{{ $receipt->payment_date->format('M d, Y') }}</p>
                                    </div>
                                    <span class="font-semibold text-slate-900">@ugx($receipt->amount_received)</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-sm text-slate-600">
                                    <span>Balance after</span>
                                    <span class="font-semibold text-slate-900">@ugx($receipt->balance_after)</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-4 hidden overflow-x-auto md:block">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Receipt</th>
                                    <th class="px-4 py-3">Payment date</th>
                                    <th class="px-4 py-3">Amount</th>
                                    <th class="px-4 py-3">Balance after</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($invoice->receipts as $receipt)
                                    <tr>
                                        <td class="px-4 py-4 font-semibold text-slate-900">{{ $receipt->receipt_number }}</td>
                                        <td class="px-4 py-4 text-slate-600">{{ $receipt->payment_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-4 font-semibold text-slate-900">@ugx($receipt->amount_received)</td>
                                        <td class="px-4 py-4 text-slate-600">@ugx($receipt->balance_after)</td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('receipts.show', $receipt) }}" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View</a>
                                                <a href="{{ route('receipts.print', $receipt) }}" target="_blank" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Print receipt</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
