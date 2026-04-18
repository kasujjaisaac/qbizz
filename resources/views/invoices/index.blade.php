<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Invoices</p>
                <h1 class="text-2xl font-semibold text-slate-950">Open Invoices</h1>
            </div>

            <a href="{{ route('invoices.create') }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-900 bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto">
                Create invoice
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2">
            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Open invoices</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950 sm:text-3xl">{{ $activeCount }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Outstanding value</p>
                <p class="mt-4 text-2xl font-semibold text-sky-700 sm:text-3xl">@ugx($activeTotal)</p>
            </article>
        </section>

        <section class="border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col items-start justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Invoice register</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Invoices with outstanding balance</h2>
                </div>

                <a href="{{ route('invoices.settled') }}" class="text-sm font-semibold text-sky-700 transition hover:text-sky-900">View settled invoices</a>
            </div>

            @if ($activeInvoices->isEmpty())
                <div class="px-4 py-8 text-sm text-slate-500 sm:px-6 sm:py-10">
                    No open invoices yet. Create one to begin billing customers.
                </div>
            @else
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($activeInvoices as $invoice)
                        <article class="space-y-4 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-slate-900 transition hover:text-sky-700">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    <p class="mt-1 text-sm text-slate-600">{{ $invoice->customer_name }}</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">@ugx($invoice->total_amount)</span>
                            </div>
                            <dl class="grid gap-2 text-sm text-slate-600">
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Status</dt>
                                    <dd class="font-semibold {{ $invoice->isPartial() ? 'text-amber-700' : 'text-sky-700' }}">
                                        {{ $invoice->isPartial() ? 'Partial' : 'Active' }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Issue date</dt>
                                    <dd>{{ $invoice->issue_date->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Paid</dt>
                                    <dd>@ugx($invoice->paid_amount)</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Balance due</dt>
                                    <dd class="font-semibold text-slate-900">@ugx($invoice->balanceDue())</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Items</dt>
                                    <dd>{{ $invoice->items_count }}</dd>
                                </div>
                            </dl>
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View</a>
                                @if (! $invoice->hasPayments())
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Edit</a>
                                @endif
                                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Print invoice</a>
                                <a href="{{ route('receipts.create', ['invoice' => $invoice->id]) }}" class="inline-flex items-center justify-center border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 transition hover:bg-emerald-100">
                                    Issue receipt
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Invoice</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Issue date</th>
                                <th class="px-6 py-3">Paid</th>
                                <th class="px-6 py-3">Balance</th>
                                <th class="px-6 py-3">Items</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($activeInvoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-slate-900 transition hover:text-sky-700">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $invoice->customer_name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="{{ $invoice->isPartial() ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-sky-200 bg-sky-50 text-sky-700' }} inline-flex border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                                            {{ $invoice->isPartial() ? 'Partial' : 'Active' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $invoice->issue_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-slate-600">@ugx($invoice->paid_amount)</td>
                                    <td class="px-6 py-4 font-semibold text-slate-900">@ugx($invoice->balanceDue())</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $invoice->items_count }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View</a>
                                            @if (! $invoice->hasPayments())
                                                <a href="{{ route('invoices.edit', $invoice) }}" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Edit</a>
                                            @endif
                                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Print invoice</a>
                                            <a href="{{ route('receipts.create', ['invoice' => $invoice->id]) }}" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 transition hover:bg-emerald-100">
                                                Issue receipt
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
                    {{ $activeInvoices->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
