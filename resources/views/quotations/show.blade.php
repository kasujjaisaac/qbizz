<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Quotations</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $quotation->quotation_number }}</h1>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap">
                <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="inline-flex w-full items-center justify-center rounded-none border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto">
                    Print quotation
                </a>

                @if (! $quotation->isConverted())
                    <a href="{{ route('quotations.edit', $quotation) }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto">
                        Edit quotation
                    </a>
                @endif

                @if ($quotation->convertedInvoice)
                    <a href="{{ route('invoices.show', $quotation->convertedInvoice) }}" class="inline-flex w-full items-center justify-center rounded-none border border-emerald-600 bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
                        View invoice
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @php
                $statusClasses = $quotation->isConverted()
                    ? 'text-amber-600'
                    : ($quotation->isAccepted()
                        ? 'text-emerald-600'
                        : ($quotation->isRejected()
                            ? 'text-rose-600'
                            : ($quotation->isExpired()
                                ? 'text-slate-700'
                                : ($quotation->isSent() ? 'text-sky-700' : 'text-violet-700'))));
            @endphp
            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Status</p>
                <p class="mt-4 text-2xl font-semibold {{ $statusClasses }}">
                    {{ $quotation->displayStatus() }}
                </p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Issue date</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">{{ $quotation->issue_date->format('M d, Y') }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Valid until</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">{{ optional($quotation->valid_until)->format('M d, Y') ?: 'Not set' }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Quotation total</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">@ugx($quotation->total_amount)</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Conversion</p>
                <p class="mt-4 text-xl font-semibold text-slate-950">
                    {{ $quotation->convertedInvoice ? $quotation->convertedInvoice->invoice_number : 'Not converted' }}
                </p>
                @if ($quotation->converted_at)
                    <p class="mt-2 text-sm text-slate-600">{{ $quotation->converted_at->format('M d, Y g:i A') }}</p>
                @endif
            </article>
        </section>

        <section class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 border-b border-slate-200 pb-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Actions</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Move the quotation forward</h2>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    @if ($quotation->isDraft())
                        <form method="POST" action="{{ route('quotations.send', $quotation) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-none border border-sky-600 bg-sky-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-500 sm:w-auto">
                                Mark as sent
                            </button>
                        </form>
                    @endif

                    @if (! $quotation->isAccepted() && ! $quotation->isRejected() && ! $quotation->isConverted())
                        <form method="POST" action="{{ route('quotations.accept', $quotation) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-none border border-emerald-600 bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
                                Accept quotation
                            </button>
                        </form>
                    @endif

                    @if (! $quotation->isRejected() && ! $quotation->isConverted())
                        <form method="POST" action="{{ route('quotations.reject', $quotation) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-none border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 sm:w-auto">
                                Mark as rejected
                            </button>
                        </form>
                    @endif

                    @if ($quotation->isAccepted() && ! $quotation->isConverted())
                        <form method="POST" action="{{ route('quotations.convert', $quotation) }}">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-none border border-amber-600 bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-500 sm:w-auto">
                                Convert to invoice
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-6 border-b border-slate-200 pb-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    @if ($quotation->businessProfile->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($quotation->businessProfile->logo_path) }}" alt="Business logo" class="h-16 w-16 object-contain">
                    @endif

                    <div>
                        <h2 class="text-2xl font-semibold text-slate-950">{{ $quotation->businessProfile->business_name }}</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $quotation->businessProfile->formattedAddress() }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $quotation->businessProfile->contact_email }}{{ $quotation->businessProfile->phone ? ' · '.$quotation->businessProfile->phone : '' }}</p>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Prepared for</p>
                        <div class="mt-3 text-sm leading-7 text-slate-600">
                            <p class="font-semibold text-slate-900">{{ $quotation->customer_name }}</p>
                            @if ($quotation->customer_email)
                                <p>{{ $quotation->customer_email }}</p>
                            @endif
                            @if ($quotation->customer_phone)
                                <p>{{ $quotation->customer_phone }}</p>
                            @endif
                            @if ($quotation->customer_address)
                                <p class="whitespace-pre-line">{{ $quotation->customer_address }}</p>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Quotation details</p>
                        <div class="mt-3 space-y-2 text-sm text-slate-600">
                            <p><span class="font-semibold text-slate-900">Number:</span> {{ $quotation->quotation_number }}</p>
                            <p><span class="font-semibold text-slate-900">Issue date:</span> {{ $quotation->issue_date->format('M d, Y') }}</p>
                            <p><span class="font-semibold text-slate-900">Valid until:</span> {{ optional($quotation->valid_until)->format('M d, Y') ?: 'Not set' }}</p>
                            <p><span class="font-semibold text-slate-900">Status:</span> {{ $quotation->displayStatus() }}</p>
                            @if ($quotation->convertedInvoice)
                                <p><span class="font-semibold text-slate-900">Invoice:</span> {{ $quotation->convertedInvoice->invoice_number }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3 md:hidden">
                @foreach ($quotation->items as $item)
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
                    <span>@ugx($quotation->total_amount)</span>
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
                        @foreach ($quotation->items as $item)
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
                            <td class="px-4 py-4 text-lg font-semibold text-slate-950">@ugx($quotation->total_amount)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($quotation->notes)
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Notes</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $quotation->notes }}</p>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
