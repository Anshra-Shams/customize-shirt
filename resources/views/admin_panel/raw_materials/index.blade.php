@extends('admin_panel.layout.app')

@section('content')
<style>
    .rm-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 992px) {
        .rm-stat-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .rm-stat-grid { grid-template-columns: 1fr; }
    }
    .rm-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }
    .rm-stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .rm-stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .rm-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .card-filter {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }
    .rm-table {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .rm-table table {
        margin-bottom: 0;
    }
    .rm-table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1rem;
        vertical-align: middle;
    }
    .rm-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        font-size: 0.88rem;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.65rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .stock-in {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .stock-low {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .stock-out {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    /* Modern Modal UI matching user mockup */
    .custom-rm-modal .modal-content {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }
    .custom-rm-modal .modal-header {
        background: #ffffff;
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-rm-modal .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
    }
    .custom-rm-modal .modal-body {
        padding: 1.75rem;
        background: #ffffff;
    }
    .custom-rm-modal .modal-footer {
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        padding: 1rem 1.75rem;
    }
    .custom-rm-modal .form-label {
        display: block !important;
        width: 100% !important;
        margin-bottom: 6px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: #374151 !important;
    }
    .custom-rm-modal input.form-control,
    .custom-rm-modal select.form-control,
    .custom-rm-modal select.form-select {
        display: block !important;
        width: 100% !important;
        height: 44px !important;
        padding: 8px 12px !important;
        font-size: 14px !important;
        color: #1f2937 !important;
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        box-shadow: none !important;
        transition: all 0.15s ease;
    }
    .custom-rm-modal input.form-control:focus,
    .custom-rm-modal select.form-control:focus,
    .custom-rm-modal select.form-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    .custom-rm-modal textarea.form-control {
        display: block !important;
        width: 100% !important;
        padding: 10px 12px !important;
        font-size: 14px !important;
        color: #1f2937 !important;
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        box-shadow: none !important;
    }
    .custom-rm-modal textarea.form-control:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }

    /* Custom iOS-style Slider Switch */
    .rm-toggle-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
        margin-bottom: 0;
        cursor: pointer;
    }
    .rm-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    .rm-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: all .25s ease;
        border-radius: 26px;
    }
    .rm-toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: all .25s ease;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.18);
    }
    .rm-toggle-switch input:checked + .rm-toggle-slider {
        background-color: #10b981;
    }
    .rm-toggle-switch input:checked + .rm-toggle-slider:before {
        transform: translateX(22px);
    }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    {{-- Header Title & Actions --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-1 text-slate-800 fw-bold d-flex align-items-center gap-2">
                <i class="fas fa-boxes-stacked text-primary"></i>
                Raw Materials
            </h4>
            <p class="text-muted small mb-0">Create, track inventory, and purchase raw materials for customized production.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm px-3" 
                    id="btnOpenAddModal" 
                    data-toggle="modal" data-target="#rawMaterialModal" 
                    data-bs-toggle="modal" data-bs-target="#rawMaterialModal">
                <i class="fas fa-plus"></i>
                <span>Add Raw Material</span>
            </button>
            <a href="{{ route('Purchase.home') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-shopping-cart"></i>
                <span>Purchases</span>
            </a>
            <a href="{{ route('warehouse_stocks.index') }}" class="btn btn-outline-info d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-warehouse"></i>
                <span>Warehouse Stock</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="fas fa-check-circle"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="fw-bold mb-1"><i class="fas fa-exclamation-circle me-1"></i> Please fix the errors below:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="rm-stat-grid">
        <div class="rm-stat-card">
            <div>
                <div class="rm-stat-label">Total Raw Materials</div>
                <div class="rm-stat-value">{{ $stats['total'] }}</div>
            </div>
            <div class="rm-stat-icon" style="background: #eff6ff; color: #2563eb;">
                <i class="fas fa-cubes"></i>
            </div>
        </div>
        <div class="rm-stat-card">
            <div>
                <div class="rm-stat-label">In Stock</div>
                <div class="rm-stat-value text-success">{{ $stats['in_stock'] }}</div>
            </div>
            <div class="rm-stat-icon" style="background: #f0fdf4; color: #16a34a;">
                <i class="fas fa-check-double"></i>
            </div>
        </div>
        <div class="rm-stat-card">
            <div>
                <div class="rm-stat-label">Low Stock Alert</div>
                <div class="rm-stat-value text-warning">{{ $stats['low_stock'] }}</div>
            </div>
            <div class="rm-stat-icon" style="background: #fffbeb; color: #d97706;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>
        <div class="rm-stat-card">
            <div>
                <div class="rm-stat-label">Out of Stock</div>
                <div class="rm-stat-value text-danger">{{ $stats['out_of_stock'] }}</div>
            </div>
            <div class="rm-stat-icon" style="background: #fef2f2; color: #dc2626;">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card-filter shadow-sm">
        <form method="GET" action="{{ route('raw_materials.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Search by Material Name or Code..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_status" class="form-select">
                    <option value="">All Stock Status</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'stock_status']))
                    <a href="{{ route('raw_materials.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="fas fa-redo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Raw Materials Listing Table --}}
    <div class="rm-table shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead>
                    <tr>
                        <th style="width: 120px;">Code / SKU</th>
                        <th>Material Name</th>
                        <th>Category</th>
                        <th>Base Unit</th>
                        <th class="text-end">Price (Per Unit)</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Reorder Alert</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rawMaterials as $item)
                        @php
                            $totalStock = (float)($item->warehouse_stocks_sum_total_pieces ?? 0);
                            $alertQty = (float)($item->alert_quantity ?? 0);
                            $unitName = optional($item->unit)->name ?: 'Pcs';
                            $dispStock = fmod($totalStock, 1) == 0 ? (int)$totalStock : number_format($totalStock, 2);
                            $dispAlert = fmod($alertQty, 1) == 0 ? (int)$alertQty : number_format($alertQty, 2);
                        @endphp
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                    {{ $item->item_code }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                @if($item->meta_description)
                                    <small class="text-muted d-block">{{ Str::limit($item->meta_description, 45) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    {{ optional($item->category_relation)->name ?: 'Uncategorized' }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-muted">{{ $unitName }}</span>
                            </td>
                            <td class="text-end font-monospace fw-bold text-slate-700">
                                Rs. {{ number_format($item->purchase_price_per_piece, 2) }}
                            </td>
                            <td class="text-center">
                                @if($totalStock <= 0)
                                    <span class="stock-badge stock-out">
                                        <i class="fas fa-circle-xmark"></i> 0 {{ $unitName }}
                                    </span>
                                @elseif($alertQty > 0 && $totalStock <= $alertQty)
                                    <span class="stock-badge stock-low" title="Low Stock Warning">
                                        <i class="fas fa-triangle-exclamation"></i> {{ $dispStock }} {{ $unitName }}
                                    </span>
                                @else
                                    <span class="stock-badge stock-in">
                                        <i class="fas fa-circle-check"></i> {{ $dispStock }} {{ $unitName }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center text-muted">
                                {{ $dispAlert }} {{ $unitName }}
                            </td>
                            <td class="text-center">
                                @if($item->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-edit-material" 
                                            title="Edit Material"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ $item->item_name }}"
                                            data-code="{{ $item->item_code }}"
                                            data-category="{{ $item->category_id }}"
                                            data-unit="{{ $item->unit_id }}"
                                            data-price="{{ (float)$item->purchase_price_per_piece }}"
                                            data-alert="{{ (float)$item->alert_quantity }}"
                                            data-description="{{ $item->meta_description }}"
                                            data-active="{{ $item->is_active ? 1 : 0 }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('raw_materials.destroy', $item->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this raw material?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Material">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="fas fa-boxes-stacked fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                    <h6 class="fw-bold">No Raw Materials Found</h6>
                                    <p class="small text-muted mb-3">Add your raw materials (fabric, buttons, thread, labels, etc.) to start managing stock and purchases.</p>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rawMaterialModal">
                                        <i class="fas fa-plus me-1"></i> Add First Raw Material
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rawMaterials->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $rawMaterials->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ===== MODAL POP-UP: ADD / EDIT RAW MATERIAL ===== --}}
<div class="modal fade custom-rm-modal" id="rawMaterialModal" tabindex="-1" role="dialog" aria-labelledby="rawMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title mb-0" id="rawMaterialModalLabel">Add Raw Material</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.75rem; line-height: 1; opacity: 0.6; cursor: pointer;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="rawMaterialForm" method="POST" action="{{ route('raw_materials.store') }}" autocomplete="off">
                @csrf
                <div id="methodContainer"></div>

                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Material Name --}}
                        <div class="col-md-6">
                            <label class="form-label">Material Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" id="modal_item_name" class="form-control" 
                                   placeholder="e.g. Cotton Fabric 60/60 White" required>
                        </div>

                        {{-- Material Code --}}
                        <div class="col-md-6">
                            <label class="form-label">Material Code <span class="text-danger">*</span></label>
                            <input type="text" name="item_code" id="modal_item_code" class="form-control font-monospace" 
                                   placeholder="RM-001" value="{{ $nextCode ?? 'RM-0001' }}" required>
                        </div>

                        {{-- Category --}}
                        <div class="col-md-6">
                            <label class="form-label mb-1">Category</label>
                            <div class="d-flex" style="gap: 6px;">
                                <select name="category_id" id="modal_category_id" class="form-control" style="flex: 1;">
                                    <option value="">Select...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-light border shadow-sm px-3" 
                                        id="btnOpenQuickCatModal" 
                                        title="Add New Category" 
                                        style="height: 44px; border-radius: 8px; font-weight: bold; font-size: 18px; line-height: 1; background: #e5e7eb; color: #111827; flex-shrink: 0;">
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Base Unit --}}
                        <div class="col-md-6">
                            <label class="form-label mb-1">Base Unit <span class="text-danger">*</span></label>
                            <select name="unit_id" id="modal_unit_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Price (Per Unit) --}}
                        <div class="col-md-6">
                            <label class="form-label">Price (Per Unit) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="purchase_price" id="modal_purchase_price" 
                                   class="form-control font-monospace" placeholder="0.00" required>
                        </div>

                        {{-- Reorder Alert --}}
                        <div class="col-md-6">
                            <label class="form-label">Reorder Alert</label>
                            <input type="number" step="0.01" min="0" name="alert_quantity" id="modal_alert_quantity" 
                                   class="form-control font-monospace" placeholder="0.00" value="0.00">
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="modal_description" rows="3" class="form-control" 
                                      placeholder="Optional details..."></textarea>
                        </div>

                        {{-- Status Switch (iOS Slider) --}}
                        <div class="col-12 pt-2">
                            <div class="d-flex align-items-center gap-2">
                                <label class="rm-toggle-switch">
                                    <input type="checkbox" name="is_active" id="modal_is_active" value="1" checked>
                                    <span class="rm-toggle-slider"></span>
                                </label>
                                <span class="fw-bold" id="modal_status_label" style="font-size: 14px; color: #10b981;">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnSubmitModal">Save Material</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== QUICK ADD CATEGORY MODAL ===== --}}
