<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Payments</p>
                <h1 class="text-2xl font-semibold text-slate-950">Receipts</h1>
            </div>

            <a href="{{ route('receipts.create') }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-900 bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto">
                Issue receipt
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section>
            <div class="overflow-x-auto pb-2">
                <div class="flex min-w-[820px] gap-4">
                    <article class="min-w-0 flex-1 border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Receipts issued</p>
                        <p class="mt-4 text-2xl font-semibold text-slate-950 sm:text-3xl">{{ $receiptCount }}</p>
                    </article>

                    <article class="min-w-0 flex-1 border border-emerald-200 bg-emerald-50 p-4 shadow-sm sm:p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-700">Money received</p>
                        <p class="mt-4 text-2xl font-semibold text-emerald-700 sm:text-3xl">@ugx($receiptTotal)</p>
                    </article>

                    <article class="min-w-0 flex-1 border border-sky-200 bg-sky-50 p-4 shadow-sm sm:p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Linked to invoices</p>
                        <p class="mt-4 text-2xl font-semibold text-slate-950 sm:text-3xl">{{ $linkedReceiptCount }}</p>
                    </article>

                    <article class="min-w-0 flex-1 border border-amber-200 bg-amber-50 p-4 shadow-sm sm:p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-700">Standalone receipts</p>
                        <p class="mt-4 text-2xl font-semibold text-slate-950 sm:text-3xl">{{ $standaloneReceiptCount }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col items-start justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Receipt register</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Recorded payments</h2>
                </div>
            </div>

            @if ($receipts->isEmpty())
                <div class="px-4 py-8 text-sm text-slate-500 sm:px-6 sm:py-10">
                    No receipts have been issued yet. Create your first receipt to start tracking payments received.
                </div>
            @else
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($receipts as $receipt)
                        <article class="space-y-4 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('receipts.show', $receipt) }}" class="font-semibold text-slate-900 transition hover:text-sky-700">
                                        {{ $receipt->receipt_number }}
                                    </a>
                                    <p class="mt-1 text-sm text-slate-600">{{ $receipt->payer_name }}</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">@ugx($receipt->amount_received)</span>
                            </div>

                            <dl class="grid gap-2 text-sm text-slate-600">
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Payment date</dt>
                                    <dd>{{ $receipt->payment_date->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Source</dt>
                                    <dd>{{ $receipt->invoice ? $receipt->invoice->invoice_number : 'No invoice' }}</dd>
                                </div>
                                @if ($receipt->invoice && $receipt->balance_after !== null)
                                    <div class="flex items-center justify-between gap-3">
                                        <dt>Balance after</dt>
                                        <dd>@ugx($receipt->balance_after)</dd>
                                    </div>
                                @endif
                            </dl>

                            <div class="flex flex-col gap-2">
                                <a href="{{ route('receipts.show', $receipt) }}" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View</a>
                                <a href="{{ route('receipts.print', $receipt) }}" target="_blank" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Print receipt</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Receipt</th>
                                <th class="px-6 py-3">Payer</th>
                                <th class="px-6 py-3">Payment date</th>
                                <th class="px-6 py-3">Source</th>
                                <th class="px-6 py-3">Amount</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($receipts as $receipt)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('receipts.show', $receipt) }}" class="font-semibold text-slate-900 transition hover:text-sky-700">
                                            {{ $receipt->receipt_number }}
                                        </a>
                                        @if ($receipt->invoice && $receipt->balance_after !== null)
                                            <p class="mt-1 text-xs text-slate-500">Balance after: @ugx($receipt->balance_after)</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $receipt->payer_name }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $receipt->payment_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-slate-600">
                                        @if ($receipt->invoice)
                                            <a href="{{ route('invoices.show', $receipt->invoice) }}" class="font-semibold text-sky-700 transition hover:text-sky-900">
                                                {{ $receipt->invoice->invoice_number }}
                                            </a>
                                        @else
                                            No invoice
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-900">@ugx($receipt->amount_received)</td>
                                    <td class="px-6 py-4">
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

                <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
                    {{ $receipts->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
