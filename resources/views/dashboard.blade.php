<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Business overview</p>
                <h1 class="text-2xl font-semibold text-slate-950">Dashboard</h1>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <a href="{{ route('quotations.create') }}" class="inline-flex w-full items-center justify-center rounded-none border border-amber-600 bg-amber-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-500 sm:w-auto">
                    Create quotation
                </a>
                <a href="{{ route('invoices.create') }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-900 bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto">
                    Create invoice
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section>
            <div class="overflow-x-auto pb-2">
                <div class="flex min-w-[1080px] gap-3">
                <article class="relative min-w-0 flex-1 overflow-hidden border border-slate-300 bg-slate-50 p-4 pl-5 shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-slate-900"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-600">Total Invoiced</p>
                    <p class="mt-2 text-lg font-semibold leading-tight text-slate-950 sm:text-xl xl:text-2xl">@ugx($totalInvoiced)</p>
                </article>

                <article class="relative min-w-0 flex-1 overflow-hidden border border-emerald-200 bg-emerald-50 p-4 pl-5 shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-emerald-500"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-700">Money Received</p>
                    <p class="mt-2 text-lg font-semibold leading-tight text-slate-950 sm:text-xl xl:text-2xl">@ugx($moneyReceived)</p>
                </article>

                <article class="relative min-w-0 flex-1 overflow-hidden border border-sky-200 bg-sky-50 p-4 pl-5 shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-sky-500"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-700">Outstanding</p>
                    <p class="mt-2 text-lg font-semibold leading-tight text-slate-950 sm:text-xl xl:text-2xl">@ugx($outstandingBalance)</p>
                </article>

                <article class="relative min-w-0 flex-1 overflow-hidden border border-amber-200 bg-amber-50 p-4 pl-5 shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-amber-500"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700">Open Invoices</p>
                    <p class="mt-2 text-lg font-semibold leading-tight text-slate-950 sm:text-xl xl:text-2xl">{{ $activeInvoiceCount }}</p>
                </article>

                <article class="relative min-w-0 flex-1 overflow-hidden border border-orange-200 bg-orange-50 p-4 pl-5 shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-orange-500"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-700">Pending Quotations</p>
                    <p class="mt-2 text-lg font-semibold leading-tight text-slate-950 sm:text-xl xl:text-2xl">{{ $pendingQuotationCount }}</p>
                </article>

                <article class="relative min-w-0 flex-1 overflow-hidden border border-lime-200 bg-lime-50 p-4 pl-5 shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-lime-500"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-lime-700">Accepted Quotations</p>
                    <p class="mt-2 text-lg font-semibold leading-tight text-slate-950 sm:text-xl xl:text-2xl">{{ $acceptedQuotationCount }}</p>
                </article>

                <article class="relative min-w-0 flex-1 overflow-hidden border border-teal-200 bg-teal-50 p-4 pl-5 shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-teal-500"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-teal-700">Settled Invoices</p>
                    <p class="mt-2 text-lg font-semibold leading-tight text-slate-950 sm:text-xl xl:text-2xl">{{ $settledInvoiceCount }}</p>
                </article>

                <article class="relative min-w-0 flex-1 overflow-hidden border border-violet-200 bg-violet-50 p-4 pl-5 shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-violet-500"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-violet-700">Receipts</p>
                    <p class="mt-2 text-lg font-semibold leading-tight text-violet-700 sm:text-xl xl:text-2xl">{{ $receiptCount }}</p>
                </article>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_380px]">
            <div class="space-y-6">
                <div class="relative overflow-hidden border border-slate-200 bg-white shadow-sm">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-emerald-500"></span>
                    <div class="flex flex-col items-start justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Recent Invoices</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-950">Latest activity</h2>
                        </div>

                        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
                            <a href="{{ route('invoices.create') }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-900 bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto">
                                Create invoice
                            </a>
                            <a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-sky-700 transition hover:text-sky-900">View all</a>
                        </div>
                    </div>

                    @if ($recentInvoices->isEmpty())
                        <div class="px-4 py-8 text-sm text-slate-500 sm:px-6 sm:py-10">
                            <p>No invoices yet. Create your first invoice to start tracking revenue and payments.</p>
                            <a href="{{ route('invoices.create') }}" class="mt-4 inline-flex items-center justify-center rounded-none border border-slate-900 bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Create invoice
                            </a>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100 md:hidden">
                            @foreach ($recentInvoices as $invoice)
                                <article class="relative overflow-hidden space-y-4 px-4 py-4 pl-5">
                                    <span class="{{ $invoice->isSettled() ? 'bg-emerald-500' : ($invoice->isPartial() ? 'bg-amber-500' : 'bg-sky-500') }} absolute inset-y-0 left-0 w-1"></span>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-slate-900 transition hover:text-sky-700">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                            <p class="mt-1 text-sm text-slate-600">{{ $invoice->customer_name }}</p>
                                        </div>
                                        <span class="{{ $invoice->isSettled() ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : ($invoice->isPartial() ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-sky-200 bg-sky-50 text-sky-700') }} inline-flex border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em]">
                                            {{ $invoice->isSettled() ? 'Settled' : ($invoice->isPartial() ? 'Partial' : 'Active') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 text-sm text-slate-600">
                                        <span>Issued {{ $invoice->issue_date->format('M d, Y') }}</span>
                                        <span class="font-semibold text-slate-900">@ugx($invoice->total_amount)</span>
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
                                        <th class="px-6 py-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($recentInvoices as $invoice)
                                        <tr class="bg-white">
                                            <td class="px-6 py-4">
                                                <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-slate-900 transition hover:text-sky-700">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                                <p class="mt-1 text-xs text-slate-500">Issued {{ $invoice->issue_date->format('M d, Y') }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600">{{ $invoice->customer_name }}</td>
                                            <td class="px-6 py-4">
                                                <span class="{{ $invoice->isSettled() ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : ($invoice->isPartial() ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-sky-200 bg-sky-50 text-sky-700') }} inline-flex border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                                                    {{ $invoice->isSettled() ? 'Settled' : ($invoice->isPartial() ? 'Partial' : 'Active') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-slate-900">@ugx($invoice->total_amount)</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">

                <div class="relative overflow-hidden border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-teal-500"></span>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Quick actions</p>
                    <div class="mt-5 space-y-3">
                        <a href="{{ route('quotations.create') }}" class="flex items-center justify-between border border-slate-200 border-l-4 border-l-amber-500 px-4 py-4 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:border-l-amber-600 hover:bg-slate-50">
                            <span>Create a quotation</span>
                            <span class="text-slate-400">+</span>
                        </a>
                        <a href="{{ route('invoices.create') }}" class="flex items-center justify-between border border-slate-200 border-l-4 border-l-sky-500 px-4 py-4 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:border-l-sky-600 hover:bg-slate-50">
                            <span>Create an invoice</span>
                            <span class="text-slate-400">+</span>
                        </a>
                        <a href="{{ route('receipts.create') }}" class="flex items-center justify-between border border-slate-200 border-l-4 border-l-fuchsia-500 px-4 py-4 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:border-l-fuchsia-600 hover:bg-slate-50">
                            <span>Create a receipt</span>
                            <span class="text-slate-400">+</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
