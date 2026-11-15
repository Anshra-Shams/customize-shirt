@extends('admin_panel.layout.app')

@section('content')
  <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        /* ================= RESPONSIVE PURCHASE UI (Modernized) ================= */
        body {
            background-color: #f4f6f9;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            overflow-x: hidden !important;
        }

        .main-container {
            border: 2px solid #475569 !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05) !important;
            background-color: #ffffff !important;
            padding: 18px !important;
            font-size: .85rem;
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }

        .table-responsive {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            overflow-x: hidden !important;
            overflow-y: visible !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03) !important;
            min-height: 150px;
            background-color: #ffffff;
        }

        .sales-table {
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
            width: 100% !important;
            table-layout: auto !important;
        }

        .sales-table thead th {
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

        .sales-table thead th.col-product {
            text-align: left !important;
            padding-left: 12px !important;
        }

        .sales-table tbody td {
            border: 1px solid #cbd5e1 !important;
            padding: 0 !important;
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        /* ⚡ FLAT BORDERLESS GRID INPUTS ⚡ */
        .sales-table tbody .form-control,
        .sales-table tbody .form-select {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            height: 38px !important;
            margin: 0 !important;
            padding: 6px 8px !important;
            width: 100% !important;
            background-color: transparent !important;
            text-align: center;
            color: #1e293b !important;
            font-weight: 500 !important;
            font-size: 0.82rem !important;
        }

        .sales-table tbody td.col-product .form-select {
            text-align: left !important;
            padding-left: 12px !important;
        }

        /* Calculations and Read-Only cells get a neat slate tone background */
        .sales-table tbody .input-readonly,
        .sales-table tbody input[readonly],
        .sales-table tbody select[disabled] {
            background-color: #f1f5f9 !important;
            cursor: not-allowed !important;
            color: #475569 !important;
            font-weight: 600 !important;
        }

        /* Subtle focus highlight inside cell */
        .sales-table tbody .form-control:focus,
        .sales-table tbody .form-select:focus {
            outline: none !important;
            background-color: #f8fafc !important;
            box-shadow: inset 0 0 0 2px #2563eb !important;
        }

        /* Select2 Specific flat borderless styling */
        .sales-table tbody .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
            display: flex;
            align-items: center;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px !important;
            padding-right: 20px !important;
            font-size: 0.82rem !important;
            color: #1e293b !important;
            font-weight: 500 !important;
            text-align: left !important;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 8px !important;
        }

        /* Select2 Focus state */
        .sales-table tbody .select2-container--default.select2-container--focus .select2-selection--single {
            background-color: #f8fafc !important;
            box-shadow: inset 0 0 0 2px #2563eb !important;
        }

        .sales-table tfoot td {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-top: 2px solid #94a3b8 !important;
            padding: 8px 10px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        /* Row hover */
        .sales-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        /* Column Widths & Layout */
        .col-product { width: 36%; }
        .col-unit { width: 10%; text-align: center; }
        .col-qty { width: 12%; text-align: center; }
        .col-price { width: 13%; text-align: right; }
        .col-disc { width: 8%; text-align: right; }
        .col-disc-amt { width: 9%; text-align: right; }
        .col-amount { width: 12%; text-align: right; }
        .col-action { width: 4%; text-align: center; }

        .btn {
            font-size: .82rem;
            padding: .35rem .8rem;
            border-radius: 5px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-success {
            background-color: #198754;
            border-color: #198754;
        }

        .section-title {
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
            border-left: 3px solid #0d6efd;
            padding-left: 8px;
        }

        /* Layout Helpers */
        .card-panel {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            height: 100%;
        }

        .select2-container .select2-selection--single {
            height: 36px !important;
            padding: 3px 12px;
            border-color: #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 5px !important;
        }
    </style>

    <div class="container-fluid py-2">
        <div class="main-container mx-auto">

            <form id="purchaseForm" action="{{ route('purchase.update', $purchase->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')

                {{-- TOP HEADER & INVOICE / VENDOR CARD --}}
                <div class="card-panel shadow-sm mb-3 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <h4 class="header-text text-dark fw-bold mb-0 ms-2">Edit Purchase #{{ $purchase->invoice_no }}</h4>
                        </div>
                        <div>
                            <span class="badge bg-light text-secondary border px-3 py-2 fs-6 fw-semibold" id="entryDate">
                                Date: {{ date('d/m/Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-xl-2 col-lg-2 col-md-4">
                            <label class="form-label fw-bold mb-1 text-muted small">System No.</label>
                            <input type="text" class="form-control input-readonly" name="invoice_no" value="{{ $purchase->invoice_no }}" readonly>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-4">
                            <label class="form-label fw-bold mb-1 text-muted small">Vendor Inv#</label>
                            <input type="text" class="form-control" name="purchase_order_no" value="{{ $purchase->purchase_order_no ?? '' }}" placeholder="Manual Ref">
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-4">
                            <label class="form-label fw-bold mb-1 text-muted small">Select Vendor</label>
                            <div class="d-flex align-items-center gap-1">
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <select class="form-select select2" id="vendorSelect" name="vendor_id">
                                        <option value="" disabled>Select Vendor</option>
                                        @foreach ($Vendor as $v)
                                            <option value="{{ $v->id }}" data-phone="{{ $v->phone }}" data-address="{{ $v->address }}"
                                                {{ $v->id == $purchase->vendor_id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-4">
                            <label class="form-label fw-bold mb-1 text-muted small text-truncate d-block" title="Raw Material Category">Raw Material Category</label>
                            <select class="form-select select2" id="categoryFilter" name="category_id">
                                <option value="">-- All Categories --</option>
                                @foreach ($rawMaterialCategories ?? [] as $rmCat)
                                    <option value="{{ $rmCat->id }}">{{ $rmCat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-4">
                            <label class="form-label fw-bold mb-1 text-muted small">Date</label>
                            <input type="date" name="purchase_date" class="form-control"
                                value="{{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-4">
                            <label class="form-label fw-bold mb-1 text-muted small">M.Bill / Remarks</label>
                            <input type="text" class="form-control" name="note" id="remarks" value="{{ $purchase->note }}" placeholder="Optional notes...">
                        </div>
                    </div>

                    <!-- TOP VENDOR DETAILS & HISTORY STRIP -->
                    <div id="vendorInfoCard" class="mt-3 p-2 border rounded-3 bg-light d-none">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-telephone-fill text-primary"></i>
                                <span class="fw-bold text-muted small">Mobile:</span>
                                <span class="fw-semibold text-dark small" id="vi_mobile">—</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                <span class="fw-bold text-muted small">Address:</span>
                                <span class="fw-semibold text-dark small" id="vi_address">—</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger-subtle text-danger border border-danger fs-6 px-3 py-1">
                                    Previous Balance: Rs. <span id="vi_prev_bal">0.00</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="warehouse_id" value="{{ $purchase->warehouse_id ?? ($Warehouse->first()->id ?? 1) }}">
                </div>

                {{-- PURCHASE ITEMS (FULL WIDTH) --}}
                <div class="card-panel shadow-sm p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="section-title mb-0">Purchase Raw Materials</div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm" onclick="addBlankRow()">
                                <i class="bi bi-plus-lg"></i> Add Row
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3 bg-white">
                        <table class="table table-bordered sales-table mb-0" id="purchaseTable">
                            <thead>
                                <tr>
                                    <th class="col-product">Raw Material</th>
                                    <th class="col-unit">Unit</th>
                                    <th class="col-qty">Qty</th>
                                    <th class="col-price">Purchase Price</th>
                                    <th class="col-disc">Disc %</th>
                                    <th class="col-disc-amt">Disc Amt</th>
                                    <th class="col-amount">Amount</th>
                                    <th class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseTableBody">
                                @foreach ($purchase->items as $item)
                                    @php
                                        $unitName = $item->unit ?: ($item->product->unit->name ?? 'Pcs');
                                        $sizeMode = $item->size_mode ?? 'by_pieces';
                                        $gross = (float)$item->line_total + (float)$item->item_discount;
                                        $dPct = $gross > 0 ? ((float)$item->item_discount / $gross) * 100 : 0;
                                    @endphp
                                    <tr data-sizemode="{{ $sizeMode }}" data-pieces_per_m2="{{ $item->pieces_per_m2 }}">
                                        <td>
                                            <select class="form-select product-select2" name="product_id[]">
                                                <option value="{{ $item->product_id }}" selected>
                                                    {{ $item->product->item_name }} ({{ $item->product->item_code }})
                                                </option>
                                            </select>
                                            {{-- Snapshots --}}
                                            <input type="hidden" name="size_mode[]" class="hidden-size-mode" value="{{ $sizeMode }}">
                                            <input type="hidden" name="pieces_per_box[]" class="hidden-pieces-per-box" value="{{ $item->pieces_per_box ?: 1 }}">
                                            <input type="hidden" name="pieces_per_m2[]" class="hidden-pieces-per-m2" value="{{ $item->pieces_per_m2 ?: 0 }}">
                                            <input type="hidden" name="length[]" class="hidden-length" value="{{ $item->length }}">
                                            <input type="hidden" name="width[]" class="hidden-width" value="{{ $item->width }}">
                                            <input type="hidden" name="color[]" class="hidden-variant-data" value="{{ $item->color ?? '' }}">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold unit-toggle-btn py-0 px-2" data-unit="{{ $unitName }}" style="font-size:0.72rem;">{{ $unitName }}</button>
                                            <input type="hidden" name="unit[]" class="unit-input-val" value="{{ $unitName }}">
                                        </td>
                                        <td>
                                            <input type="number" step="any" min="0.01" name="qty[]" class="form-control text-center main-qty-input" value="{{ (float)$item->qty }}" placeholder="Qty">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="price[]" class="form-control text-end price" value="{{ (float)$item->price }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="item_discount[]" class="form-control text-end item-disc-percent" value="{{ round($dPct, 2) }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control text-end input-readonly item-disc-amt" value="{{ number_format((float)$item->item_discount, 2, '.', '') }}" readonly>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control text-end input-readonly row-total" value="{{ number_format((float)$item->line_total, 2, '.', '') }}" readonly>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end fw-bold text-muted">Total Amount:</td>
                                    <td class="text-end fw-bold fs-6 text-dark"><span id="totalAmount">0.00</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="row g-3 mt-1">
                    {{-- LEFT: Payment / Receipt Voucher --}}
                    <div class="col-lg-7">
                        <div class="card-panel shadow-sm">
                            <div class="section-title mb-3">Payment / Receipt Voucher</div>
                            <div id="paymentWrapper" class="border rounded p-3 bg-light mb-3">
                                @if(!empty($existingPayments) && count($existingPayments) > 0)
                                    @foreach ($existingPayments as $pIndex => $ep)
                                        <div class="d-flex gap-2 align-items-center mb-2 payment-row flex-wrap">
                                            <select class="form-select rv-account" name="payment_account_id[]"
                                                style="max-width: 300px; flex-grow: 1;">
                                                <option value="" disabled>Select Account</option>
                                                @foreach ($accounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ $acc->id == $ep['account_id'] ? 'selected' : '' }}>{{ $acc->title }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" step="any" class="form-control text-end payment-amount"
                                                name="payment_amount[]" placeholder="Amount" value="{{ $ep['amount'] }}" style="width:140px">
                                            @if($pIndex === 0)
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPayment">
                                                    <i class="bi bi-plus"></i> Add
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-payment">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="d-flex gap-2 align-items-center mb-2 payment-row flex-wrap">
                                        <select class="form-select rv-account" name="payment_account_id[]"
                                            style="max-width: 300px; flex-grow: 1;">
                                            <option value="" selected disabled>Select Account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="any" class="form-control text-end payment-amount"
                                            name="payment_amount[]" placeholder="Amount" style="width:140px">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPayment">
                                            <i class="bi bi-plus"></i> Add
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="text-end">
                                <span class="me-2 fw-bold text-muted">Total Paid:</span>
                                <span class="fw-bold fs-6 text-success" id="totalPaid">0.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Summary --}}
                    <div class="col-lg-5">
                        <div class="card-panel shadow-sm">
                            <div class="section-title mb-3">Summary</div>
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-muted fw-medium">Total Qty</div>
                                <div class="col-5 text-end"><span id="tQty" class="fw-bold">0</span></div>
                            </div>
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-muted fw-medium">Sub-Total</div>
                                <div class="col-5 text-end fw-bold"><span id="tSub">0.00</span></div>
                                <input type="hidden" name="subtotal" id="subtotalInput" value="{{ (float)$purchase->subtotal }}">
                            </div>
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-muted fw-medium">Bill Discount</div>
                                <div class="col-5 text-end d-flex gap-1">
                                    @php
                                        $inlineVal = $purchase->items->sum('item_discount');
                                        $bSub = (float) $purchase->subtotal + $inlineVal;
                                        $bDisc = (float) $purchase->discount + $inlineVal;
                                        $bPct = $bSub > 0 ? ($bDisc / $bSub) * 100 : 0;
                                    @endphp
                                    <input type="number" class="form-control text-end form-control-sm"
                                        id="billDiscountPct" value="{{ round($bPct, 2) }}" placeholder="%" style="width: 70px;" step="0.01">
                                    <input type="number" class="form-control text-end form-control-sm"
                                        id="billDiscount" value="{{ (float) $bDisc }}" step="0.01">
                                    <input type="hidden" name="discount" id="discountInput" value="{{ (float) $purchase->discount }}">
                                </div>
                            </div>
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-muted fw-medium">Extra Cost</div>
                                <div class="col-5 text-end">
                                    <input type="number" class="form-control text-end form-control-sm" name="extra_cost"
                                        id="extraCost" value="{{ (float) $purchase->extra_cost }}">
                                </div>
                            </div>
                            <hr class="my-2 border-secondary">
                            <div class="row py-2">
                                <div class="col-6 fw-bold fs-5 text-primary">Net Payable</div>
                                <div class="col-6 text-end fw-bold fs-5 text-primary"><span id="tPayable">0.00</span></div>
                                <input type="hidden" name="net_amount" id="netAmountInput" value="{{ (float)$purchase->net_amount }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success btn-submit-update px-5 fw-bold shadow-sm">
                        <i class="bi bi-save me-2"></i> Update Purchase
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Init Global Select2
            $('.select2').select2({
                width: '100%'
            });

            // Initialize existing product selects
            $('.product-select2').each(function() {
                initProductSelect2($(this));
            });

            // Vendor Select Logic
            $('#vendorSelect').on('change', function() {
                const vendorId = $(this).val();
                if (!vendorId) {
                    $('#vendorInfoCard').addClass('d-none');
                    return;
                }

                $.get(`/vendor/${vendorId}/ledger-json`, function(data) {
                    if (data && data.vendor) {
                        $('#vi_mobile').text(data.vendor.phone || '—');
                        $('#vi_address').text(data.vendor.address || '—');
                        $('#vi_prev_bal').text(parseFloat(data.current_balance || 0).toFixed(2));
                        $('#vendorInfoCard').removeClass('d-none');
                    }
                }).fail(function() {
                    $('#vendorInfoCard').addClass('d-none');
                });
            });

            // Trigger vendor ledger load on edit if already selected
            if ($('#vendorSelect').val()) {
                $('#vendorSelect').trigger('change');
            }

            // Recalc existing rows and payments
            recalcAll();
            recalcPayments();

            // Add Row
            window.addBlankRow = function() {
                const html = `
                <tr>
                    <td>
                        <select class="form-select product-select2" name="product_id[]"></select>
                        <input type="hidden" name="size_mode[]" class="hidden-size-mode" value="by_pieces">
                        <input type="hidden" name="pieces_per_box[]" class="hidden-pieces-per-box" value="1">
                        <input type="hidden" name="pieces_per_m2[]" class="hidden-pieces-per-m2" value="0">
                        <input type="hidden" name="length[]" class="hidden-length">
                        <input type="hidden" name="width[]" class="hidden-width">
                        <input type="hidden" name="color[]" class="hidden-variant-data" value="">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold unit-toggle-btn py-0 px-2" data-unit="Pcs" style="font-size:0.72rem;">Pcs</button>
                        <input type="hidden" name="unit[]" class="unit-input-val" value="Pcs">
                    </td>
                    <td>
                        <input type="number" step="any" min="0.01" name="qty[]" class="form-control text-center main-qty-input" value="1" placeholder="Qty">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="price[]" class="form-control text-end price" value="0">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="item_discount[]" class="form-control text-end item-disc-percent" value="0">
                    </td>
                    <td>
                        <input type="number" class="form-control text-end input-readonly item-disc-amt" value="0.00" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control text-end input-readonly row-total" value="0.00" readonly>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                    </td>
                </tr>`;
                const $row = $(html);
                $('#purchaseTableBody').append($row);
                initProductSelect2($row.find('.product-select2'));
            };

            // Remove Row
            $(document).on('click', '.remove-row', function() {
                if ($('#purchaseTableBody tr').length > 1) {
                    $(this).closest('tr').remove();
                    recalcAll();
                }
            });

            // Inputs -> Calc
            $('#purchaseTableBody').on('input', '.main-qty-input, .price, .item-disc-percent', function() {
                recalcRow($(this).closest('tr'));
                recalcAll();
            });

            $('#billDiscount, #billDiscountPct, #extraCost').on('input', function() {
                recalcAll();
            });

            function normalizeDiscountInput() {
                let totalInlineDiscount = 0;
                $('#purchaseTableBody tr').each(function() {
                    const rowDiscAmt = parseFloat($(this).find('.item-disc-amt').val()) || 0;
                    totalInlineDiscount += rowDiscAmt;
                });

                let billDiscVal = parseFloat($('#billDiscount').val());
                if (isNaN(billDiscVal) || billDiscVal < totalInlineDiscount) {
                    $('#billDiscount').val(totalInlineDiscount.toFixed(2));
                }
                recalcAll();
            }

            $('#billDiscount, #billDiscountPct').on('blur', function() {
                normalizeDiscountInput();
            });

            $('#purchaseForm').on('submit', function() {
                normalizeDiscountInput();
            });

            // --- Payment Section Logic ---
            $('#btnAddPayment').on('click', function() {
                const row = `
                <div class="d-flex gap-2 align-items-center mb-2 payment-row flex-wrap">
                    <select class="form-select rv-account" name="payment_account_id[]" style="max-width: 300px; flex-grow: 1;">
                        <option value="" selected disabled>Select Account</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                        @endforeach
                    </select>
                    <input type="number" class="form-control text-end payment-amount" name="payment_amount[]" placeholder="Amount" style="width:140px">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-payment">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>`;
                $('#paymentWrapper').append(row);
            });

            $(document).on('click', '.remove-payment', function() {
                $(this).closest('.payment-row').remove();
                recalcPayments();
            });

            $(document).on('input', '.payment-amount', function() {
                recalcPayments();
            });

            function recalcPayments() {
                let total = 0;
                $('.payment-amount').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#totalPaid').text(total.toFixed(2));
            }

            // Unit toggle button
            $(document).on('click', '.unit-toggle-btn', function() {
                const $btn = $(this);
                const $row = $btn.closest('tr');
                const sizeMode = $row.data('sizemode') || $row.find('.hidden-size-mode').val();

                if (sizeMode === 'by_kg' || sizeMode === 'by_gm') {
                    let currentUnit = $btn.attr('data-unit') || 'Kg';
                    if (currentUnit.toLowerCase() === 'kg') {
                        currentUnit = 'Gm';
                        $btn.text('Gm').removeClass('btn-outline-primary').addClass('btn-outline-info');
                    } else {
                        currentUnit = 'Kg';
                        $btn.text('Kg').removeClass('btn-outline-info').addClass('btn-outline-primary');
                    }
                    $btn.attr('data-unit', currentUnit);
                    $row.find('.unit-input-val').val(currentUnit);
                    recalcRow($row);
                    recalcAll();
                }
            });

            function recalcRow($row) {
                const qty = parseFloat($row.find('.main-qty-input').val()) || 0;
                const price = parseFloat($row.find('.price').val()) || 0;
                const discPct = parseFloat($row.find('.item-disc-percent').val()) || 0;
                const sizeMode = $row.data('sizemode') || $row.find('.hidden-size-mode').val();
                const unitVal = ($row.find('.unit-input-val').val() || '').toLowerCase();
                const pieces_per_m2 = parseFloat($row.data('pieces_per_m2')) || 0;

                let gross = 0;
                if (sizeMode === 'by_size') {
                    gross = (pieces_per_m2 || 1) * qty * price;
                } else if (unitVal === 'gm' || unitVal === 'g') {
                    gross = (qty / 1000.0) * price;
                } else {
                    gross = qty * price;
                }

                const discAmt = gross * (discPct / 100);
                const lineTotal = Math.max(0, gross - discAmt);

                $row.find('.item-disc-amt').val(discAmt.toFixed(2));
                $row.find('.row-total').val(lineTotal.toFixed(2));
            }

            function recalcAll() {
                let totalQty = 0;
                let subtotal = 0;
                let totalInlineDiscount = 0;

                $('#purchaseTableBody tr').each(function() {
                    const qty = parseFloat($(this).find('.main-qty-input').val()) || 0;
                    const total = parseFloat($(this).find('.row-total').val()) || 0;
                    const rowDiscAmt = parseFloat($(this).find('.item-disc-amt').val()) || 0;

                    totalQty += qty;
                    subtotal += total;
                    totalInlineDiscount += rowDiscAmt;
                });

                const grossSubtotal = subtotal + totalInlineDiscount;

                $('#tQty').text(totalQty.toFixed(2));
                $('#tSub').text(subtotal.toFixed(2));
                $('#subtotalInput').val(subtotal.toFixed(2));
                $('#totalAmount').text(subtotal.toFixed(2));

                let additionalDiscount = parseFloat($('#discountInput').val()) || 0;
                let billDiscVal = parseFloat($('#billDiscount').val());

                if ($(document.activeElement).is('#billDiscount') || $(document.activeElement).is('#billDiscountPct')) {
                    if ($(document.activeElement).is('#billDiscountPct')) {
                        const pct = parseFloat($('#billDiscountPct').val()) || 0;
                        billDiscVal = grossSubtotal * (pct / 100);
                        $('#billDiscount').val(billDiscVal.toFixed(2));
                    }
                    if (!isNaN(billDiscVal)) {
                        additionalDiscount = Math.max(0, billDiscVal - totalInlineDiscount);
                    } else {
                        additionalDiscount = 0;
                    }
                } else {
                    billDiscVal = totalInlineDiscount + additionalDiscount;
                    $('#billDiscount').val(billDiscVal.toFixed(2));
                }
                
                const pct = grossSubtotal > 0 ? (billDiscVal / grossSubtotal) * 100 : 0;
                $('#billDiscountPct').val(pct.toFixed(2));
                $('#discountInput').val(additionalDiscount.toFixed(2));

                const extraCost = parseFloat($('#extraCost').val()) || 0;
                const net = subtotal - additionalDiscount + extraCost;

                $('#tPayable').text(net.toFixed(2));
                $('#netAmountInput').val(net.toFixed(2));
            }

            function initProductSelect2($el) {
                $el.select2({
                    placeholder: 'Search Raw Material (Name / SKU / Code)',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: '{{ route('products.ajax.search') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term,
                                page: params.page || 1,
                                raw_materials_only: 1,
                                category_id: $('#categoryFilter').val() || ''
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results || [],
                                pagination: {
                                    more: (data.pagination && data.pagination.more) ? true : false
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    templateResult: formatProduct,
                    templateSelection: formatSelection
                });

                $el.on('select2:select', function(e) {
                    const data = e.params.data;
                    const $row = $(this).closest('tr');

                    // Dynamic Unit
                    let unitName = data.unit_name || 'Pcs';
                    if (data.size_mode === 'by_kg' || data.size_mode === 'by_gm') {
                        unitName = 'Kg';
                        $row.find('.unit-toggle-btn').removeClass('btn-outline-info').addClass('btn-outline-primary');
                    }
                    $row.find('.unit-toggle-btn').text(unitName).attr('data-unit', unitName);
                    $row.find('.unit-input-val').val(unitName);

                    // Snapshots
                    $row.find('.hidden-size-mode').val(data.size_mode || '');
                    $row.find('.hidden-pieces-per-box').val(data.pieces_per_box || 1);
                    $row.find('.hidden-pieces-per-m2').val(data.pieces_per_m2 || 0);
                    $row.find('.hidden-length').val(data.length || '');
                    $row.find('.hidden-width').val(data.width || '');
                    $row.find('.hidden-variant-data').val(data.variant_data || '');

                    $row.data('sizemode', data.size_mode);
                    $row.data('pieces_per_m2', Number(data.pieces_per_m2) || 0);

                    // Discount
                    $row.find('.item-disc-percent').val(data.purchase_discount_percent || 0);

                    // Price
                    const sizeMode = data.size_mode || 'std';
                    const pM2 = parseFloat(data.purchase_price_per_m2) || 0;
                    const pPiece = parseFloat(data.purchase_price_per_piece) || parseFloat(data.trade_price) || 0;
                    let finalPrice = (sizeMode === 'by_size') ? pM2 : pPiece;

                    $row.find('.price').val(finalPrice);
                    $row.find('.main-qty-input').focus().select();

                    recalcRow($row);
                    recalcAll();
                });
            }

            function formatProduct(repo) {
                if (repo.loading) return repo.text;
                let stock = repo.stock !== undefined ? repo.stock : 0;
                let sku = repo.sku || 'N/A';
                let unit = repo.unit_name || 'Pcs';
                let cat = repo.category_name ? ` | Cat: ${repo.category_name}` : '';
                let stockVal = parseFloat(repo.stock_pieces !== undefined ? repo.stock_pieces : repo.stock) || 0;
                let badgeClass = stockVal > 0 ? 'bg-success' : 'bg-danger';

                return $(`
                <div class="clearfix">
                    <div class="float-start">
                        <div class="fw-bold">${repo.name || repo.text}</div>
                        <small class="text-muted">SKU: ${sku} | Unit: ${unit}${cat}</small>
                    </div>
                    <div class="float-end">
                        <span class="badge ${badgeClass} rounded-pill">Stock: ${stock}</span>
                    </div>
                </div>
                `);
            }

            function formatSelection(repo) {
                return repo.name || repo.text;
            }
        });
    </script>
@endsection
