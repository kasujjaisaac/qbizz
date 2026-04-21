<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $quotation->quotation_number }} | Print Quotation</title>
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
                border: 1px solid #b45309;
                background: #b45309;
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
                background: linear-gradient(90deg, #b45309, #f59e0b);
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
                color: #92400e;
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

            .status-pill.accepted {
                border-color: #a7f3d0;
                background: #ecfdf5;
                color: #047857;
            }

            .status-pill.rejected {
                border-color: #fecdd3;
                background: #fff1f2;
                color: #be123c;
            }

            .status-pill.converted {
                border-color: #fde68a;
                background: #fffbeb;
                color: #b45309;
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
            <button type="button" onclick="window.print()">Print quotation</button>
        </div>

        <div class="print-page" data-fit-page>
            <div class="print-page__content" data-fit-content>
                <div class="receipt-topbar"></div>

                <div class="receipt-inner">
                    <section class="brand">
                        @if ($quotation->businessProfile->logo_path)
                            <img class="brand-logo" src="{{ \Illuminate\Support\Facades\Storage::url($quotation->businessProfile->logo_path) }}" alt="Business logo">
                        @endif

                        <h1 class="brand-name">{{ $quotation->businessProfile->business_name }}</h1>

                        <p class="brand-meta">{{ $quotation->businessProfile->formattedAddress() }}</p>
                        <p class="brand-meta">{{ $quotation->businessProfile->contact_email }}</p>
                        @if ($quotation->businessProfile->phone)
                            <p class="brand-meta">{{ $quotation->businessProfile->phone }}</p>
                        @endif
                        @if ($quotation->businessProfile->tax_id)
                            <p class="brand-meta">Tax ID: {{ $quotation->businessProfile->tax_id }}</p>
                        @endif
                        @if ($quotation->businessProfile->website)
                            <p class="brand-meta">{{ $quotation->businessProfile->website }}</p>
                        @endif
                    </section>

                    <section class="section">
                        <p class="section-label">Quotation</p>
                        <h2 class="invoice-title">{{ $quotation->quotation_number }}</h2>

                        <div class="meta-grid">
                            <div class="meta-row">
                                <span>Issue date</span>
                                <span>{{ $quotation->issue_date->format('M d, Y') }}</span>
                            </div>
                            <div class="meta-row">
                                <span>Valid until</span>
                                <span>{{ optional($quotation->valid_until)->format('M d, Y') ?: 'Not set' }}</span>
                            </div>
                            <div class="meta-row">
                                <span>Status</span>
                                <span>{{ $quotation->displayStatus() }}</span>
                            </div>
                            @if ($quotation->convertedInvoice)
                                <div class="meta-row">
                                    <span>Invoice</span>
                                    <span>{{ $quotation->convertedInvoice->invoice_number }}</span>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="section">
                        <p class="section-label">Prepared For</p>
                        <p class="section-text" style="font-weight: 700; color: #0f172a;">{{ $quotation->customer_name }}</p>
                        @if ($quotation->customer_email)
                            <p class="section-text">{{ $quotation->customer_email }}</p>
                        @endif
                        @if ($quotation->customer_phone)
                            <p class="section-text">{{ $quotation->customer_phone }}</p>
                        @endif
                        @if ($quotation->customer_address)
                            <p class="section-text">{{ $quotation->customer_address }}</p>
                        @endif
                    </section>

                    <section class="section">
                        <p class="section-label">Items</p>

                        <div class="items">
                            @foreach ($quotation->items as $item)
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
                            <div class="total-row grand-total">
                                <span>Quotation total</span>
                                <span>@ugx($quotation->total_amount)</span>
                            </div>
                        </div>
                    </section>

                    @if ($quotation->notes)
                        <section class="section">
                            <p class="section-label">Notes</p>
                            <p class="section-text">{{ $quotation->notes }}</p>
                        </section>
                    @endif

                    <section class="section">
                        @php
                            $statusClasses = $quotation->isConverted()
                                ? 'converted'
                                : ($quotation->isAccepted()
                                    ? 'accepted'
                                    : ($quotation->isRejected() ? 'rejected' : ''));
                            $statusText = $quotation->isConverted()
                                ? 'Converted to invoice'
                                : ($quotation->isAccepted()
                                    ? 'Approved quotation'
                                    : ($quotation->isRejected()
                                        ? 'Rejected quotation'
                                        : ($quotation->isExpired()
                                            ? 'Expired quotation'
                                            : ($quotation->isSent() ? 'Shared with customer' : 'Draft quotation'))));
                        @endphp
                        <div class="status-pill {{ $statusClasses }}">
                            {{ $statusText }}
                        </div>
                    </section>

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
