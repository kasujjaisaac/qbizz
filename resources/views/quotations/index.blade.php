<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Quotations</p>
                <h1 class="text-2xl font-semibold text-slate-950">Quotation Register</h1>
            </div>

            <a href="{{ route('quotations.create') }}" class="inline-flex w-full items-center justify-center rounded-none border border-amber-600 bg-amber-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-500 sm:w-auto">
                Create quotation
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Total quotations</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950 sm:text-3xl">{{ $quotationCount }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Awaiting response</p>
                <p class="mt-4 text-2xl font-semibold text-sky-700 sm:text-3xl">{{ $pendingCount }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Accepted</p>
                <p class="mt-4 text-2xl font-semibold text-emerald-600 sm:text-3xl">{{ $acceptedCount }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Converted</p>
                <p class="mt-4 text-2xl font-semibold text-amber-600 sm:text-3xl">{{ $convertedCount }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Quoted value</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950 sm:text-3xl">@ugx($quotedTotal)</p>
            </article>
        </section>

        <section class="border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col items-start justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Quotation pipeline</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">All customer quotations</h2>
                </div>
            </div>

            @if ($quotations->isEmpty())
                <div class="px-4 py-8 text-sm text-slate-500 sm:px-6 sm:py-10">
                    <p>No quotations yet. Create one to send a pricing proposal before raising an invoice.</p>
                    <a href="{{ route('quotations.create') }}" class="mt-4 inline-flex items-center justify-center rounded-none border border-amber-600 bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-500">
                        Create quotation
                    </a>
                </div>
            @else
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($quotations as $quotation)
                        @php
                            $statusClasses = $quotation->isConverted()
                                ? 'border-amber-200 bg-amber-50 text-amber-700'
                                : ($quotation->isAccepted()
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                    : ($quotation->isRejected()
                                        ? 'border-rose-200 bg-rose-50 text-rose-700'
                                        : ($quotation->isExpired()
                                            ? 'border-slate-300 bg-slate-100 text-slate-700'
                                            : ($quotation->isSent()
                                                ? 'border-sky-200 bg-sky-50 text-sky-700'
                                                : 'border-violet-200 bg-violet-50 text-violet-700'))));
                        @endphp
                        <article class="space-y-4 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('quotations.show', $quotation) }}" class="font-semibold text-slate-900 transition hover:text-amber-700">
                                        {{ $quotation->quotation_number }}
                                    </a>
                                    <p class="mt-1 text-sm text-slate-600">{{ $quotation->customer_name }}</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">@ugx($quotation->total_amount)</span>
                            </div>
                            <dl class="grid gap-2 text-sm text-slate-600">
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Status</dt>
                                    <dd>
                                        <span class="{{ $statusClasses }} inline-flex border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em]">
                                            {{ $quotation->displayStatus() }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Issued</dt>
                                    <dd>{{ $quotation->issue_date->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Valid until</dt>
                                    <dd>{{ optional($quotation->valid_until)->format('M d, Y') ?: 'Not set' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Items</dt>
                                    <dd>{{ $quotation->items_count }}</dd>
                                </div>
                            </dl>
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('quotations.show', $quotation) }}" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View</a>
                                @if (! $quotation->isConverted())
                                    <a href="{{ route('quotations.edit', $quotation) }}" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Edit</a>
                                @endif
                                <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="inline-flex items-center justify-center border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Print quotation</a>
                                @if ($quotation->isAccepted() && ! $quotation->isConverted())
                                    <form method="POST" action="{{ route('quotations.convert', $quotation) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex w-full items-center justify-center border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:bg-amber-100">
                                            Convert to invoice
                                        </button>
                                    </form>
                                @elseif ($quotation->convertedInvoice)
                                    <a href="{{ route('invoices.show', $quotation->convertedInvoice) }}" class="inline-flex items-center justify-center border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 transition hover:bg-emerald-100">
                                        View invoice
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Quotation</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Valid until</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3">Items</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($quotations as $quotation)
                                @php
                                    $statusClasses = $quotation->isConverted()
                                        ? 'border-amber-200 bg-amber-50 text-amber-700'
                                        : ($quotation->isAccepted()
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : ($quotation->isRejected()
                                                ? 'border-rose-200 bg-rose-50 text-rose-700'
                                                : ($quotation->isExpired()
                                                    ? 'border-slate-300 bg-slate-100 text-slate-700'
                                                    : ($quotation->isSent()
                                                        ? 'border-sky-200 bg-sky-50 text-sky-700'
                                                        : 'border-violet-200 bg-violet-50 text-violet-700'))));
                                @endphp
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="font-semibold text-slate-900 transition hover:text-amber-700">
                                            {{ $quotation->quotation_number }}
                                        </a>
                                        <p class="mt-1 text-xs text-slate-500">Issued {{ $quotation->issue_date->format('M d, Y') }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $quotation->customer_name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="{{ $statusClasses }} inline-flex border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                                            {{ $quotation->displayStatus() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ optional($quotation->valid_until)->format('M d, Y') ?: 'Not set' }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-900">@ugx($quotation->total_amount)</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $quotation->items_count }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('quotations.show', $quotation) }}" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">View</a>
                                            @if (! $quotation->isConverted())
                                                <a href="{{ route('quotations.edit', $quotation) }}" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Edit</a>
                                            @endif
                                            <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="border border-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Print</a>
                                            @if ($quotation->isAccepted() && ! $quotation->isConverted())
                                                <form method="POST" action="{{ route('quotations.convert', $quotation) }}">
                                                    @csrf
                                                    <button type="submit" class="border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:bg-amber-100">
                                                        Convert
                                                    </button>
                                                </form>
                                            @elseif ($quotation->convertedInvoice)
                                                <a href="{{ route('invoices.show', $quotation->convertedInvoice) }}" class="border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 transition hover:bg-emerald-100">
                                                    Invoice
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
                    {{ $quotations->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
