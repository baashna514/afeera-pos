@php
    $company = $sale->company ?? auth()->user()?->company;
    $paperSize = company_setting('receipt.paper_size', '80mm');
    $containerWidth = match($paperSize) {
        '58mm' => '56mm',
        'a4' => '100%',
        default => '78mm',
    };
    $containerMaxWidth = match($paperSize) {
        '58mm' => '260px',
        'a4' => '780px',
        default => '320px',
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Receipt – {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace, -apple-system, sans-serif;
            font-size: {{ $paperSize === 'a4' ? '12px' : '11px' }};
            color: #000;
            background: #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            min-height: 100vh;
        }

        .receipt-container {
            background: #fff;
            width: {{ $containerWidth }};
            max-width: {{ $containerMaxWidth }};
            padding: 14px 12px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .brand-name {
            font-size: {{ $paperSize === 'a4' ? '18px' : '15px' }};
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .tagline {
            font-size: 9.5px;
            font-style: italic;
            color: #333;
            margin-bottom: 3px;
        }

        .branch-address {
            font-size: 10px;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .phone-numbers {
            font-size: 9.5px;
            margin-bottom: 6px;
        }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            line-height: 1.4;
        }

        .info-row {
            font-size: 10px;
            line-height: 1.4;
            margin-top: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 4px;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            font-weight: bold;
        }

        .items-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            font-size: 10.5px;
            margin-top: 4px;
        }

        .totals-table td {
            padding: 2px 0;
        }

        .net-total-row {
            font-size: {{ $paperSize === 'a4' ? '14px' : '12px' }};
            font-weight: 900;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
        }

        .policy-section {
            font-size: 8.5px;
            line-height: 1.3;
            margin-top: 8px;
            text-align: left;
        }

        .policy-title {
            font-size: 10px;
            font-weight: 900;
            text-align: center;
            margin-bottom: 3px;
        }

        .software-credit {
            font-size: 8px;
            color: #444;
            text-align: center;
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px dotted #888;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-print {
            background: #059669;
            color: #fff;
        }
        .btn-print:hover { background: #047857; }

        .btn-back {
            background: #475569;
            color: #fff;
        }
        .btn-back:hover { background: #334155; }

        @media print {
            body {
                background: #fff;
                padding: 0;
                min-height: auto;
            }
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
                padding: 4px 2px;
                width: 100%;
                max-width: 100%;
            }
            .action-buttons {
                display: none !important;
            }
            @page {
                margin: 0;
                size: auto;
            }
        }
    </style>
</head>
<body>
    <div>
        <div class="receipt-container" id="thermalReceipt">
            <!-- Store Header -->
            <div class="text-center">
                @if(company_setting('receipt.show_logo', true))
                    <div style="font-size: 22px; margin-bottom: 2px;">🏪</div>
                @endif
                <div class="brand-name">{{ $company->name ?? 'SMART POS SYSTEM' }}</div>
                @if($headerText = company_setting('receipt.header_text'))
                    <div class="tagline">{{ $headerText }}</div>
                @endif
                @if($company?->address)
                    <div class="branch-address">{{ $company->address }}</div>
                @endif
                @if($company?->phone)
                    <div class="phone-numbers">Mob #: {{ $company->phone }}</div>
                @endif
            </div>

            <div class="dashed-line"></div>

            <!-- Invoice Meta -->
            <div class="meta-row">
                <span class="font-bold">No . {{ $sale->invoice_number }}</span>
                <span>{{ $sale->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            @if(company_setting('receipt.show_customer_name', true))
                <div class="info-row">
                    <span class="font-bold">Customer: </span>
                    <span class="uppercase">{{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}</span>
                </div>
            @endif
            @if(company_setting('receipt.show_cashier_name', true))
                <div class="info-row">
                    <span class="font-bold">Cashier: </span>
                    <span>{{ auth()->user()->name ?? 'Cashier Staff' }}</span>
                </div>
            @endif
            <div class="meta-row">
                <span>Remarks: {{ $sale->description ?: ($sale->note ?: '-') }}</span>
                <span>Ref.: {{ $sale->extra_field_one ?: '-' }}</span>
            </div>

            <div class="dashed-line"></div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-left" style="width:48%;">Item Name</th>
                        <th class="text-center" style="width:14%;">Qty</th>
                        <th class="text-right" style="width:18%;">Price</th>
                        <th class="text-right" style="width:20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td class="text-left font-bold">
                                {{ $item->product->name ?? 'Deleted Item' }}
                                @if($item->unit)
                                    <span style="font-weight:normal; font-size:8.5px; color:#333;">({{ $item->unit->short_code }})</span>
                                @endif
                                @if(company_setting('receipt.show_sku', false) && !empty($item->product?->sku))
                                    <div style="font-size:8px; font-weight:normal; color:#555;">SKU: {{ $item->product->sku }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->price, 2) }}</td>
                            <td class="text-right font-bold">{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="dashed-line"></div>

            <!-- Summary & Totals -->
            <div class="meta-row">
                <span class="font-bold">Total items: {{ $sale->items->count() }} ({{ $sale->items->sum('quantity') }} Qty)</span>
            </div>

            <table class="totals-table">
                <tr>
                    <td class="text-right font-bold" style="width:65%;">Gross Total :</td>
                    <td class="text-right font-bold">{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
                @if($sale->paid_amount < $sale->total_amount)
                <tr>
                    <td class="text-right">Paid Amount :</td>
                    <td class="text-right">{{ number_format($sale->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right font-bold" style="color:#b91c1c;">Remaining Due :</td>
                    <td class="text-right font-bold" style="color:#b91c1c;">{{ number_format($sale->due_amount, 2) }}</td>
                </tr>
                @endif
                @if(company_setting('receipt.show_tax_breakdown', false))
                <tr>
                    <td class="text-right" style="color:#555;">Sales Tax Included :</td>
                    <td class="text-right" style="color:#555;">0.00</td>
                </tr>
                @endif
            </table>

            <div class="dashed-line"></div>

            <!-- Net Total -->
            <div class="meta-row net-total-row">
                <span class="uppercase">TOTAL PAYABLE</span>
                <span class="text-right">Rs. {{ number_format($sale->total_amount, 2) }}</span>
            </div>

            <!-- Status & Method -->
            <div class="meta-row" style="margin-top: 4px; font-size:9.5px;">
                <span>Payment: <strong class="uppercase">{{ str_replace('_', ' ', $sale->payment_method) }}</strong></span>
                <span>Status: <strong class="uppercase">{{ $sale->payment_status_label }}</strong></span>
            </div>

            @if(company_setting('receipt.show_barcode', false))
                <div class="text-center" style="margin: 8px 0;">
                    <div style="font-family: monospace; letter-spacing: 4px; font-size: 10px; background: #eee; padding: 2px 6px; display: inline-block;">
                        ||||| ||||||| |||| ||||||| |||
                    </div>
                    <div style="font-size: 8px; color: #555;">{{ $sale->invoice_number }}</div>
                </div>
            @endif

            @if(company_setting('receipt.show_qr_code', false))
                <div class="text-center" style="margin: 6px 0;">
                    <div style="font-size: 8px; color: #555;">[ QR CODE: {{ $sale->invoice_number }} ]</div>
                </div>
            @endif

            <!-- Return & Exchange Policy -->
            <div class="dashed-line"></div>
            <div class="policy-section">
                @if($footerText = company_setting('receipt.footer_text'))
                    <div class="text-center" style="font-weight:bold; margin-bottom:3px;">{{ $footerText }}</div>
                @else
                    <div class="text-center" style="font-weight:bold; margin-bottom:3px;">Thank you for shopping with us!</div>
                @endif

                @if($policy = company_setting('receipt.return_policy'))
                    <div class="policy-title">Return & Exchange Policy</div>
                    <div style="white-space: pre-line;">{{ $policy }}</div>
                @else
                    <div class="policy-title">Return & Exchange Policy.</div>
                    <div>• Items may be exchanged within 7 days with original receipt.</div>
                    <div>• Damaged or used items cannot be returned.</div>
                @endif
            </div>

            <!-- Credit Footer -->
            <div class="software-credit">
                Powered by SmartPOS System
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn btn-print" onclick="window.print()">🖨️ Print Receipt</button>
            <a href="{{ route('sales.index') }}" class="btn btn-back">← Back to Invoices</a>
            <a href="{{ route('sales.create') }}" class="btn btn-back">+ New Sale</a>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
