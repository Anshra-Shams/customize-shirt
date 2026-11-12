<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/bootstrap-icons/css/bootstrap-icons.min.css') }}" rel="stylesheet">

<style>
    /* Clean Modern ERP Transaction Entry UI */
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

    .co-form-control,
    .co-form-select,
    .co-card-panel .form-control,
    .co-card-panel .form-select,
    .co-card-panel .select2-container--default .select2-selection--single {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 6px 11px !important;
        font-weight: 500 !important;
        color: #1e293b !important;
        background-color: #ffffff !important;
        transition: all 0.15s ease-in-out !important;
        height: 38px !important;
        font-size: 0.84rem !important;
    }

    .co-card-panel textarea.form-control {
        height: auto !important;
    }

    .co-card-panel .select2-container--default .select2-selection--single {
        padding: 0 8px !important;
        display: flex !important;
        align-items: center !important;
    }

    .co-card-panel .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 4px !important;
        color: #1e293b !important;
    }

    .co-card-panel .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    .co-card-panel .form-control:focus,
    .co-card-panel .form-select:focus,
    .co-card-panel .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14) !important;
        outline: none !important;
    }

    .co-input-readonly {
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #475569 !important;
        font-weight: 600 !important;
    }

    /* Flat Borderless Excel-Style Order Items Grid */
    .co-table-responsive {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        background-color: #ffffff;
    }

    .co-items-table {
        border-collapse: collapse !important;
        margin-bottom: 0 !important;
        width: 100% !important;
        min-width: 960px;
    }

    .co-items-table thead th {
        background-color: #f8fafc !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 11px !important;
        letter-spacing: 0.5px;
        padding: 10px 8px !important;
        border: 1px solid #cbd5e1 !important;
        border-bottom: 2px solid #94a3b8 !important;
        vertical-align: middle !important;
        text-align: center;
    }

    .co-items-table thead th.text-start {
        text-align: left !important;
        padding-left: 12px !important;
    }

    .co-items-table tbody td {
        border: 1px solid #cbd5e1 !important;
        padding: 0 !important;
        background-color: #ffffff;
        vertical-align: middle !important;
    }

    .co-items-table tbody .form-control,
    .co-items-table tbody .form-select {
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        height: 40px !important;
        margin: 0 !important;
        padding: 6px 10px !important;
        width: 100% !important;
        background-color: transparent !important;
        color: #1e293b !important;
        font-weight: 500 !important;
        font-size: 0.83rem !important;
    }

    .co-items-table tbody .form-control:focus {
        outline: none !important;
        background-color: #f8fafc !important;
        box-shadow: inset 0 0 0 2px #2563eb !important;
    }

    .co-items-table tbody .select2-container--default .select2-selection--single {
        height: 40px !important;
        padding: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background-color: transparent !important;
        display: flex !important;
        align-items: center !important;
    }

    .co-items-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        padding-left: 10px !important;
        padding-right: 22px !important;
        font-size: 0.83rem !important;
        color: #1e293b !important;
        font-weight: 500 !important;
        text-align: left !important;
    }

    .co-items-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 6px !important;
    }

    .co-items-table tbody .select2-container--default.select2-container--focus .select2-selection--single {
        background-color: #f8fafc !important;
        box-shadow: inset 0 0 0 2px #2563eb !important;
    }

    .co-items-table tbody .co-cell-readonly {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        font-weight: 700 !important;
        padding: 0 10px !important;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .co-unit-pill {
        border: 1px solid #93c5fd !important;
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        border-radius: 6px !important;
        font-size: 0.76rem !important;
        font-weight: 700 !important;
        height: 26px !important;
        width: 68px !important;
        padding: 2px 4px !important;
        text-align: center !important;
        margin: 0 auto !important;
        display: block !important;
    }

    .co-del-btn {
        width: 28px;
        height: 28px;
        border: 1.5px solid #fca5a5;
        background: #fff;
        color: #ef4444;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
    }

    .co-del-btn:hover {
        background: #fee2e2;
        border-color: #ef4444;
    }

    .co-items-table tfoot td {
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-top: 2px solid #94a3b8 !important;
        padding: 9px 12px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    .co-btn-primary {
        background-color: #2563eb !important;
        border: 1.5px solid #1d4ed8 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        padding: 8px 20px !important;
        font-size: 0.85rem !important;
    }

    .co-btn-primary:hover {
        background-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .co-btn-secondary {
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        padding: 8px 18px !important;
        font-size: 0.85rem !important;
    }

    .co-btn-secondary:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }

    /* Order Item Materials Button & Modal */
    .co-mat-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .co-mat-btn:hover,
    .co-mat-btn:focus {
        border-color: #334155;
        background: #f8fafc;
        color: #0f172a;
        outline: none;
    }
    .co-mat-btn svg {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
        color: #334155;
    }
    .co-row-custom-hint {
        font-size: 10px;
        font-weight: 600;
        color: #6366f1;
        margin-top: 2px;
        line-height: 1.1;
    }
    #orderMaterialsModal .modal-content {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.16);
        overflow: hidden;
    }
    #orderMaterialsModal .co-mat-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px 8px;
        background: #ffffff;
    }
    #orderMaterialsModal .co-mat-modal-title {
        font-size: 16.5px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    #orderMaterialsModal .co-mat-modal-close {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    #orderMaterialsModal .co-mat-modal-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .co-mat-scope-note {
        font-size: 11.5px;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 7px 11px;
        margin-bottom: 12px;
    }
    .co-mat-col-headers {
        display: grid;
        grid-template-columns: 1fr 135px 38px;
        gap: 10px;
        margin-bottom: 6px;
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }
    .co-mat-item-wrap {
        margin-bottom: 10px;
    }
    .co-mat-item-row {
        display: grid;
        grid-template-columns: 1fr 135px 38px;
        gap: 10px;
        align-items: center;
    }
    .co-mat-item-row input {
        height: 38px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        font-size: 13.5px !important;
        color: #0f172a !important;
        background: #ffffff !important;
        width: 100%;
    }
    .co-mat-item-row input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        outline: none !important;
    }
    .co-mat-custom-caption {
        font-size: 10.5px;
        font-weight: 600;
        color: #6366f1;
        margin-top: 3px;
        padding-left: 4px;
        letter-spacing: 0.2px;
    }
    .co-mat-row-del {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        border: 1px solid #fecaca;
        background: #fff5f5;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .co-mat-row-del:hover {
        background: #fee2e2;
        border-color: #f87171;
    }
    .co-mat-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 35px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 2px;
    }
    .co-mat-add-btn:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }
    .co-mat-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 16px;
    }
    .co-mat-summary-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .co-mat-summary-box .label {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 3px;
    }
    .co-mat-summary-box .value {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }
    #orderMaterialsModal .co-mat-modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 12px 22px 16px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger rounded-3 mb-3">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $isEdit = isset($order) && $order->exists;
    $isPrepare = !empty($isPrepareConfirmation);
    $defaultMode = old('customer_mode', ($isEdit && !$order->customer_id && $order->walkin_name) ? 'walkin' : 'existing');
    $selectedCustomerId = old('customer_id', $isEdit ? $order->customer_id : '');
    $orderNoValue = old('order_no', $isEdit ? $order->order_no : ($nextOrderNo ?? ''));
    $orderDateValue = old('order_date', $isEdit && $order->order_date ? $order->order_date->format('Y-m-d') : date('Y-m-d'));
    $deliveryDateValue = old('delivery_date', $isEdit && $order->delivery_date ? $order->delivery_date->format('Y-m-d') : '');
    $statusValue = old('status', $isPrepare ? 'in_progress' : ($isEdit ? $order->status : 'pending'));
    $descValue = old('description', $isEdit ? $order->description : '');
    $discountValue = old('discount', $isEdit ? $order->discount : 0);
    $advanceValue = old('advance_amount', $isEdit ? $order->advance_amount : 0);

    $paymentAccounts = $accounts ?? collect();
    $oldPayAccounts = old('payment_account_id');
    $oldPayAmounts = old('payment_amount');

    if (is_array($oldPayAmounts)) {
        $paymentRows = [];
        foreach ($oldPayAmounts as $pIdx => $pAmt) {
            $paymentRows[] = [
                'account_id' => $oldPayAccounts[$pIdx] ?? '',
                'amount' => $pAmt,
            ];
        }
        if (empty($paymentRows)) {
            $paymentRows = [['account_id' => '', 'amount' => 0]];
        }
    } elseif ($isEdit && is_array($order->payment_methods) && count($order->payment_methods) > 0) {
        $paymentRows = collect($order->payment_methods)->map(function ($pm) {
            return [
                'account_id' => $pm['account_id'] ?? '',
                'amount' => $pm['amount'] ?? 0,
            ];
        })->toArray();
    } else {
        $paymentRows = [
            ['account_id' => '', 'amount' => $advanceValue > 0 ? $advanceValue : 0],
        ];
    }

    $preparedProducts = $products->map(function ($prod) {
        $defaultPrice = $prod->sale_price_per_piece > 0
            ? $prod->sale_price_per_piece
            : ((float) ($prod->price ?? 0));
        $unitName = optional($prod->unit)->name ?: 'Pcs';
        $defaultMaterials = [];

        if (!empty($prod->color)) {
            $parsedVariants = json_decode($prod->color, true);
            if (is_array($parsedVariants)) {
                foreach ($parsedVariants as $pv) {
                    if (is_array($pv) && !empty($pv['materials']) && is_array($pv['materials'])) {
                        foreach ($pv['materials'] as $m) {
                            if (!is_array($m)) {
                                continue;
                            }
                            $mName = trim((string) ($m['name'] ?? ''));
                            $mAmt = is_numeric($m['amount'] ?? null) ? round((float) $m['amount'], 2) : 0;
                            if ($mName === '' && $mAmt == 0) {
                                continue;
                            }
                            $defaultMaterials[] = [
                                'name' => $mName,
                                'amount' => $mAmt,
                                'is_custom' => false,
                            ];
                        }
                        break;
                    }
                }
            }
        }

        return [
            'id' => $prod->id,
            'item_name' => $prod->item_name,
            'item_code' => $prod->item_code,
            'default_price' => $defaultPrice,
            'unit_name' => $unitName,
            'default_materials_json' => json_encode($defaultMaterials),
        ];
    });

    $flaskSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6"/><path d="M10 3v5.2a2 2 0 0 1-.4 1.2L4.7 16.5A2.5 2.5 0 0 0 6.7 20.5h10.6a2.5 2.5 0 0 0 2-4l-4.9-7.1a2 2 0 0 1-.4-1.2V3"/><path d="M6.5 15h11"/></svg>';

    $oldItemNames = old('item_name');
    if (is_array($oldItemNames)) {
        $rows = [];
        foreach ($oldItemNames as $idx => $nm) {
            $rawMat = old("item_materials.$idx", '[]');
            $decodedMat = is_string($rawMat) ? (json_decode($rawMat, true) ?: []) : (is_array($rawMat) ? $rawMat : []);
            $rows[] = [
                'product_id' => old("product_id.$idx"),
                'item_name' => $nm,
                'description' => old("item_description.$idx"),
                'materials' => $decodedMat,
                'qty' => old("qty.$idx", 1),
                'unit' => old("unit.$idx", 'Pcs'),
                'price' => old("price.$idx", 0),
            ];
        }
    } elseif ($isEdit && $order->items->count() > 0) {
        $rows = $order->items->map(function ($it) {
            return [
                'product_id' => $it->product_id,
                'item_name' => $it->item_name,
                'description' => $it->description,
                'materials' => is_array($it->materials) ? $it->materials : [],
                'qty' => $it->qty,
                'unit' => $it->unit ?: 'Pcs',
                'price' => $it->price,
            ];
        })->toArray();
    } else {
        $rows = [
            ['product_id' => '', 'item_name' => '', 'description' => '', 'materials' => [], 'qty' => 1, 'unit' => 'Pcs', 'price' => 0],
        ];
    }
