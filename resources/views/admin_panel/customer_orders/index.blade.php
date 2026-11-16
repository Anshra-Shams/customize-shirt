@extends('admin_panel.layout.app')

@section('content')
<style>
    .co-stat-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.85rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 1400px) {
        .co-stat-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 992px) {
        .co-stat-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .co-stat-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .co-stat-grid { grid-template-columns: 1fr; }
    }
    .co-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }
    .co-stat-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.2rem;
    }
    .co-stat-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .co-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }
    .co-filter-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }
    .co-filter-box label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 0.3rem;
        display: block;
    }
    .co-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .co-table {
        border: 1px solid #dee2e6 !important;
        border-collapse: collapse;
    }
    .co-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid #dee2e6 !important;
        padding: 0.75rem;
        white-space: nowrap;
    }
    .co-table tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        border: 1px solid #dee2e6 !important;
        font-size: 0.85rem;
        color: #334155;
    }
    .co-table tbody tr:hover {
        background: #f8fafc;
    }
    .co-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 0.32rem 0.68rem;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .co-badge-pending { background: #fef3c7; color: #92400e; }
    .co-badge-in_progress { background: #dbeafe; color: #1e40af; }
    .co-badge-ready { background: #e0e7ff; color: #4338ca; }
    .co-badge-delivered { background: #d1fae5; color: #065f46; }
    .co-badge-cancelled { background: #fee2e2; color: #991b1b; }
    .co-status-select {
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.25rem 0.45rem;
        color: #0f172a;
        background: #ffffff;
    }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid py-3">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 12px;">
                <div>
                    <h4 class="font-weight-bold text-dark mb-1">
                        <i class="fas fa-clipboard-list text-primary mr-2"></i>Customer Orders
                    </h4>
                    <p class="text-muted mb-0 small">Manage custom and catalog orders taken from customers with delivery dates.</p>
                </div>
                <div class="d-flex" style="gap: 8px;">
                    <a href="{{ route('customer_orders.create') }}" class="btn btn-primary font-weight-bold shadow-sm px-4">
                        <i class="fas fa-plus mr-1"></i> Take New Order
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="co-stat-grid">
                <div class="co-stat-card">
                    <div>
                        <div class="co-stat-label">Total Orders</div>
                        <div class="co-stat-value">{{ number_format($stats['total']) }}</div>
                    </div>
                    <div class="co-stat-icon" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
                <div class="co-stat-card">
                    <div>
                        <div class="co-stat-label">Total Amount</div>
                        <div class="co-stat-value text-primary" style="font-size: 1.25rem;">Rs {{ number_format($stats['total_amount'], 2) }}</div>
                    </div>
                    <div class="co-stat-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="co-stat-card">
                    <div>
                        <div class="co-stat-label">Est. Profit</div>
                        <div class="co-stat-value {{ $stats['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 1.25rem;">
                            Rs {{ number_format($stats['total_profit'], 2) }}
                        </div>
                    </div>
                    <div class="co-stat-icon" style="background: #dcfce7; color: #16a34a;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="co-stat-card">
                    <div>
                        <div class="co-stat-label">Pending</div>
                        <div class="co-stat-value">{{ number_format($stats['pending']) }}</div>
                    </div>
                    <div class="co-stat-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="co-stat-card">
                    <div>
                        <div class="co-stat-label">In Preparing / Ready</div>
                        <div class="co-stat-value">{{ number_format($stats['in_progress'] + $stats['ready']) }}</div>
                    </div>
                    <div class="co-stat-icon" style="background: #dbeafe; color: #2563eb;">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="co-stat-card">
                    <div>
                        <div class="co-stat-label">Delivered</div>
                        <div class="co-stat-value">{{ number_format($stats['delivered']) }}</div>
                    </div>
                    <div class="co-stat-icon" style="background: #d1fae5; color: #059669;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="co-stat-card">
                    <div>
                        <div class="co-stat-label">Delivery Due Today</div>
                        <div class="co-stat-value text-danger">{{ number_format($stats['due_today']) }}</div>
                    </div>
                    <div class="co-stat-icon" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="co-filter-box">
                <form method="GET" action="{{ route('customer_orders.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label>Search Order / Customer / Item</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                value="{{ request('search') }}" placeholder="Order #, name, phone, item...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label>Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="all">All Statuses</option>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label>Customer</label>
                            <select name="customer_id" class="form-control form-control-sm select2-filter">
                                <option value="">All Customers</option>
                                @foreach ($customers as $cust)
                                    <option value="{{ $cust->id }}" {{ (string) request('customer_id') === (string) $cust->id ? 'selected' : '' }}>
                                        {{ $cust->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label>Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control form-control-sm"
                                value="{{ request('delivery_date') }}">
                        </div>
                        <div class="col-md-3 mb-2 d-flex" style="gap: 6px;">
                            <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('customer_orders.index') }}" class="btn btn-sm btn-light border font-weight-bold px-3">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="co-table-card">
                <div class="table-responsive">
                    <table class="table table-bordered co-table mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Ordered Items</th>
                                <th class="text-center">Total Qty</th>
                                <th>Order Date</th>
                                <th>Delivery Date</th>
                                <th class="text-right">Est. Amount</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                @php
                                    $isOverdue = $order->delivery_date
                                        && $order->delivery_date->isPast()
                                        && !$order->delivery_date->isToday()
                                        && !in_array($order->status, ['delivered', 'cancelled']);
                                    $isDueToday = $order->delivery_date
                                        && $order->delivery_date->isToday()
                                        && !in_array($order->status, ['delivered', 'cancelled']);
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('customer_orders.show', $order->id) }}" class="font-weight-bold text-primary">
                                            {{ $order->order_no }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">
                                            {{ $order->customer_display_name }}
                                            @if (!$order->customer_id)
                                                <span class="badge badge-light border text-muted ml-1" style="font-size: 10px;">Walk-in</span>
                                            @endif
                                        </div>
                                        @if ($order->customer_phone)
                                            <div class="small text-muted"><i class="fas fa-phone-alt mr-1"></i>{{ $order->customer_phone }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $itemNamesList = $order->items->pluck('item_name')->filter()->values();
                                        @endphp
                                        @if ($itemNamesList->count() > 0)
                                            <div class="font-weight-bold text-dark">
                                                {{ $itemNamesList->take(4)->implode(', ') }}
                                                @if ($itemNamesList->count() > 4)
                                                    <span class="small text-primary font-weight-bold">+{{ $itemNamesList->count() - 4 }} more</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        {{ rtrim(rtrim(number_format($order->total_qty, 2), '0'), '.') }}
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $order->order_date ? $order->order_date->format('d M Y') : '-' }}
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($order->delivery_date)
                                            <span class="{{ $isOverdue ? 'text-danger font-weight-bold' : ($isDueToday ? 'text-warning font-weight-bold' : '') }}">
                                                {{ $order->delivery_date->format('d M Y') }}
                                            </span>
                                            @if ($isOverdue)
                                                <div><span class="badge badge-danger" style="font-size: 10px;">Overdue</span></div>
                                            @elseif ($isDueToday)
                                                <div><span class="badge badge-warning text-dark" style="font-size: 10px;">Due Today</span></div>
                                            @endif
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold text-dark text-nowrap">
                                        Rs {{ number_format($order->total_amount, 2) }}
                                        @if ($order->advance_amount > 0)
                                            <div class="small text-success font-weight-semibold">Adv: Rs {{ number_format($order->advance_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('customer_orders.status', $order->id) }}" method="POST" class="d-inline co-list-status-form">
                                            @csrf
                                            <select name="status" class="co-status-select co-order-status-select"
                                                data-prev-status="{{ $order->status }}"
                                                data-prepare-url="{{ route('customer_orders.prepare', $order->id) }}"
                                                data-deliver-url="{{ route('sales.from.order', $order->id) }}">
                                                @foreach ($statuses as $key => $label)
                                                    <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        @if ($order->status === 'pending')
                                            <a href="{{ route('customer_orders.prepare', $order->id) }}" class="btn btn-sm btn-outline-warning" title="Prepare Order">
                                                <i class="fas fa-tasks"></i>
                                            </a>
                                        @endif
                                        @if ($order->status !== 'delivered' && $order->status !== 'cancelled')
                                            <a href="{{ route('sales.from.order', $order->id) }}" class="btn btn-sm btn-outline-success" title="Deliver Order (Generate Sale)">
                                                <i class="fas fa-truck"></i>
                                            </a>
                                        @elseif ($order->sale_id)
                                            <a href="{{ route('sales.invoice', $order->sale_id) }}" target="_blank" class="btn btn-sm btn-outline-success" title="View Sale Invoice">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('customer_orders.show', $order->id) }}" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('customer_orders.print', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Slip">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="{{ route('customer_orders.edit', $order->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('customer_orders.destroy', $order->id) }}" method="POST" class="d-inline delete-order-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-order" data-order="{{ $order->order_no }}" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-clipboard-list fa-3x mb-3 d-block text-secondary" style="opacity: 0.4;"></i>
                                        <div class="font-weight-bold mb-1">No customer orders found</div>
                                        <div class="small mb-3">Click "Take New Order" to record your first customer order.</div>
                                        <a href="{{ route('customer_orders.create') }}" class="btn btn-sm btn-primary font-weight-bold px-3">
                                            <i class="fas fa-plus mr-1"></i> Take New Order
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 bg-light bg-opacity-25">
                    <div class="text-muted small">
                        Showing <strong>{{ $orders->firstItem() ?? 0 }}</strong> to <strong>{{ $orders->lastItem() ?? 0 }}</strong> of <strong>{{ $orders->total() }}</strong> orders (15 per page)
                    </div>
                    <div>
                        @if ($orders->hasPages())
                            {{ $orders->links() }}
                        @else
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>
                                <li class="page-item active"><span class="page-link">1</span></li>
                                <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-filter').select2({
                placeholder: 'All Customers',
                allowClear: true,
                width: '100%'
            });
        }

        $('.co-order-status-select').on('change', function() {
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

            $(this).closest('form').submit();
        });

        $('.btn-delete-order').on('click', function() {
            var $form = $(this).closest('form');
            var orderNo = $(this).data('order');

            Swal.fire({
                title: 'Delete Order #' + orderNo + '?',
                text: 'This will permanently delete this customer order record.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $form.submit();
                }
            });
        });
    });
</script>
@endsection
