<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Invoices</p>
            <h1 class="text-2xl font-semibold text-slate-950">Settled Invoices</h1>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2">
            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Settled invoices</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950 sm:text-3xl">{{ $settledCount }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Money received</p>
                <p class="mt-4 text-2xl font-semibold text-emerald-600 sm:text-3xl">@ugx($settledTotal)</p>
            </article>
        </section>

        <section class="border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col items-start justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Payment archive</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Invoices fully paid by receipt</h2>
                </div>

                <a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-sky-700 transition hover:text-sky-900">Back to open invoices</a>
            </div>

            @if ($settledInvoices->isEmpty())
                <div class="px-4 py-8 text-sm text-slate-500 sm:px-6 sm:py-10">
                    No invoices have been marked as settled yet.
                </div>
            @else
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($settledInvoices as $invoice)
                        <article class="space-y-4 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $invoice->invoice_number }}</p>
                                    <p class="mt-1 text-sm text-slate-600">{{ $invoice->customer_name }}</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">@ugx($invoice->total_amount)</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 text-sm text-slate-600">
                                <span>Settled on</span>
                                <span>{{ optional($invoice->settled_at)->format('M d, Y') }}</span>
                            </div>
                            @if ($invoice->latestReceipt)
                                <div class="flex items-center justify-between gap-3 text-sm text-slate-600">
                                    <span>Latest receipt</span>
                                    <span class="font-semibold text-slate-900">{{ $invoice->latestReceipt->receipt_number }}</span>
                                </div>
                            @endif
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View</a>
                                @if ($invoice->latestReceipt)
                                    <a href="{{ route('receipts.show', $invoice->latestReceipt) }}" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View receipt</a>
                                @endif
                                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Print invoice</a>
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
                                <th class="px-6 py-3">Settled on</th>
                                <th class="px-6 py-3">Receipts</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($settledInvoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $invoice->customer_name }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ optional($invoice->settled_at)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-slate-600">
                                        <div>{{ $invoice->receipts_count }}</div>
                                        @if ($invoice->latestReceipt)
                                            <div class="mt-1 text-xs text-slate-500">{{ $invoice->latestReceipt->receipt_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-900">@ugx($invoice->total_amount)</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View</a>
                                            @if ($invoice->latestReceipt)
                                                <a href="{{ route('receipts.show', $invoice->latestReceipt) }}" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Receipt</a>
                                            @endif
                                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Print invoice</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
                    {{ $settledInvoices->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