@endphp

{{-- TOP HEADER & CUSTOMER / ORDER DETAILS CARD --}}
<div class="co-card-panel mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 border-bottom pb-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('customer_orders.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <h5 class="text-dark fw-bold mb-0 ms-2">
                @if ($isPrepare)
                    Confirm Order Preparation #{{ $order->order_no }}
                @elseif ($isEdit)
                    Edit Customer Order #{{ $order->order_no }}
                @else
                    Take Customer Order
                @endif
            </h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if ($isPrepare)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-bold">
                    <i class="bi bi-clipboard2-check me-1"></i> Preparing Confirmation
                </span>
            @endif
            <span class="badge bg-light text-secondary border px-3 py-2 fw-semibold">
                Date: {{ date('d/m/Y') }}
            </span>
        </div>
    </div>

    @if ($isPrepare)
        <div class="alert alert-primary py-2 px-3 mb-3 small d-flex align-items-center gap-2">
            <i class="bi bi-check2-square fs-6"></i>
            <span>Please verify the customer order items, materials, and delivery date below, then click <strong>Confirm In Preparing</strong>.</span>
        </div>
    @endif

    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label fw-bold mb-1 text-muted small">Order No.</label>
            <input type="text" class="form-control co-input-readonly fw-bold" name="order_no" value="{{ $orderNoValue }}" placeholder="ORD-0001">
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold mb-1 text-muted small">
                <span id="customerFieldLabel">{{ $defaultMode === 'walkin' ? 'Walk-in Customer Name *' : 'Select Customer *' }}</span>
            </label>
            <input type="hidden" name="customer_mode" id="customerModeInput" value="{{ $defaultMode }}">
            <div class="d-flex align-items-center gap-1">
                <div class="flex-grow-1" id="existingCustomerBox" style="{{ $defaultMode === 'existing' ? '' : 'display:none;' }}">
                    <select name="customer_id" id="customerSelect" class="form-select select2-customer" style="width:100%;">
                        <option value="">Select Customer</option>
                        @foreach ($customers as $cust)
                            <option value="{{ $cust->id }}"
                                data-phone="{{ $cust->mobile ?? '' }}"
                                data-address="{{ $cust->address ?? '' }}"
                                {{ (string) $selectedCustomerId === (string) $cust->id ? 'selected' : '' }}>
                                {{ $cust->customer_name }}@if ($cust->mobile) ({{ $cust->mobile }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-grow-1" id="walkinCustomerBox" style="{{ $defaultMode === 'walkin' ? '' : 'display:none;' }}">
                    <input type="text" name="walkin_name" id="walkinNameInput" class="form-control"
                        placeholder="Enter walk-in customer name"
                        value="{{ old('walkin_name', $isEdit ? $order->walkin_name : '') }}">
                </div>
                <button type="button" id="toggleCustomerModeBtn" class="btn btn-primary shadow-sm"
                    style="height: 38px; width: 38px; display: inline-flex; align-items: center; justify-content: center;"
                    title="{{ $defaultMode === 'walkin' ? 'Switch to Registered Customer' : 'Switch to Walk-in / New Customer' }}">
                    <i class="bi {{ $defaultMode === 'walkin' ? 'bi-person-lines-fill' : 'bi-plus-lg' }}" id="toggleCustomerIcon"></i>
                </button>
            </div>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold mb-1 text-muted small">Order Date <span class="text-danger">*</span></label>
            <input type="date" name="order_date" class="form-control" value="{{ $orderDateValue }}" required>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold mb-1 text-muted small">Delivery Date</label>
            <input type="date" name="delivery_date" class="form-control" value="{{ $deliveryDateValue }}">
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold mb-1 text-muted small">Order Status</label>
            <select name="status" class="form-select">
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" {{ $statusValue === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Customer Contact & Address Strip --}}
    <div class="mt-3 p-2 border rounded-3 bg-light">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-telephone-fill"></i></span>
                    <input type="text" name="customer_phone" id="customerPhoneInput" class="form-control border-start-0"
                        placeholder="Customer Phone / Mobile" style="height: 32px !important;"
                        value="{{ old('customer_phone', $isEdit ? $order->customer_phone : '') }}">
                </div>
            </div>
            <div class="col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-geo-alt-fill"></i></span>
                    <input type="text" name="customer_address" id="customerAddressInput" class="form-control border-start-0"
                        placeholder="Customer / Delivery Address (Optional)" style="height: 32px !important;"
                        value="{{ old('customer_address', $isEdit ? $order->customer_address : '') }}">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ORDER ITEMS (FULL WIDTH) --}}
