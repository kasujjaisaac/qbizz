<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $invoice->invoice_number }} | Print Invoice</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />
        <style>
            :root {
                --page-width: 80mm;
                --page-height: 257mm;
                --page-scale: 1;
            }

            @page {
                size: 80mm 257mm;
                margin: 0;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                padding: 20px 12px 28px;
                font-family: 'Poppins', sans-serif;
                color: #0f172a;
                background: #e2e8f0;
            }

            .toolbar {
                display: flex;
                justify-content: center;
                padding: 16px 12px;
            }

            .toolbar button {
                border: 1px solid #0b255f;
                background: #0b255f;
                color: #ffffff;
                padding: 12px 18px;
                font-size: 13px;
                font-weight: 700;
                cursor: pointer;
            }

            .print-page {
                width: min(100%, var(--page-width));
                height: var(--page-height);
                margin: 0 auto 24px;
                background: #ffffff;
                box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
                overflow: hidden;
            }

            .print-page__content {
                transform: scale(var(--page-scale));
                transform-origin: top center;
            }

            .receipt-topbar {
                height: 4px;
                background: linear-gradient(90deg, #173a7a, #2563eb);
            }

            .receipt-inner {
                padding: 5mm 5mm 6mm;
            }

            .brand {
                text-align: center;
                border-bottom: 1px dashed #cbd5e1;
                padding-bottom: 8px;
                margin-bottom: 8px;
            }

            .brand-logo {
                width: 38px;
                height: 38px;
                margin: 0 auto 6px;
                object-fit: contain;
                display: block;
            }

            .brand-name {
                margin: 0;
                font-size: 13px;
                line-height: 1.2;
                font-weight: 700;
                color: #0b255f;
            }

            .brand-meta,
            .section-text {
                margin: 3px 0 0;
                font-size: 8.5px;
                line-height: 1.35;
                color: #475569;
                white-space: pre-line;
                overflow-wrap: anywhere;
            }

            .section {
                padding: 7px 0;
                border-bottom: 1px dashed #cbd5e1;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .section:last-child {
                border-bottom: 0;
                padding-bottom: 0;
            }

            .section-label {
                margin: 0 0 4px;
                font-size: 7px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                color: #64748b;
            }

            .invoice-title {
                margin: 0;
                font-size: 13px;
                font-weight: 700;
                color: #0f172a;
            }

            .meta-grid {
                display: grid;
                gap: 4px;
                margin-top: 6px;
            }

            .meta-row,
            .line-row,
            .total-row {
                display: flex;
                justify-content: space-between;
                gap: 8px;
                align-items: flex-start;
            }

            .meta-row span:first-child,
            .line-sub {
                color: #64748b;
                font-size: 8px;
            }

            .meta-row span:last-child {
                text-align: right;
                font-size: 8.5px;
                font-weight: 600;
                overflow-wrap: anywhere;
            }

            .items {
                display: grid;
                gap: 6px;
                margin-top: 6px;
            }

            .line-details {
                min-width: 0;
                flex: 1;
            }

            .line-row {
                padding-bottom: 5px;
                border-bottom: 1px dotted #e2e8f0;
            }

            .line-row:last-child {
                padding-bottom: 0;
                border-bottom: 0;
            }

            .line-title {
                font-size: 9px;
                font-weight: 700;
                color: #0f172a;
                overflow-wrap: anywhere;
            }

            .line-total {
                font-size: 8.5px;
                font-weight: 700;
                white-space: nowrap;
            }

            .totals {
                display: grid;
                gap: 4px;
                margin-top: 5px;
            }

            .total-row {
                font-size: 8.5px;
                color: #0f172a;
            }

            .total-row.grand-total {
                border-top: 1px solid #cbd5e1;
                padding-top: 5px;
                font-size: 9.5px;
                font-weight: 700;
            }

            .status-pill {
                display: inline-flex;
                justify-content: center;
                width: 100%;
                border: 1px solid #cbd5e1;
                background: #f8fafc;
                padding: 6px 8px;
                font-size: 8px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                color: #0f172a;
            }

            .status-pill.settled {
                border-color: #a7f3d0;
                background: #ecfdf5;
                color: #047857;
            }

            .status-pill.partial {
                border-color: #fde68a;
                background: #fffbeb;
                color: #b45309;
            }

            .signature-role {
                font-size: 7px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                color: #64748b;
            }

            .signature-name {
                margin-top: 3px;
                font-size: 8.5px;
                line-height: 1.35;
                color: #475569;
                white-space: pre-line;
            }

            .signature-block {
                margin-top: 2px;
                text-align: center;
            }

            .signature-image {
                display: block;
                max-width: 110px;
                max-height: 34px;
                margin: 0 auto 4px;
                object-fit: contain;
            }

            .receipt-footer {
                text-align: center;
                padding-top: 5px;
                font-size: 8px;
                line-height: 1.35;
                color: #64748b;
            }

            @media print {
                html,
                body {
                    margin: 0;
                    padding: 0;
                    background: #ffffff;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .toolbar {
                    display: none;
                }

                .print-page {
                    width: var(--page-width);
                    max-width: none;
                    height: var(--page-height);
                    margin: 0 auto;
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="toolbar">
            <button type="button" onclick="window.print()">Print invoice</button>
        </div>

        <div class="print-page" data-fit-page>
            <div class="print-page__content" data-fit-content>
                <div class="receipt-topbar"></div>

                <div class="receipt-inner">
                    <section class="brand">
                        @if ($invoice->businessProfile->logo_path)
                            <img class="brand-logo" src="{{ \Illuminate\Support\Facades\Storage::url($invoice->businessProfile->logo_path) }}" alt="Business logo">
                        @endif

                        <h1 class="brand-name">{{ $invoice->businessProfile->business_name }}</h1>

                        <p class="brand-meta">{{ $invoice->businessProfile->formattedAddress() }}</p>
                        <p class="brand-meta">{{ $invoice->businessProfile->contact_email }}</p>
                        @if ($invoice->businessProfile->phone)
                            <p class="brand-meta">{{ $invoice->businessProfile->phone }}</p>
                        @endif
                        @if ($invoice->businessProfile->tax_id)
                            <p class="brand-meta">Tax ID: {{ $invoice->businessProfile->tax_id }}</p>
                        @endif
                        @if ($invoice->businessProfile->website)
                            <p class="brand-meta">{{ $invoice->businessProfile->website }}</p>
                        @endif
                    </section>

                    <section class="section">
                        <p class="section-label">Invoice</p>
                        <h2 class="invoice-title">{{ $invoice->invoice_number }}</h2>

                        <div class="meta-grid">
                            <div class="meta-row">
                                <span>Issue date</span>
                                <span>{{ $invoice->issue_date->format('M d, Y') }}</span>
                            </div>
                            <div class="meta-row">
                                <span>Due date</span>
                                <span>{{ optional($invoice->due_date)->format('M d, Y') ?: 'Not set' }}</span>
                            </div>
                            <div class="meta-row">
                                <span>Status</span>
                                <span>{{ $invoice->isSettled() ? 'Settled' : ($invoice->isPartial() ? 'Partial' : 'Active') }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="section">
                        <p class="section-label">Bill To</p>
                        <p class="section-text" style="font-weight: 700; color: #0f172a;">{{ $invoice->customer_name }}</p>
                        @if ($invoice->customer_email)
                            <p class="section-text">{{ $invoice->customer_email }}</p>
                        @endif
                        @if ($invoice->customer_phone)
                            <p class="section-text">{{ $invoice->customer_phone }}</p>
                        @endif
                        @if ($invoice->customer_address)
                            <p class="section-text">{{ $invoice->customer_address }}</p>
                        @endif
                    </section>

                    <section class="section">
                        <p class="section-label">Items</p>

                        <div class="items">
                            @foreach ($invoice->items as $item)
                                <div class="line-row">
                                    <div class="line-details">
                                        <div class="line-title">{{ $item->description }}</div>
                                        <div class="line-sub">{{ number_format((float) $item->quantity, 2) }} x @ugx($item->unit_price)</div>
                                    </div>
                                    <div class="line-total">@ugx($item->line_total)</div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="section">
                        <div class="totals">
                            <div class="total-row">
                                <span>Invoice total</span>
                                <span>@ugx($invoice->total_amount)</span>
                            </div>

                            <div class="total-row">
                                <span>Amount paid</span>
                                <span>@ugx($invoice->paid_amount)</span>
                            </div>

                            @if ($invoice->settled_at)
                                <div class="total-row">
                                    <span>Settled on</span>
                                    <span>{{ $invoice->settled_at->format('M d, Y') }}</span>
                                </div>
                            @endif

                            <div class="total-row grand-total">
                                <span>Amount due</span>
                                <span>@ugx($invoice->balanceDue())</span>
                            </div>
                        </div>
                    </section>

                    @if ($invoice->notes)
                        <section class="section">
                            <p class="section-label">Notes</p>
                            <p class="section-text">{{ $invoice->notes }}</p>
                        </section>
                    @endif

                    @if ($invoice->receipts->isNotEmpty())
                        <section class="section">
                            <p class="section-label">Receipts</p>
                            <div class="items">
                                @foreach ($invoice->receipts as $receipt)
                                    <div class="line-row">
                                        <div class="line-details">
                                            <div class="line-title">{{ $receipt->receipt_number }}</div>
                                            <div class="line-sub">{{ $receipt->payment_date->format('M d, Y') }} · Balance after @ugx($receipt->balance_after)</div>
                                        </div>
                                        <div class="line-total">@ugx($receipt->amount_received)</div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="section">
                        <div class="status-pill {{ $invoice->isSettled() ? 'settled' : ($invoice->isPartial() ? 'partial' : '') }}">
                            {{ $invoice->isSettled() ? 'Payment received' : ($invoice->isPartial() ? 'Partially paid' : 'Awaiting payment') }}
                        </div>
                    </section>

                    @if ($invoice->businessProfile->signature_path)
                        <section class="section">
                            <p class="section-label">Authorization</p>
                            <div class="signature-block">
                                <img class="signature-image" src="{{ \Illuminate\Support\Facades\Storage::url($invoice->businessProfile->signature_path) }}" alt="Authorized signature">
                                <div class="signature-name">{{ $invoice->businessProfile->business_name }}</div>
                                @if ($invoice->businessProfile->issuer_title)
                                    <div class="signature-role">{{ $invoice->businessProfile->issuer_title }}</div>
                                @endif
                            </div>
                        </section>
                    @endif

                    <div class="receipt-footer">
                        Printed from {{ config('app.name', 'Qbizz') }}.
                    </div>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const page = document.querySelector('[data-fit-page]');
                const content = document.querySelector('[data-fit-content]');

                if (!page || !content) {
                    return;
                }

                const fitToSinglePage = () => {
                    page.style.setProperty('--page-scale', '1');

                    const availableHeight = page.clientHeight;
                    const contentHeight = content.scrollHeight;

                    if (!availableHeight || !contentHeight) {
                        return;
                    }

                    const scale = Math.min(1, availableHeight / contentHeight);

                    page.style.setProperty('--page-scale', scale.toFixed(4));
                };

                window.addEventListener('load', fitToSinglePage);
                window.addEventListener('resize', fitToSinglePage);
                window.addEventListener('beforeprint', fitToSinglePage);

                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(fitToSinglePage);
                }
            })();
        </script>
    </body>
</html>