<div class="modal fade" id="quickCategoryModal" tabindex="-1" role="dialog" aria-labelledby="quickCategoryModalLabel" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document" style="z-index: 1071;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-3 px-3 border-bottom d-flex align-items-center justify-content-between bg-white">
                <h6 class="modal-title fw-bold mb-0 text-dark">Add New Category</h6>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; line-height: 1; opacity: 0.6; cursor: pointer;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 bg-white">
                <div class="mb-2">
                    <label class="form-label fw-bold small text-muted mb-1">Category Name <span class="text-danger">*</span></label>
                    <input type="text" id="quick_cat_name" class="form-control" placeholder="e.g. Fabrics, Buttons" style="height: 40px; border-radius: 6px;">
                    <div id="quick_cat_error" class="text-danger small mt-1" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer p-2 bg-light d-flex justify-content-end gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary px-3 fw-bold" id="btnSaveQuickCategory">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        function openRawModal() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function') {
                jQuery('#rawMaterialModal').modal('show');
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('rawMaterialModal')).show();
            } else {
                var el = document.getElementById('rawMaterialModal');
                if (el) {
                    el.classList.add('show');
                    el.style.display = 'block';
                }
            }
        }

        function closeRawModal() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function') {
                jQuery('#rawMaterialModal').modal('hide');
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('rawMaterialModal')).hide();
            } else {
                var el = document.getElementById('rawMaterialModal');
                if (el) {
                    el.classList.remove('show');
                    el.style.display = 'none';
                }
            }
        }

        window.openRawModal = openRawModal;
        window.closeRawModal = closeRawModal;

        function initModalLogic() {
            var modalEl = document.getElementById('rawMaterialModal');
            var modalTitle = document.getElementById('rawMaterialModalLabel');
            var modalForm = document.getElementById('rawMaterialForm');
            var methodContainer = document.getElementById('methodContainer');
            var defaultNextCode = "{{ $nextCode ?? 'RM-0001' }}";
            var storeRoute = "{{ route('raw_materials.store') }}";
            var switchEl = document.getElementById('modal_is_active');
            var submitBtn = document.getElementById('btnSubmitModal');
            var addBtn = document.getElementById('btnOpenAddModal');

            if (!modalEl || !modalForm) return;

            function updateStatusLabel(isChecked) {
                var label = document.getElementById('modal_status_label');
                if (!label) return;
                label.textContent = isChecked ? 'Active' : 'Inactive';
                label.style.color = isChecked ? '#10b981' : '#64748b';
            }

            function resetToAddMode() {
                modalTitle.textContent = 'Add Raw Material';
                modalForm.action = storeRoute;
                methodContainer.innerHTML = '';

                document.getElementById('modal_item_name').value = '';
                document.getElementById('modal_item_code').value = defaultNextCode;
                document.getElementById('modal_category_id').value = '';
                document.getElementById('modal_unit_id').value = '';
                document.getElementById('modal_purchase_price').value = '';
                document.getElementById('modal_alert_quantity').value = '0.00';
                document.getElementById('modal_description').value = '';

                if (switchEl) {
                    switchEl.checked = true;
                    updateStatusLabel(true);
                }

                if (submitBtn) {
                    submitBtn.textContent = 'Save Material';
                }
            }

            // Click on Add Raw Material Button
            if (addBtn) {
                addBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    resetToAddMode();
                    openRawModal();
                });
            }

            // Click on Edit Buttons
            document.querySelectorAll('.btn-edit-material').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id;
                    var name = this.dataset.name;
                    var code = this.dataset.code;
                    var cat = this.dataset.category;
                    var unit = this.dataset.unit;
                    var price = this.dataset.price;
                    var alertQty = this.dataset.alert;
                    var desc = this.dataset.description;
                    var active = this.dataset.active === '1';

                    modalTitle.textContent = 'Edit Raw Material';
                    modalForm.action = '/raw-materials/' + id;
                    methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                    document.getElementById('modal_item_name').value = name || '';
                    document.getElementById('modal_item_code').value = code || '';
                    document.getElementById('modal_category_id').value = cat || '';
                    document.getElementById('modal_unit_id').value = unit || '';
                    document.getElementById('modal_purchase_price').value = price || '';
                    document.getElementById('modal_alert_quantity').value = alertQty || '0.00';
                    document.getElementById('modal_description').value = desc || '';

                    if (switchEl) {
                        switchEl.checked = active;
                        updateStatusLabel(active);
                    }

                    if (submitBtn) {
                        submitBtn.textContent = 'Update Material';
                    }

                    openRawModal();
                });
            });

            // Toggle switch listener
            if (switchEl) {
                switchEl.addEventListener('change', function () {
                    updateStatusLabel(this.checked);
                });
            }

            // Close button listeners
            document.querySelectorAll('[data-dismiss="modal"], [data-bs-dismiss="modal"]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    closeRawModal();
                });
            });

            // Auto-open if query param open_modal=1
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('open_modal') === '1') {
                resetToAddMode();
                openRawModal();
            }

            // Modal reset on hide
            if (typeof jQuery !== 'undefined') {
                jQuery('#rawMaterialModal').on('hidden.bs.modal', function () {
                    resetToAddMode();
                });
            }

            // Quick Add Category Modal Logic
            var quickCatModal = document.getElementById('quickCategoryModal');
            var btnOpenQuickCat = document.getElementById('btnOpenQuickCatModal');
            var btnSaveQuickCat = document.getElementById('btnSaveQuickCategory');
            var quickCatNameInput = document.getElementById('quick_cat_name');
            var quickCatError = document.getElementById('quick_cat_error');

            function openQuickCat() {
                if (quickCatError) {
                    quickCatError.style.display = 'none';
                    quickCatError.textContent = '';
                }
                if (quickCatNameInput) {
                    quickCatNameInput.value = '';
                }

                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function') {
                    jQuery('#quickCategoryModal').modal('show');
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(quickCatModal).show();
                } else if (quickCatModal) {
                    quickCatModal.classList.add('show');
                    quickCatModal.style.display = 'block';
                }
            }

            function closeQuickCat() {
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function') {
                    jQuery('#quickCategoryModal').modal('hide');
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(quickCatModal).hide();
                } else if (quickCatModal) {
                    quickCatModal.classList.remove('show');
                    quickCatModal.style.display = 'none';
                }
            }

            if (btnOpenQuickCat) {
                btnOpenQuickCat.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openQuickCat();
                });
            }

            if (btnSaveQuickCat) {
                btnSaveQuickCat.addEventListener('click', function (e) {
                    e.preventDefault();
                    var name = quickCatNameInput ? quickCatNameInput.value.trim() : '';
                    if (!name) {
                        if (quickCatError) {
                            quickCatError.textContent = 'Category name is required.';
                            quickCatError.style.display = 'block';
                        }
                        return;
                    }

                    btnSaveQuickCat.disabled = true;
                    btnSaveQuickCat.textContent = 'Saving...';

                    fetch("{{ route('raw_material_categories.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ name: name })
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        btnSaveQuickCat.disabled = false;
                        btnSaveQuickCat.textContent = 'Save';

                        if (data.success && data.category) {
                            var selectEl = document.getElementById('modal_category_id');
                            if (selectEl) {
                                var option = new Option(data.category.name, data.category.id, true, true);
                                selectEl.add(option);
                                selectEl.value = data.category.id;
                            }
                            closeQuickCat();
                        } else {
                            if (quickCatError) {
                                quickCatError.textContent = data.message || 'Error creating category';
                                quickCatError.style.display = 'block';
                            }
                        }
                    })
                    .catch(function (err) {
                        btnSaveQuickCat.disabled = false;
                        btnSaveQuickCat.textContent = 'Save';
                        if (quickCatError) {
                            quickCatError.textContent = 'Category already exists or network error.';
                            quickCatError.style.display = 'block';
                        }
                    });
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initModalLogic);
        } else {
            initModalLogic();
        }
    })();
</script>
@endsection