<div class="co-card-panel mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="co-section-title mb-0">Order Items</div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm fw-bold" id="addOrderItemRow">
                <i class="bi bi-plus-lg"></i> Add Row
            </button>
        </div>
    </div>

    <div class="co-table-responsive">
        <table class="co-items-table" id="orderItemsTable">
            <thead>
                <tr>
                    <th class="text-start" style="width: 21%;">Catalog Product (Optional)</th>
                    <th class="text-start" style="width: 18%;">Order Item / What Order Is For *</th>
                    <th class="text-start" style="width: 17%;">Item Description / Specs</th>
                    <th class="text-center" style="width: 11%;">Materials</th>
                    <th style="width: 7%;">Unit</th>
                    <th style="width: 7%;">Qty *</th>
                    <th style="width: 9%;">Unit Price</th>
                    <th style="width: 10%;">Amount</th>
                    <th style="width: 45px;">Action</th>
                </tr>
            </thead>
            <tbody id="orderItemsBody">
                @foreach ($rows as $row)
                    @php
                        $rowMaterials = is_array($row['materials'] ?? null) ? $row['materials'] : [];
                        $rowMatCount = count($rowMaterials);
                        $rowCustomCount = collect($rowMaterials)->where('is_custom', true)->count();
                    @endphp
                    <tr class="order-item-row">
                        <td>
                            <select name="product_id[]" class="form-select product-picker" style="width:100%;">
                                <option value="">Search Product (or leave Custom)</option>
                                @foreach ($preparedProducts as $prod)
                                    <option value="{{ $prod['id'] }}"
                                        data-name="{{ $prod['item_name'] }}"
                                        data-price="{{ $prod['default_price'] }}"
                                        data-unit="{{ $prod['unit_name'] }}"
                                        data-materials="{{ $prod['default_materials_json'] }}"
                                        {{ (string) ($row['product_id'] ?? '') === (string) $prod['id'] ? 'selected' : '' }}>
                                        {{ $prod['item_name'] }}@if ($prod['item_code']) ({{ $prod['item_code'] }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="item_name[]" class="form-control item-name-input"
                                placeholder="e.g. Custom Suit / Item Name"
                                value="{{ $row['item_name'] ?? '' }}" required>
                        </td>
                        <td>
                            <input type="text" name="item_description[]" class="form-control"
                                placeholder="Color, size, design notes..."
                                value="{{ $row['description'] ?? '' }}">
                        </td>
                        <td class="text-center">
                            <input type="hidden" name="item_materials[]" class="item-materials-input" value="{{ json_encode($rowMaterials) }}">
                            <button type="button" class="co-mat-btn open-order-materials-btn">
                                {!! $flaskSvg !!}
                                <span>Materials (<span class="co-mat-count">{{ $rowMatCount }}</span>)</span>
                            </button>
                            <div class="co-row-custom-hint {{ $rowCustomCount > 0 ? '' : 'd-none' }}">
                                {{ $rowCustomCount > 0 ? '+' . $rowCustomCount . ' customize' : '' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="text" name="unit[]" list="unitOptions" class="co-unit-pill item-unit"
                                value="{{ $row['unit'] ?? 'Pcs' }}" placeholder="Pcs">
                        </td>
                        <td>
                            <input type="number" name="qty[]" class="form-control item-qty text-center"
                                step="0.01" min="0.01" value="{{ $row['qty'] ?? 1 }}" required>
                        </td>
                        <td>
                            <input type="number" name="price[]" class="form-control item-price text-end"
                                step="0.01" min="0" value="{{ $row['price'] ?? 0 }}">
                        </td>
                        <td>
                            <div class="co-cell-readonly">
                                <span class="row-total-text">0.00</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="co-del-btn remove-item-row" title="Remove Row">
                                <i class="bi bi-x-lg" style="font-size: 12px;"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-end text-muted">Total Amount:</td>
                    <td class="text-end fs-6 text-dark"><span id="footerTotalAmount">0.00</span></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<datalist id="unitOptions">
    <option value="Pcs">
    <option value="Box">
    <option value="Pack">
    <option value="Dozen">
    <option value="Set">
    <option value="Kg">
    <option value="Meter">
</datalist>

{{-- BOTTOM SECTION: DESCRIPTION / REMARKS + SUMMARY --}}
<div class="row g-3 mt-1">
    <div class="col-lg-7">
        <div class="co-card-panel">
            <div class="co-section-title">Order Description &amp; Instructions</div>
            <div class="mb-3">
                <textarea name="description" rows="4" class="form-control"
                    placeholder="Write overall order description, customer special instructions, packaging or delivery notes...">{{ $descValue }}</textarea>
            </div>
            <div class="p-2 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="small text-primary fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Safe Order Mode: Stock &amp; POS are not affected. Advance payments post to selected Account Ledger.
                </span>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="d-flex flex-column gap-3">
            <div class="co-card-panel">
                <div class="co-section-title">Summary</div>
                <div class="p-3 bg-light rounded-3 border">
                    <div class="row py-1 align-items-center">
                        <div class="col-7 text-muted fw-medium">Total Qty</div>
                        <div class="col-5 text-end"><span id="summaryTotalQty" class="fw-bold text-dark">0</span></div>
                    </div>
                    <div class="row py-1 align-items-center">
                        <div class="col-7 text-muted fw-medium">Sub-Total</div>
                        <div class="col-5 text-end fw-bold text-dark"><span id="summarySubtotal">0.00</span></div>
                    </div>
                    <div class="row py-1 align-items-center">
                        <div class="col-7 text-muted fw-medium">Order Discount</div>
                        <div class="col-5 text-end d-flex gap-1 justify-content-end">
                            <input type="number" class="form-control text-end" id="orderDiscountPct"
                                placeholder="%" style="width: 68px; height: 32px !important;" step="0.01" min="0">
                            <input type="number" name="discount" class="form-control text-end" id="orderDiscountInput"
                                value="{{ $discountValue }}" step="0.01" min="0" style="width: 95px; height: 32px !important;">
                        </div>
                    </div>
                    <hr class="my-2 border-secondary">
                    <div class="row py-1 align-items-center">
                        <div class="col-6 fw-bold fs-5 text-primary">Estimated Total</div>
                        <div class="col-6 text-end fw-bold fs-5 text-primary">Rs <span id="summaryGrandTotal">0.00</span></div>
                    </div>
                    <div class="row py-1 mt-1 align-items-center">
                        <div class="col-7 text-success fw-semibold">Advance Paid</div>
                        <div class="col-5 text-end d-flex justify-content-end">
                            <input type="number" name="advance_amount" id="summaryAdvanceInput"
                                class="form-control text-end fw-bold text-success"
                                value="{{ number_format((float) $advanceValue, 2, '.', '') }}"
                                step="0.01" min="0" style="width: 120px; height: 32px !important;">
                        </div>
                    </div>
                    <div class="row py-1 align-items-center">
                        <div class="col-6 fw-bold text-dark">Remaining Balance</div>
                        <div class="col-6 text-end fw-bold text-danger">Rs <span id="summaryRemainingBalance">0.00</span></div>
                    </div>
                </div>
            </div>

            {{-- Payment Methods Card --}}
            <div class="co-card-panel">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="fw-bold text-dark" style="font-size: 0.9rem;">
                        <i class="fas fa-wallet text-success me-1"></i> Payment Methods
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 fw-bold" id="btnAddPaymentRow" style="font-size: 0.78rem; border-radius: 7px;">
                        <i class="bi bi-plus-lg me-1"></i>Add Account
                    </button>
                </div>

                <div id="coPaymentRows">
                    @php
                        $hasPreferredAccount = $paymentAccounts->contains(fn($a) => str_contains(strtolower($a->title), 'cash') || str_contains(strtolower($a->title), 'easypaisa'));
                    @endphp
                    @foreach ($paymentRows as $pRow)
                        <div class="d-flex gap-2 align-items-center mb-2 co-payment-row">
                            <select class="form-select co-pay-account fw-semibold" name="payment_account_id[]" style="font-size: 0.85rem;">
                                <option value="">Select Account</option>
                                @foreach ($paymentAccounts as $acc)
                                    @php
                                        $isPreferred = str_contains(strtolower($acc->title), 'cash') || str_contains(strtolower($acc->title), 'easypaisa');
                                        $autoDefault = ($pRow['account_id'] ?? '') === '' && $loop->parent->first && ($hasPreferredAccount ? $isPreferred : $loop->first);
                                        $isSelected = (string) ($pRow['account_id'] ?? '') === (string) $acc->id || $autoDefault;
                                    @endphp
                                    <option value="{{ $acc->id }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $acc->title }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" step="0.01" min="0" class="form-control text-end co-pay-amount fw-bold"
                                name="payment_amount[]"
                                value="{{ (float) ($pRow['amount'] ?? 0) > 0 ? number_format((float) $pRow['amount'], 2, '.', '') : '' }}"
                                placeholder="0.00" style="width: 130px;">
                            <button type="button" class="co-del-btn remove-payment-row" title="Remove Account" style="flex-shrink: 0;">
                                <i class="bi bi-x-lg" style="font-size: 11px;"></i>
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center pt-1 small text-muted">
                    <span>Total Paid via Accounts</span>
                    <strong class="text-success">Rs <span id="paymentMethodsTotalDisplay">0.00</span></strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- BOTTOM ACTION BAR --}}
<div class="d-flex flex-wrap gap-2 justify-content-end p-3 mt-3 border-top bg-light rounded-3">
    <a href="{{ route('customer_orders.index') }}" class="btn co-btn-secondary">
        <i class="bi bi-x-circle me-1"></i> Cancel
    </a>
    <button type="button" class="btn co-btn-secondary" onclick="window.location.reload()">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
    </button>
    <button type="submit" class="btn co-btn-primary">
        <i class="bi bi-check-circle me-1"></i>
        @if ($isPrepare)
            Confirm In Preparing
        @elseif ($isEdit)
            Update Order
        @else
            Save Customer Order
        @endif
    </button>
</div>

{{-- Hidden Template for New Rows --}}
<template id="orderItemRowTemplate">
    <tr class="order-item-row">
        <td>
            <select name="product_id[]" class="form-select product-picker" style="width:100%;">
                <option value="">Search Product (or leave Custom)</option>
                @foreach ($preparedProducts as $prod)
                    <option value="{{ $prod['id'] }}"
                        data-name="{{ $prod['item_name'] }}"
                        data-price="{{ $prod['default_price'] }}"
                        data-unit="{{ $prod['unit_name'] }}"
                        data-materials="{{ $prod['default_materials_json'] }}">
                        {{ $prod['item_name'] }}@if ($prod['item_code']) ({{ $prod['item_code'] }})@endif
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="item_name[]" class="form-control item-name-input"
                placeholder="e.g. Custom Suit / Item Name" required>
        </td>
        <td>
            <input type="text" name="item_description[]" class="form-control"
                placeholder="Color, size, design notes...">
        </td>
        <td class="text-center">
            <input type="hidden" name="item_materials[]" class="item-materials-input" value="[]">
            <button type="button" class="co-mat-btn open-order-materials-btn">
                {!! $flaskSvg !!}
                <span>Materials (<span class="co-mat-count">0</span>)</span>
            </button>
            <div class="co-row-custom-hint d-none"></div>
        </td>
        <td class="text-center">
            <input type="text" name="unit[]" list="unitOptions" class="co-unit-pill item-unit"
                value="Pcs" placeholder="Pcs">
        </td>
        <td>
            <input type="number" name="qty[]" class="form-control item-qty text-center"
                step="0.01" min="0.01" value="1" required>
        </td>
        <td>
            <input type="number" name="price[]" class="form-control item-price text-end"
                step="0.01" min="0" value="0">
        </td>
        <td>
            <div class="co-cell-readonly">
                <span class="row-total-text">0.00</span>
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="co-del-btn remove-item-row" title="Remove Row">
                <i class="bi bi-x-lg" style="font-size: 12px;"></i>
            </button>
        </td>
    </tr>
</template>

{{-- Hidden Template for New Payment Account Rows --}}
<template id="coPaymentRowTemplate">
    <div class="d-flex gap-2 align-items-center mb-2 co-payment-row">
        <select class="form-select co-pay-account fw-semibold" name="payment_account_id[]" style="font-size: 0.85rem;">
            <option value="">Select Account</option>
            @foreach ($paymentAccounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->title }}</option>
            @endforeach
        </select>
        <input type="number" step="0.01" min="0" class="form-control text-end co-pay-amount fw-bold"
            name="payment_amount[]" placeholder="0.00" style="width: 130px;">
        <button type="button" class="co-del-btn remove-payment-row" title="Remove Account" style="flex-shrink: 0;">
            <i class="bi bi-x-lg" style="font-size: 11px;"></i>
        </button>
    </div>
</template>

{{-- Order Item Materials & Customizations Modal --}}
<div id="orderMaterialsModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 660px;">
        <div class="modal-content">
            <div class="co-mat-modal-header">
                <h5 class="co-mat-modal-title">Materials and services &middot; <span id="coMatModalItemLabel">Order Item</span></h5>
                <button type="button" class="co-mat-modal-close" id="coMatModalCloseBtn" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body px-4 pt-2 pb-3 bg-white">
                <div class="co-mat-scope-note">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    Extra or edited materials added here are saved <strong>only for this customer order</strong> and will not modify the catalog product.
                </div>
                <div class="co-mat-col-headers">
                    <div>Name (material or service)</div>
                    <div>Amount (Rs)</div>
                    <div></div>
                </div>
                <div id="coMatModalRows"></div>
                <button type="button" class="co-mat-add-btn" id="coMatModalAddRowBtn">
                    <i class="bi bi-plus-lg" style="font-size: 12px;"></i> Add material
                </button>

                <div class="co-mat-summary-grid">
                    <div class="co-mat-summary-box">
                        <div class="label">Materials cost</div>
                        <div class="value" id="coMatModalTotalCost">Rs 0</div>
                    </div>
                    <div class="co-mat-summary-box">
                        <div class="label">Order unit price</div>
                        <div class="value" id="coMatModalUnitPrice">Rs 0</div>
                    </div>
                    <div class="co-mat-summary-box">
                        <div class="label">Customize items</div>
                        <div class="value text-primary" id="coMatModalCustomCount">0</div>
                    </div>
                </div>
            </div>
            <div class="co-mat-modal-footer">
                <button type="button" class="btn co-btn-secondary py-1 px-3" id="coMatModalCancelBtn">Cancel</button>
                <button type="button" class="btn co-btn-primary py-1 px-3" id="coMatModalSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    $(document).ready(function() {
        function initSelect2Elements($scope) {
            if (typeof $.fn.select2 === 'undefined') return;
            $scope.find('.select2-customer').select2({
                placeholder: 'Select Customer',
                allowClear: true,
                width: '100%'
            });
            $scope.find('.product-picker').select2({
                placeholder: 'Search Product (or leave Custom)',
                allowClear: true,
                width: '100%'
            });
        }

        initSelect2Elements($(document));

        // Toggle between Registered Customer & Walk-in Customer
        $('#toggleCustomerModeBtn').on('click', function() {
            var current = $('#customerModeInput').val();
            if (current === 'existing') {
                $('#customerModeInput').val('walkin');
                $('#existingCustomerBox').hide();
                $('#walkinCustomerBox').show();
                $('#customerFieldLabel').text('Walk-in Customer Name *');
                $('#toggleCustomerIcon').removeClass('bi-plus-lg').addClass('bi-person-lines-fill');
                $(this).attr('title', 'Switch to Registered Customer');
                $('#walkinNameInput').focus();
            } else {
                $('#customerModeInput').val('existing');
                $('#walkinCustomerBox').hide();
                $('#existingCustomerBox').show();
                $('#customerFieldLabel').text('Select Customer *');
                $('#toggleCustomerIcon').removeClass('bi-person-lines-fill').addClass('bi-plus-lg');
                $(this).attr('title', 'Switch to Walk-in / New Customer');
            }
        });

        // Auto-fill phone & address when selecting an existing customer
        $('#customerSelect').on('change', function() {
            var $opt = $(this).find('option:selected');
            var phone = $opt.data('phone') || '';
            var address = $opt.data('address') || '';
            if (phone) $('#customerPhoneInput').val(phone);
            if (address) $('#customerAddressInput').val(address);
        });

        // Recalculate row totals and order summary
        function recalculateOrderTotals() {
            var totalQty = 0;
            var subtotal = 0;

            $('#orderItemsBody .order-item-row').each(function() {
                var $row = $(this);
                var qty = parseFloat($row.find('.item-qty').val()) || 0;
                var price = parseFloat($row.find('.item-price').val()) || 0;
                var lineTotal = qty * price;

                $row.find('.row-total-text').text(lineTotal.toFixed(2));
                totalQty += qty;
                subtotal += lineTotal;
            });

            var discount = parseFloat($('#orderDiscountInput').val()) || 0;
            var grandTotal = Math.max(0, subtotal - discount);
            var advancePaid = parseFloat($('#summaryAdvanceInput').val()) || 0;
            var remainingBalance = Math.max(0, grandTotal - advancePaid);

            $('#footerTotalAmount').text(subtotal.toFixed(2));
            $('#summaryTotalQty').text(Number.isInteger(totalQty) ? totalQty : totalQty.toFixed(2));
            $('#summarySubtotal').text(subtotal.toFixed(2));
            $('#summaryGrandTotal').text(grandTotal.toFixed(2));
            $('#summaryRemainingBalance').text(remainingBalance.toFixed(2));
            $('#paymentMethodsTotalDisplay').text(advancePaid.toFixed(2));
        }

        function syncAdvanceFromPaymentRows() {
            var sum = 0;
            $('#coPaymentRows .co-pay-amount').each(function() {
                sum += parseFloat($(this).val()) || 0;
            });
            $('#summaryAdvanceInput').val(sum.toFixed(2));
            recalculateOrderTotals();
        }

        $('#btnAddPaymentRow').on('click', function() {
            var tpl = document.getElementById('coPaymentRowTemplate').content.cloneNode(true);
            $('#coPaymentRows').append(tpl);
        });

        $(document).on('input', '.co-pay-amount', function() {
            syncAdvanceFromPaymentRows();
        });

        $('#summaryAdvanceInput').on('input', function() {
            var val = parseFloat($(this).val()) || 0;
            var $rows = $('#coPaymentRows .co-pay-amount');
            if ($rows.length === 1) {
                $rows.first().val(val > 0 ? val.toFixed(2) : '');
            }
            recalculateOrderTotals();
        });

        $(document).on('click', '.remove-payment-row', function() {
            if ($('#coPaymentRows .co-payment-row').length <= 1) {
                $(this).closest('.co-payment-row').find('.co-pay-amount').val('');
                syncAdvanceFromPaymentRows();
                return;
            }
            $(this).closest('.co-payment-row').remove();
            syncAdvanceFromPaymentRows();
        });

        // Sync Discount % with Discount Amount
        $('#orderDiscountPct').on('input', function() {
            var pct = parseFloat($(this).val()) || 0;
            var subtotal = parseFloat($('#summarySubtotal').text()) || 0;
            var amt = (subtotal * pct) / 100;
            $('#orderDiscountInput').val(amt.toFixed(2));
            recalculateOrderTotals();
        });

        $('#orderDiscountInput').on('input', function() {
            var amt = parseFloat($(this).val()) || 0;
            var subtotal = parseFloat($('#summarySubtotal').text()) || 0;
            if (subtotal > 0) {
                $('#orderDiscountPct').val(((amt / subtotal) * 100).toFixed(2));
            } else {
                $('#orderDiscountPct').val('');
            }
            recalculateOrderTotals();
        });

        // Parse and update order row materials UI
        function parseOrderMaterials(raw) {
            if (Array.isArray(raw)) return raw;
            if (!raw || typeof raw !== 'string') return [];
            try {
                var parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function refreshOrderRowMaterialsUI($row) {
            var list = parseOrderMaterials($row.find('.item-materials-input').val());
            var customCount = list.filter(function(it) { return !!it.is_custom; }).length;
            $row.find('.co-mat-count').text(list.length);
            var $hint = $row.find('.co-row-custom-hint');
            if (customCount > 0) {
                $hint.text('+' + customCount + ' customize').removeClass('d-none');
            } else {
                $hint.text('').addClass('d-none');
            }
        }

        // Product selection auto-fills item name, unit, price, and default materials
        $(document).on('change', '.product-picker', function() {
            var $row = $(this).closest('.order-item-row');
            var $opt = $(this).find('option:selected');
            if ($(this).val()) {
                var name = $opt.data('name') || '';
                var price = parseFloat($opt.data('price')) || 0;
                var unit = $opt.data('unit') || 'Pcs';
                var rawMatAttr = $opt.attr('data-materials') || '[]';
                var defaultMats = parseOrderMaterials(rawMatAttr).map(function(m) {
                    return {
                        name: String(m.name || '').trim(),
                        amount: parseFloat(m.amount) || 0,
                        is_custom: false
                    };
                });

                $row.find('.item-name-input').val(name);
                $row.find('.item-unit').val(unit);
                $row.find('.item-price').val(price.toFixed(2));
                $row.find('.item-materials-input').val(JSON.stringify(defaultMats));
                refreshOrderRowMaterialsUI($row);
                recalculateOrderTotals();
            }
        });

        $(document).on('input', '.item-qty, .item-price', function() {
            recalculateOrderTotals();
        });

        // Add new item row
        $('#addOrderItemRow').on('click', function() {
            var tpl = document.getElementById('orderItemRowTemplate').content.cloneNode(true);
            var $newRow = $(tpl).find('tr');
            $('#orderItemsBody').append($newRow);
            initSelect2Elements($newRow);
            recalculateOrderTotals();
        });

        // Remove item row (keep at least 1 row)
        $(document).on('click', '.remove-item-row', function() {
            var rowCount = $('#orderItemsBody .order-item-row').length;
            if (rowCount <= 1) {
                var $row = $(this).closest('.order-item-row');
                $row.find('.product-picker').val('').trigger('change');
                $row.find('input[type="text"], input[type="number"]').val('');
                $row.find('.item-qty').val(1);
                $row.find('.item-unit').val('Pcs');
                $row.find('.item-price').val(0);
                $row.find('.item-materials-input').val('[]');
                refreshOrderRowMaterialsUI($row);
                recalculateOrderTotals();
                return;
            }
            $(this).closest('.order-item-row').remove();
            recalculateOrderTotals();
        });

        // ---- Order Item Materials Modal Logic (Order-scoped, never alters Catalog) ----
        var $activeOrderMaterialRow = null;

        function formatOrderRs(val) {
            var n = parseFloat(val) || 0;
            var clean = Math.abs(n - Math.round(n)) < 0.005
                ? Math.round(n).toLocaleString()
                : parseFloat(n.toFixed(2)).toLocaleString(undefined, { maximumFractionDigits: 2 });
            return 'Rs ' + clean;
        }

        function renderOrderMaterialModalRow(item) {
            var name = (item && item.name) ? item.name : '';
            var amount = (item && item.amount !== undefined && item.amount !== '') ? item.amount : '';
            var isCustom = item ? !!item.is_custom : true;

            var $wrap = $('<div class="co-mat-item-wrap"></div>').attr('data-is-custom', isCustom ? '1' : '0');
            var $row = $('<div class="co-mat-item-row"></div>');
            var $nameInp = $('<input type="text" class="co-mat-row-name" placeholder="e.g. Extra Lace, Pocket, Stitching" autocomplete="off">').val(name);
            var $amtInp = $('<input type="number" step="any" min="0" class="co-mat-row-amount" placeholder="0">').val(amount);
            var $delBtn = $('<button type="button" class="co-mat-row-del" title="Remove material"><i class="bi bi-trash"></i></button>');

            $row.append($nameInp, $amtInp, $delBtn);
            $wrap.append($row);

            var $caption = $('<div class="co-mat-custom-caption"></div>').text('customize');
            if (!isCustom) {
                $caption.hide();
            }
            $wrap.append($caption);

            $('#coMatModalRows').append($wrap);
            return $wrap;
        }

        function collectOrderModalMaterials() {
            var list = [];
            $('#coMatModalRows .co-mat-item-wrap').each(function() {
                var $w = $(this);
                var name = ($w.find('.co-mat-row-name').val() || '').trim();
                var rawAmt = $w.find('.co-mat-row-amount').val();
                var amount = rawAmt === '' ? 0 : (parseFloat(rawAmt) || 0);
                var isCustom = $w.attr('data-is-custom') === '1';
                if (name !== '' || amount > 0) {
                    list.push({ name: name, amount: amount, is_custom: isCustom });
                }
            });
            return list;
        }

        function updateOrderMaterialModalSummary() {
            if (!$activeOrderMaterialRow) return;
            var list = collectOrderModalMaterials();
            var totalCost = list.reduce(function(sum, it) { return sum + (parseFloat(it.amount) || 0); }, 0);
            var customCount = list.filter(function(it) { return !!it.is_custom; }).length;
            var unitPrice = parseFloat($activeOrderMaterialRow.find('.item-price').val()) || 0;

            $('#coMatModalTotalCost').text(formatOrderRs(totalCost));
            $('#coMatModalUnitPrice').text(formatOrderRs(unitPrice));
            $('#coMatModalCustomCount').text(customCount);
        }

        function showOrderMaterialsModal() {
            var modalEl = document.getElementById('orderMaterialsModal');
            if (!modalEl) return;
            if (typeof $('#orderMaterialsModal').modal === 'function') {
                $('#orderMaterialsModal').modal('show');
            } else if (window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }

        function hideOrderMaterialsModal() {
            var modalEl = document.getElementById('orderMaterialsModal');
            if (!modalEl) return;
            if (typeof $('#orderMaterialsModal').modal === 'function') {
                $('#orderMaterialsModal').modal('hide');
            } else if (window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
        }

        $(document).on('click', '.open-order-materials-btn', function() {
            $activeOrderMaterialRow = $(this).closest('.order-item-row');
            var itemLabel = ($activeOrderMaterialRow.find('.item-name-input').val() || '').trim() || 'Order Item';
            $('#coMatModalItemLabel').text(itemLabel);

            var list = parseOrderMaterials($activeOrderMaterialRow.find('.item-materials-input').val());
            $('#coMatModalRows').empty();

            if (list.length > 0) {
                list.forEach(function(item) {
                    renderOrderMaterialModalRow(item);
                });
            } else {
                renderOrderMaterialModalRow({ name: '', amount: '', is_custom: true });
            }

            updateOrderMaterialModalSummary();
            showOrderMaterialsModal();
        });

        $('#coMatModalAddRowBtn').on('click', function() {
            var $added = renderOrderMaterialModalRow({ name: '', amount: '', is_custom: true });
            $added.find('.co-mat-row-name').focus();
            updateOrderMaterialModalSummary();
        });

        $(document).on('input', '#coMatModalRows .co-mat-row-name, #coMatModalRows .co-mat-row-amount', function() {
            var $wrap = $(this).closest('.co-mat-item-wrap');
            $wrap.attr('data-is-custom', '1');
            $wrap.find('.co-mat-custom-caption').show();
            updateOrderMaterialModalSummary();
        });

        $(document).on('click', '#coMatModalRows .co-mat-row-del', function() {
            $(this).closest('.co-mat-item-wrap').remove();
            if ($('#coMatModalRows .co-mat-item-wrap').length === 0) {
                renderOrderMaterialModalRow({ name: '', amount: '', is_custom: true });
            }
            updateOrderMaterialModalSummary();
        });

        $('#coMatModalCloseBtn, #coMatModalCancelBtn').on('click', hideOrderMaterialsModal);

        $('#coMatModalSaveBtn').on('click', function() {
            if (!$activeOrderMaterialRow) return;
            var list = collectOrderModalMaterials();
            $activeOrderMaterialRow.find('.item-materials-input').val(JSON.stringify(list));
            refreshOrderRowMaterialsUI($activeOrderMaterialRow);
            hideOrderMaterialsModal();
        });

        recalculateOrderTotals();
    });
</script>
@endsection
