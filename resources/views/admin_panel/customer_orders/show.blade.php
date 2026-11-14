@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendors/bootstrap-icons/css/bootstrap-icons.min.css') }}" rel="stylesheet">

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        overflow-x: hidden !important;
    }

    .co-main-container {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -4px rgba(0, 0, 0, 0.03) !important;
        background-color: #ffffff !important;
        padding: 18px !important;
        font-size: .85rem;
        width: 100% !important;
        max-width: 100% !important;
    }

    .co-card-panel {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        padding: 18px !important;
        height: 100%;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }

    .co-section-title {
        font-weight: 800 !important;
        text-transform: uppercase;
        font-size: 0.78rem !important;
        letter-spacing: 0.8px !important;
        color: #1e293b !important;
        margin-bottom: 14px !important;
        border-left: 4px solid #2563eb !important;
        padding-left: 10px !important;
    }

    .co-readonly-box {
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        background-color: #f8fafc;
        padding: 7px 12px;
        font-weight: 600;
        color: #0f172a;
        font-size: 0.85rem;
        min-height: 38px;
        display: flex;
        align-items: center;
    }

    .co-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.38rem 0.85rem;
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .co-badge-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .co-badge-in_progress { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .co-badge-ready { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .co-badge-delivered { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .co-badge-cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .co-table-responsive {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        overflow-x: auto !important;
        background-color: #ffffff;
    }

    .co-items-table {
        border-collapse: collapse !important;
        margin-bottom: 0 !important;
        width: 100% !important;
        min-width: 820px;
    }

    .co-items-table thead th {
        background-color: #f8fafc !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 11px !important;
        letter-spacing: 0.5px;
        padding: 10px 12px !important;
        border: 1px solid #cbd5e1 !important;
        border-bottom: 2px solid #94a3b8 !important;
        vertical-align: middle !important;
    }

    .co-items-table tbody td {
        border: 1px solid #cbd5e1 !important;
        padding: 10px 12px !important;
        background-color: #ffffff;
        vertical-align: middle !important;
        font-size: 0.84rem;
        color: #1e293b;
    }

    .co-items-table tbody tr:hover td {
        background-color: #f8fafc !important;
    }

    .co-items-table tfoot td {
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-top: 2px solid #94a3b8 !important;
        padding: 9px 12px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    .co-unit-pill {
        display: inline-block;
        border: 1px solid #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 2px 10px;
    }

    .co-status-form .form-select {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 8px !important;
        height: 38px !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
    }

    .co-btn-primary {
        background-color: #2563eb !important;
        border: 1.5px solid #1d4ed8 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        padding: 7px 16px !important;
        font-size: 0.84rem !important;
    }
    .co-btn-primary:hover { background-color: #1d4ed8 !important; color: #ffffff !important; }

    .co-btn-secondary {
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        padding: 7px 16px !important;
        font-size: 0.84rem !important;
    }
    .co-btn-secondary:hover { background-color: #f1f5f9 !important; color: #0f172a !important; }
</style>

@php
    $isOverdue = $order->delivery_date
        && $order->delivery_date->isPast()
        && !$order->delivery_date->isToday()
        && !in_array($order->status, ['delivered', 'cancelled']);
    $isDueToday = $order->delivery_date
        && $order->delivery_date->isToday()
        && !in_array($order->status, ['delivered', 'cancelled']);
@endphp

<div class="container-fluid py-2 px-2">
    <div class="co-main-container bg-white mx-auto">

        {{-- TOP HEADER & ORDER OVERVIEW CARD --}}
        <div class="co-card-panel mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 border-bottom pb-2">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <a href="{{ route('customer_orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <h5 class="text-dark fw-bold mb-0 ms-2">
                        Customer Order #{{ $order->order_no }}
                    </h5>
                    <span class="co-badge co-badge-{{ $order->status }}">
                        {{ $statuses[$order->status] ?? ucfirst($order->status) }}
                    </span>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    @if ($order->status !== 'delivered' && $order->status !== 'cancelled')
                        <a href="{{ route('sales.from.order', $order->id) }}" class="btn btn-sm btn-success fw-bold text-white shadow-sm">
                            <i class="bi bi-truck me-1"></i> Deliver Order
                        </a>
                    @elseif ($order->sale_id)
                        <a href="{{ route('sales.invoice', $order->sale_id) }}" target="_blank" class="btn btn-sm btn-outline-success fw-bold">
                            <i class="bi bi-file-earmark-text me-1"></i> View Sale Invoice
                        </a>
                    @endif
                    <a href="{{ route('customer_orders.print', $order->id) }}" target="_blank" class="btn btn-sm co-btn-secondary">
                        <i class="bi bi-printer me-1"></i> Print Slip
                    </a>
                    <a href="{{ route('customer_orders.edit', $order->id) }}" class="btn btn-sm co-btn-primary">
                        <i class="bi bi-pencil-square me-1"></i> Edit Order
                    </a>
                </div>
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-1 text-muted small">Order No.</label>
                    <div class="co-readonly-box text-primary">{{ $order->order_no }}</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1 text-muted small">
                        Customer
                        @if (!$order->customer_id)
                            <span class="badge bg-light text-secondary border ms-1" style="font-size: 10px;">Walk-in</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 10px;">Registered</span>
                        @endif
                    </label>
                    <div class="co-readonly-box">{{ $order->customer_display_name }}</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold mb-1 text-muted small">Order Date</label>
                    <div class="co-readonly-box">
                        {{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold mb-1 text-muted small">
                        Delivery Date
                        @if ($isOverdue)
                            <span class="badge bg-danger ms-1" style="font-size: 9px;">Overdue</span>
                        @elseif ($isDueToday)
                            <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">Due Today</span>
                        @endif
                    </label>
                    <div class="co-readonly-box {{ $isOverdue ? 'text-danger border-danger' : ($isDueToday ? 'text-warning-emphasis border-warning' : 'text-primary') }}">
                        {{ $order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'Not Specified' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1 text-muted small">Quick Status Update</label>
                    <form action="{{ route('customer_orders.status', $order->id) }}" method="POST" class="d-flex gap-1 co-status-form" id="coShowStatusForm">
                        @csrf
                        <select name="status" class="form-select" id="coShowStatusSelect"
                            data-prev-status="{{ $order->status }}"
                            data-prepare-url="{{ route('customer_orders.prepare', $order->id) }}"
                            data-deliver-url="{{ route('sales.from.order', $order->id) }}">
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-success fw-bold px-3" style="height: 38px; border-radius: 8px;">
                            Update
                        </button>
                    </form>
                </div>
            </div>

            {{-- Customer Contact & Meta Strip --}}
            <div class="mt-3 p-2 border rounded-3 bg-light">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill text-primary"></i>
                        <span class="fw-bold text-muted small">Phone:</span>
                        <span class="fw-semibold text-dark small">{{ $order->customer_phone ?: 'N/A' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-primary"></i>
                        <span class="fw-bold text-muted small">Address:</span>
                        <span class="fw-semibold text-dark small">{{ $order->customer_address ?: 'No address provided' }}</span>
                    </div>
                    @if ($order->creator)
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-badge text-primary"></i>
                            <span class="fw-bold text-muted small">Taken By:</span>
                            <span class="fw-semibold text-dark small">{{ $order->creator->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ORDERED ITEMS TABLE --}}
        <div class="co-card-panel mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="co-section-title mb-0">Ordered Items ({{ $order->items->count() }})</div>
                <span class="badge bg-light text-secondary border px-3 py-2 fw-semibold">
                    Total Qty: {{ rtrim(rtrim(number_format($order->total_qty, 2), '0'), '.') }}
                </span>
            </div>

            <div class="co-table-responsive">
                <table class="co-items-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">#</th>
                            <th class="text-start" style="width: 28%;">Order Item</th>
                            <th class="text-start" style="width: 30%;">Description / Specs</th>
                            <th class="text-center" style="width: 9%;">Unit</th>
                            <th class="text-center" style="width: 9%;">Qty</th>
                            <th class="text-end" style="width: 11%;">Unit Price</th>
                            <th class="text-end" style="width: 13%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $idx => $item)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                    @if ($item->product)
                                        <small class="text-muted">Code: {{ $item->product->item_code ?? $item->product_id }}</small>
                                    @endif
                                </td>
                                <td class="text-secondary">
                                    <div>{{ $item->description ?: '-' }}</div>
                                    @if (is_array($item->materials) && count($item->materials) > 0)
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach ($item->materials as $mat)
                                                <span class="badge {{ !empty($mat['is_custom']) ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-dark border' }}" style="font-size: 11px; font-weight: 500;">
                                                    {{ $mat['name'] ?: 'Material' }}: Rs {{ rtrim(rtrim(number_format((float)($mat['amount'] ?? 0), 2), '0'), '.') }}
                                                    @if (!empty($mat['is_custom']))
                                                        <span class="ms-1 fw-bold" style="font-size: 9.5px; text-transform: lowercase;">(customize)</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="co-unit-pill">{{ $item->unit ?: 'Pcs' }}</span>
                                </td>
                                <td class="text-center fw-bold">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}</td>
                                <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-bold text-dark bg-light">Rs {{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end text-muted">Sub-Total Amount:</td>
                            <td class="text-end fs-6 text-dark">Rs {{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- BOTTOM SECTION: DESCRIPTION + SUMMARY --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-7">
                <div class="co-card-panel">
                    <div class="co-section-title">Order Description &amp; Instructions</div>
                    <div class="p-3 bg-light rounded-3 border text-dark mb-3" style="min-height: 110px; white-space: pre-line;">{{ $order->description ?: 'No general order instructions or description added.' }}</div>
                    <div class="p-2 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="small text-primary fw-semibold">
                            <i class="bi bi-shield-check me-1"></i> Safe Order Mode: Stock &amp; POS are not affected. Advance payments post to selected Account Ledger.
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="co-card-panel">
                    <div class="co-section-title">Summary</div>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="row py-1 align-items-center">
                            <div class="col-7 text-muted fw-medium">Total Qty</div>
                            <div class="col-5 text-end fw-bold text-dark">{{ rtrim(rtrim(number_format($order->total_qty, 2), '0'), '.') }}</div>
                        </div>
                        <div class="row py-1 align-items-center">
                            <div class="col-7 text-muted fw-medium">Sub-Total</div>
                            <div class="col-5 text-end fw-bold text-dark">Rs {{ number_format($order->subtotal, 2) }}</div>
                        </div>
                        <div class="row py-1 align-items-center">
                            <div class="col-7 text-muted fw-medium">Order Discount</div>
                            <div class="col-5 text-end fw-bold text-dark">Rs {{ number_format($order->discount, 2) }}</div>
                        </div>
                        <hr class="my-2 border-secondary">
                        <div class="row py-1 align-items-center">
                            <div class="col-6 fw-bold fs-5 text-primary">Estimated Total</div>
                            <div class="col-6 text-end fw-bold fs-5 text-primary">Rs {{ number_format($order->total_amount, 2) }}</div>
                        </div>
                        @php
                            $orderMatCost = 0;
                            foreach ($order->items as $oItem) {
                                $mats = is_array($oItem->materials) ? $oItem->materials : [];
                                $itemMatCost = collect($mats)->sum(fn($m) => (float)($m['amount'] ?? 0));
                                $orderMatCost += ($itemMatCost * (float)$oItem->qty);
                            }
                            $orderProfit = $order->total_amount - $orderMatCost;
                            $orderProfitPct = $order->total_amount > 0 ? round(($orderProfit / $order->total_amount) * 100) : 0;
                        @endphp
                        @if ($orderMatCost > 0)
                            <div class="row py-1 align-items-center text-muted small">
                                <div class="col-6">Est. Profit</div>
                                <div class="col-6 text-end fw-bold {{ $orderProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rs {{ number_format($orderProfit, 2) }} ({{ $orderProfitPct }}%)
                                </div>
                            </div>
                        @endif
                        <div class="row py-1 mt-1 align-items-center">
                            <div class="col-7 text-success fw-semibold small">Advance Paid</div>
                            <div class="col-5 text-end text-success fw-bold small">Rs {{ number_format($order->advance_amount, 2) }}</div>
                        </div>
                        @if ((float)($order->delivery_payment_amount ?? 0) > 0)
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-success fw-semibold small">Delivery Payment</div>
                                <div class="col-5 text-end text-success fw-bold small">Rs {{ number_format($order->delivery_payment_amount, 2) }}</div>
                            </div>
                        @endif
                        <div class="row py-1 align-items-center {{ $order->remaining_balance > 0 ? 'bg-warning-subtle' : 'bg-success-subtle' }} rounded-2 mt-1">
                            <div class="col-7 text-dark fw-bold">{{ $order->remaining_balance > 0 ? 'Remaining Balance' : 'Balance Settled' }}</div>
                            <div class="col-5 text-end {{ $order->remaining_balance > 0 ? 'text-danger' : 'text-success' }} fw-bold">Rs {{ number_format($order->remaining_balance, 2) }}</div>
                        </div>
                        @if (is_array($order->payment_methods) && count($order->payment_methods) > 0)
                            <div class="mt-2 pt-2 border-top">
                                <div class="small fw-bold text-muted mb-1">
                                    <i class="bi bi-wallet2 text-success me-1"></i> Advance Payment Methods
                                </div>
                                @foreach ($order->payment_methods as $pm)
                                    <div class="d-flex justify-content-between small text-dark py-0">
                                        <span>{{ $pm['account_title'] ?? 'Account' }}</span>
                                        <span class="fw-semibold">Rs {{ number_format((float) ($pm['amount'] ?? 0), 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if (is_array($order->delivery_payment_methods) && count($order->delivery_payment_methods) > 0)
                            <div class="mt-2 pt-2 border-top">
                                <div class="small fw-bold text-muted mb-1">
                                    <i class="bi bi-truck text-success me-1"></i> Delivery Payment Methods
                                </div>
                                @foreach ($order->delivery_payment_methods as $dpm)
                                    <div class="d-flex justify-content-between small text-dark py-0">
                                        <span>{{ $dpm['account_title'] ?? 'Account' }}</span>
                                        <span class="fw-semibold">Rs {{ number_format((float) ($dpm['amount'] ?? 0), 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($order->status === 'delivered')
            <div class="co-card-panel mt-3 border-success-subtle bg-light">
                <div class="co-section-title text-success" style="border-left-color: #10b981 !important;">
                    <i class="bi bi-check-circle-fill me-1"></i> Delivery &amp; Handover Information
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Delivered On</label>
                        <div class="co-readonly-box text-success fw-bold">
                            {{ $order->delivered_at ? $order->delivered_at->format('d/m/Y h:i A') : ($order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'Delivered') }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Delivered By</label>
                        <div class="co-readonly-box">
                            {{ $order->deliverer->name ?? 'Staff' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Received By</label>
                        <div class="co-readonly-box text-dark fw-bold">
                            {{ $order->received_by ?: $order->customer_display_name }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Delivery Payment</label>
                        <div class="co-readonly-box text-success fw-bold">
                            Rs {{ number_format((float)($order->delivery_payment_amount ?? 0), 2) }}
                        </div>
                    </div>
                    @if ($order->sale_id)
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold mb-1">Sale Invoice</label>
                            <div class="co-readonly-box">
                                <a href="{{ route('sales.invoice', $order->sale_id) }}" target="_blank" class="fw-bold text-primary text-decoration-none">
                                    <i class="bi bi-receipt me-1"></i> Invoice #{{ $order->sale_id }}
                                </a>
                            </div>
                        </div>
                    @endif
                    @if ($order->delivery_notes)
                        <div class="col-12 mt-2">
                            <label class="form-label text-muted small fw-bold mb-1">Handover Notes</label>
                            <div class="p-2 border rounded-3 bg-white text-dark small">{{ $order->delivery_notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- BOTTOM ACTION BAR --}}
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center p-3 mt-3 border-top bg-light rounded-3">
            <form action="{{ route('customer_orders.destroy', $order->id) }}" method="POST" id="deleteSingleOrderForm" class="m-0">
                @csrf
                @method('DELETE')
                <button type="button" id="btnDeleteSingleOrder" class="btn btn-outline-danger fw-bold">
                    <i class="bi bi-trash me-1"></i> Delete Order
                </button>
            </form>

            <div class="d-flex flex-wrap gap-2">
                @if ($order->status !== 'delivered' && $order->status !== 'cancelled')
                    <a href="{{ route('sales.from.order', $order->id) }}" class="btn btn-success fw-bold text-white shadow-sm">
                        <i class="bi bi-truck me-1"></i> Deliver Order
                    </a>
                @elseif ($order->sale_id)
                    <a href="{{ route('sales.invoice', $order->sale_id) }}" target="_blank" class="btn btn-outline-success fw-bold">
                        <i class="bi bi-file-earmark-text me-1"></i> View Sale Invoice
                    </a>
                @endif
                <a href="{{ route('customer_orders.index') }}" class="btn co-btn-secondary">
                    <i class="bi bi-list-ul me-1"></i> All Orders
                </a>
                <a href="{{ route('customer_orders.print', $order->id) }}" target="_blank" class="btn co-btn-secondary">
                    <i class="bi bi-printer me-1"></i> Print Slip
                </a>
                <a href="{{ route('customer_orders.edit', $order->id) }}" class="btn co-btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit Order
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#coShowStatusSelect').on('change', function() {
            var selected = $(this).val();
            var prev = $(this).data('prev-status');
            var prepareUrl = $(this).data('prepare-url');
            var deliverUrl = $(this).data('deliver-url');

            if (selected === 'in_progress' && prepareUrl) {
                $(this).val(prev);
                window.location.href = prepareUrl;
                return;
            }

            if (selected === 'delivered' && deliverUrl) {
                $(this).val(prev);
                window.location.href = deliverUrl;
                return;
            }
        });

        $('#btnDeleteSingleOrder').on('click', function() {
            Swal.fire({
                title: 'Delete Order #{{ $order->order_no }}?',
                text: 'This will permanently delete this customer order record.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $('#deleteSingleOrderForm').submit();
                }
            });
        });
    });
</script>
@endsection
