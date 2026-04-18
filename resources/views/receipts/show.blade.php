<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Receipts</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $receipt->receipt_number }}</h1>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap">
                <a href="{{ route('receipts.print', $receipt) }}" target="_blank" class="inline-flex w-full items-center justify-center rounded-none border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto">
                    Print receipt
                </a>

                @if ($receipt->invoice)
                    <a href="{{ route('invoices.show', $receipt->invoice) }}" class="inline-flex w-full items-center justify-center rounded-none border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto">
                        View invoice
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="border border-emerald-200 bg-emerald-50 p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Amount received</p>
                <p class="mt-4 text-2xl font-semibold text-emerald-700">@ugx($receipt->amount_received)</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Payment date</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">{{ $receipt->payment_date->format('M d, Y') }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Source</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">{{ $receipt->invoice ? 'Invoice linked' : 'Standalone' }}</p>
            </article>

            <article class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Balance after</p>
                <p class="mt-4 text-2xl font-semibold text-slate-950">
                    @if ($receipt->balance_after !== null)
                        @ugx($receipt->balance_after)
                    @else
                        N/A
                    @endif
                </p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_380px]">
            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:p-8">
                <div class="flex flex-col gap-6 border-b border-slate-200 pb-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-3">
                        @if ($receipt->businessProfile->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($receipt->businessProfile->logo_path) }}" alt="Business logo" class="h-16 w-16 object-contain">
                        @endif

                        <div>
                            <h2 class="text-2xl font-semibold text-slate-950">{{ $receipt->businessProfile->business_name }}</h2>
                            <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $receipt->businessProfile->formattedAddress() }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ $receipt->businessProfile->contact_email }}{{ $receipt->businessProfile->phone ? ' · '.$receipt->businessProfile->phone : '' }}</p>
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Received from</p>
                            <div class="mt-3 text-sm leading-7 text-slate-600">
                                <p class="font-semibold text-slate-900">{{ $receipt->payer_name }}</p>
                                @if ($receipt->payer_email)
                                    <p>{{ $receipt->payer_email }}</p>
                                @endif
                                @if ($receipt->payer_phone)
                                    <p>{{ $receipt->payer_phone }}</p>
                                @endif
                                @if ($receipt->payer_address)
                                    <p class="whitespace-pre-line">{{ $receipt->payer_address }}</p>
                                @endif
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Receipt details</p>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                <p><span class="font-semibold text-slate-900">Receipt number:</span> {{ $receipt->receipt_number }}</p>
                                <p><span class="font-semibold text-slate-900">Payment date:</span> {{ $receipt->payment_date->format('M d, Y') }}</p>
                                @if ($receipt->payment_method)
                                    <p><span class="font-semibold text-slate-900">Payment method:</span> {{ $receipt->payment_method }}</p>
                                @endif
                                @if ($receipt->reference_number)
                                    <p><span class="font-semibold text-slate-900">Reference:</span> {{ $receipt->reference_number }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <article class="border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Amount received</p>
                        <p class="mt-3 text-xl font-semibold text-slate-950">@ugx($receipt->amount_received)</p>
                    </article>

                    <article class="border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Balance before</p>
                        <p class="mt-3 text-xl font-semibold text-slate-950">
                            @if ($receipt->balance_before !== null)
                                @ugx($receipt->balance_before)
                            @else
                                N/A
                            @endif
                        </p>
                    </article>

                    <article class="border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Balance after</p>
                        <p class="mt-3 text-xl font-semibold text-slate-950">
                            @if ($receipt->balance_after !== null)
                                @ugx($receipt->balance_after)
                            @else
                                N/A
                            @endif
                        </p>
                    </article>
                </div>

                @if ($receipt->notes)
                    <div class="mt-6 border-t border-slate-200 pt-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Notes</p>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $receipt->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                @if ($receipt->invoice)
                    <div class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Linked invoice</p>
                        <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $receipt->invoice->invoice_number }}</h2>
                        <dl class="mt-5 space-y-4 text-sm text-slate-600">
                            <div>
                                <dt class="font-semibold text-slate-900">Customer</dt>
                                <dd class="mt-1">{{ $receipt->invoice->customer_name }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-slate-900">Invoice total</dt>
                                <dd class="mt-1">@ugx($receipt->invoice_total_snapshot ?? $receipt->invoice->total_amount)</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-slate-900">Invoice balance now</dt>
                                <dd class="mt-1">@ugx($receipt->invoice->balanceDue())</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <div class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Actions</p>
                    <div class="mt-5 space-y-3">
                        <a href="{{ route('receipts.print', $receipt) }}" target="_blank" class="flex items-center justify-between border border-slate-200 border-l-4 border-l-sky-500 px-4 py-4 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:border-l-sky-600 hover:bg-slate-50">
                            <span>Print this receipt</span>
                            <span class="text-slate-400">↗</span>
                        </a>

                        @if ($receipt->invoice && $receipt->invoice->isOpen())
                            <a href="{{ route('receipts.create', ['invoice' => $receipt->invoice->id]) }}" class="flex items-center justify-between border border-slate-200 border-l-4 border-l-emerald-500 px-4 py-4 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:border-l-emerald-600 hover:bg-slate-50">
                                <span>Record another payment</span>
                                <span class="text-slate-400">+</span>
                            </a>
                        @endif

                        <a href="{{ route('receipts.index') }}" class="flex items-center justify-between border border-slate-200 border-l-4 border-l-amber-500 px-4 py-4 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:border-l-amber-600 hover:bg-slate-50">
                            <span>Back to receipts</span>
                            <span class="text-slate-400">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
