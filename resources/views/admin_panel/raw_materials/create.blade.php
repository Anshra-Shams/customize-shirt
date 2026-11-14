@extends('admin_panel.layout.app')

@section('content')
<style>
    .rm-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .rm-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
    }
    .rm-body {
        padding: 1.5rem;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #334155;
        margin-bottom: 0.35rem;
    }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <a href="{{ route('raw_materials.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Back to Raw Materials
                </a>
            </div>

            <div class="rm-card">
                <div class="rm-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-plus-circle text-primary"></i> Add New Raw Material (نیا خام مال)
                        </h5>
                        <small class="text-muted">Define raw material details, cost rate, unit, and optional opening stock.</small>
                    </div>
                </div>

                <div class="rm-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('raw_materials.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Material Name --}}
                            <div class="col-md-8">
                                <label class="form-label">Material Name <span class="text-danger">*</span></label>
                                <input type="text" name="item_name" class="form-control" 
                                       placeholder="e.g. Cotton Fabric 60/60 White, Horn Button 14mm, Silk Thread" 
                                       value="{{ old('item_name') }}" required autofocus>
                            </div>

                            {{-- Item Code / SKU --}}
                            <div class="col-md-4">
                                <label class="form-label">Item Code / SKU <span class="text-danger">*</span></label>
                                <input type="text" name="item_code" class="form-control font-monospace" 
                                       value="{{ old('item_code', $nextCode) }}" required>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Unit --}}
                            <div class="col-md-6">
                                <label class="form-label">Unit of Measure (UOM) <span class="text-danger">*</span></label>
                                <select name="unit_id" class="form-select" required>
                                    <option value="">-- Select Unit (e.g. Meter, Pcs, Kg) --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Purchase Price / Cost --}}
                            <div class="col-md-6">
                                <label class="form-label">Purchase Cost Rate (Per Unit) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">Rs.</span>
                                    <input type="number" step="0.01" min="0" name="purchase_price" class="form-control font-monospace" 
                                           placeholder="0.00" value="{{ old('purchase_price') }}" required>
                                </div>
                                <small class="text-muted">Standard cost per meter / piece / kg when purchasing.</small>
                            </div>

                            {{-- Alert Quantity --}}
                            <div class="col-md-6">
                                <label class="form-label">Alert Quantity (Low Stock Warning)</label>
                                <input type="number" step="0.01" min="0" name="alert_quantity" class="form-control font-monospace" 
                                       placeholder="10" value="{{ old('alert_quantity', 10) }}">
                                <small class="text-muted">Receive notification when inventory falls below this quantity.</small>
                            </div>

                            <hr class="my-3 text-muted">

                            {{-- Optional Opening Stock --}}
                            <div class="col-12">
                                <div class="bg-light p-3 rounded border">
                                    <div class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                        <i class="fas fa-boxes text-secondary"></i> Initial Opening Stock (Optional)
                                    </div>
                                    <p class="small text-muted mb-3">If you already have this raw material available in a godown/warehouse, specify opening quantity below.</p>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Opening Quantity</label>
                                            <input type="number" step="0.01" min="0" name="opening_stock" class="form-control font-monospace" 
                                                   placeholder="0.00" value="{{ old('opening_stock', 0) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Warehouse / Godown</label>
                                            <select name="warehouse_id" class="form-select">
                                                <option value="">-- Select Warehouse --</option>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                                        {{ $wh->warehouse_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes / Description --}}
                            <div class="col-12">
                                <label class="form-label">Notes / Description</label>
                                <textarea name="notes" rows="2" class="form-control" placeholder="Optional notes regarding supplier specs, grade, color codes...">{{ old('notes') }}</textarea>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="col-12 d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                                <a href="{{ route('raw_materials.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="fas fa-save me-1"></i> Save Raw Material
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
