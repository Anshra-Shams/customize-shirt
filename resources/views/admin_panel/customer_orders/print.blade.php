<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Slip - {{ $order->order_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #0f172a;
        }
        .slip-actions {
            max-width: 820px;
            margin: 0 auto 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1e293b;
        }
        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }
        .slip-container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 28px 32px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
        }
        .slip-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }
        .company-name {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .company-meta {
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
        }
        .slip-badge {
            text-align: right;
        }
        .slip-badge h2 {
            margin: 0 0 4px;
            font-size: 18px;
            color: #2563eb;
            text-transform: uppercase;
        }
        .slip-badge div {
            font-size: 13px;
            color: #334155;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            line-height: 1.6;
        }
        .info-box-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        th {
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        td {
            padding: 9px 10px;
            border: 1px solid #e2e8f0;
            font-size: 13px;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .notes-box {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
        }
        .totals-box {
            width: 280px;
            font-size: 13px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }
        .totals-row.grand {
            border-top: 2px solid #0f172a;
            margin-top: 6px;
            padding-top: 8px;
            font-size: 15px;
            font-weight: 800;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .no-print { display: none !important; }
            .slip-container { box-shadow: none; border: none; padding: 10px; max-width: 100%; }
        }
    </style>
</head>
<body>
    @php
        $companyName = \App\Models\Setting::get('company_name', 'TrendHub');
        $companyPhone = \App\Models\Setting::get('company_phone', '');
        $companyAddress = \App\Models\Setting::get('company_address', '');
    @endphp

    <div class="slip-actions no-print">
        <a href="{{ route('customer_orders.show', $order->id) }}" class="btn">&larr; Back to Order</a>
        <button type="button" onclick="window.print()" class="btn btn-primary">Print Order Slip</button>
    </div>

    <div class="slip-container">
        <div class="slip-header">
            <div>
                <h1 class="company-name">{{ $companyName }}</h1>
                <div class="company-meta">
                    @if ($companyAddress) <div>{{ $companyAddress }}</div> @endif
                    @if ($companyPhone) <div>Phone: {{ $companyPhone }}</div> @endif
                </div>
            </div>
            <div class="slip-badge">
                <h2>Customer Order Slip</h2>
                <div><strong>Order #:</strong> {{ $order->order_no }}</div>
                <div><strong>Status:</strong> {{ \App\Models\CustomerOrder::STATUSES[$order->status] ?? ucfirst($order->status) }}</div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <div class="info-box-title">Customer Information</div>
                <div><strong>Name:</strong> {{ $order->customer_display_name }}</div>
                <div><strong>Phone:</strong> {{ $order->customer_phone ?: 'N/A' }}</div>
                @if ($order->customer_address)
                    <div><strong>Address:</strong> {{ $order->customer_address }}</div>
                @endif
            </div>
            <div class="info-box">
                <div class="info-box-title">{{ $order->status === 'delivered' ? 'Delivery & Schedule' : 'Order Schedule' }}</div>
                <div><strong>Order Date:</strong> {{ $order->order_date ? $order->order_date->format('d M Y') : '-' }}</div>
                <div><strong>Target Delivery:</strong> {{ $order->delivery_date ? $order->delivery_date->format('d M Y') : 'Not Specified' }}</div>
                @if ($order->status === 'delivered' && $order->delivered_at)
                    <div><strong>Delivered On:</strong> {{ $order->delivered_at->format('d M Y, h:i A') }}</div>
                @endif
                @if ($order->received_by)
                    <div><strong>Received By:</strong> {{ $order->received_by }}</div>
                @endif
                @if ($order->creator)
                    <div><strong>Taken By:</strong> {{ $order->creator->name }}</div>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">#</th>
                    <th>Order Item</th>
                    <th>Description / Specs</th>
                    <th class="text-center" style="width: 70px;">Qty</th>
                    <th class="text-center" style="width: 65px;">Unit</th>
                    <th class="text-right" style="width: 95px;">Rate</th>
                    <th class="text-right" style="width: 105px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $item->item_name }}</strong></td>
                        <td>{{ $item->description ?: '-' }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}</td>
                        <td class="text-center">{{ $item->unit ?: 'Pcs' }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right"><strong>{{ number_format($item->total, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($order->description)
            <div class="notes-box">
                <strong>Order Description / Instructions:</strong><br>
                {{ $order->description }}
            </div>
        @endif

        <div class="totals-wrap">
            <div class="totals-box">
                <div class="totals-row">
                    <span>Total Quantity:</span>
                    <strong>{{ rtrim(rtrim(number_format($order->total_qty, 2), '0'), '.') }}</strong>
                </div>
                <div class="totals-row">
                    <span>Subtotal:</span>
                    <strong>Rs {{ number_format($order->subtotal, 2) }}</strong>
                </div>
                @if ($order->discount > 0)
                    <div class="totals-row">
                        <span>Discount:</span>
                        <strong>Rs {{ number_format($order->discount, 2) }}</strong>
                    </div>
                @endif
                <div class="totals-row grand">
                    <span>Estimated Total:</span>
                    <span>Rs {{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="totals-row" style="margin-top: 6px; color: #059669;">
                    <span>Advance Paid:</span>
                    <strong>Rs {{ number_format($order->advance_amount, 2) }}</strong>
                </div>
                @if ((float)($order->delivery_payment_amount ?? 0) > 0)
                    <div class="totals-row" style="color: #059669;">
                        <span>Delivery Payment:</span>
                        <strong>Rs {{ number_format($order->delivery_payment_amount, 2) }}</strong>
                    </div>
                @endif
                <div class="totals-row" style="color: {{ $order->remaining_balance > 0 ? '#dc2626' : '#059669' }};">
                    <span>{{ $order->remaining_balance > 0 ? 'Remaining Balance:' : 'Balance Settled:' }}</span>
                    <strong>Rs {{ number_format($order->remaining_balance, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
