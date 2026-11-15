@extends('admin_panel.layout.app')

@section('content')
<style>
    .cat-stat-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
        max-width: 320px;
    }
    .cat-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }
    .cat-stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .cat-stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .cat-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .cat-card-filter {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }
    .cat-table-wrap {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .cat-table-wrap table {
        margin-bottom: 0;
    }
    .cat-table-wrap thead th {
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
    .cat-table-wrap tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        font-size: 0.88rem;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .custom-cat-modal .modal-content {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.15);
        overflow: hidden;
    }
    .custom-cat-modal .modal-header {
        background: #ffffff;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-cat-modal .modal-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
    }
    .custom-cat-modal .modal-body {
        padding: 1.5rem;
        background: #ffffff;
    }
    .custom-cat-modal .modal-footer {
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        padding: 1rem 1.5rem;
    }
    .custom-cat-modal .form-control {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 0.6rem 0.85rem;
        font-size: 0.9rem;
        height: 42px;
    }
    .custom-cat-modal .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    {{-- Header Title & Actions --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-1 text-slate-800 fw-bold d-flex align-items-center gap-2">
                <i class="fas fa-tags text-primary"></i>
                Raw Material Category
            </h4>
            <p class="text-muted small mb-0">Organize and classify raw materials into categories (e.g. Fabrics, Buttons, Threads, Zippers, Packaging).</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm px-3" 
                    id="btnOpenAddCatModal" 
                    data-toggle="modal" data-target="#catModal" 
                    data-bs-toggle="modal" data-bs-target="#catModal">
                <i class="fas fa-plus"></i>
                <span>Add Category</span>
            </button>
            <a href="{{ route('raw_materials.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-cubes"></i>
                <span>Raw Materials</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="fas fa-check-circle"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="close ms-auto" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="background:none; border:none; font-size:1.25rem;">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="close ms-auto" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="background:none; border:none; font-size:1.25rem;">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="fw-bold mb-1"><i class="fas fa-exclamation-circle me-1"></i> Please fix errors below:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="close ms-auto" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="background:none; border:none; font-size:1.25rem;">&times;</button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="cat-stat-grid">
        <div class="cat-stat-card">
            <div>
                <div class="cat-stat-label">Total Categories</div>
                <div class="cat-stat-value text-primary">{{ $stats['total_categories'] }}</div>
            </div>
            <div class="cat-stat-icon" style="background: #eff6ff; color: #2563eb;">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>

    {{-- Search Filter --}}
    <div class="cat-card-filter shadow-sm">
        <form method="GET" action="{{ route('raw_material_categories.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Search category by name..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('raw_material_categories.index') }}" class="btn btn-outline-secondary" title="Reset">
                        <i class="fas fa-redo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Categories Listing Table --}}
    <div class="cat-table-wrap shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead>
                    <tr>
                        <th style="width: 70px;">#</th>
                        <th>Category Name</th>
                        <th class="text-center" style="width: 180px;">Created Date</th>
                        <th class="text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $cat)
                        <tr>
                            <td class="text-muted fw-bold">{{ $categories->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $cat->name }}</div>
                            </td>
                            <td class="text-center text-muted small">
                                {{ $cat->created_at ? $cat->created_at->format('d M, Y') : '-' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-edit-cat" 
                                            title="Edit Category"
                                            data-id="{{ $cat->id }}"
                                            data-name="{{ $cat->name }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('raw_material_categories.destroy', $cat->id) }}" method="POST" class="d-inline" id="deleteCatForm{{ $cat->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-delete-cat" 
                                                title="Delete Category"
                                                data-id="{{ $cat->id }}"
                                                data-name="{{ $cat->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="fas fa-tags fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                    <h6 class="fw-bold">No Raw Material Categories Found</h6>
                                    <p class="small text-muted mb-3">Add categories like Fabrics, Buttons, Threads, Zippers to classify your raw materials.</p>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#catModal" data-bs-toggle="modal" data-bs-target="#catModal">
                                        <i class="fas fa-plus me-1"></i> Add First Category
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ===== MODAL POP-UP: ADD / EDIT RAW MATERIAL CATEGORY ===== --}}
<div class="modal fade custom-cat-modal" id="catModal" tabindex="-1" role="dialog" aria-labelledby="catModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title mb-0" id="catModalLabel">Add Raw Material Category</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.75rem; line-height: 1; opacity: 0.6; cursor: pointer;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="catForm" method="POST" action="{{ route('raw_material_categories.store') }}" autocomplete="off">
                @csrf
                <div id="catMethodContainer"></div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="cat_name" class="form-control" 
                               placeholder="e.g. Fabrics, Buttons, Threads, Zippers, Packaging" required autofocus>
                        <small class="text-muted d-block mt-1">Classification for raw materials used in production.</small>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnSubmitCatModal">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        function openCatModal() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function') {
                jQuery('#catModal').modal('show');
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('catModal')).show();
            } else {
                var el = document.getElementById('catModal');
                if (el) {
                    el.classList.add('show');
                    el.style.display = 'block';
                }
            }
        }

        function closeCatModal() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function') {
                jQuery('#catModal').modal('hide');
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('catModal')).hide();
            } else {
                var el = document.getElementById('catModal');
                if (el) {
                    el.classList.remove('show');
                    el.style.display = 'none';
                }
            }
        }

        function initCatLogic() {
            var modalTitle = document.getElementById('catModalLabel');
            var form = document.getElementById('catForm');
            var methodContainer = document.getElementById('catMethodContainer');
            var nameInput = document.getElementById('cat_name');
            var submitBtn = document.getElementById('btnSubmitCatModal');
            var storeRoute = "{{ route('raw_material_categories.store') }}";
            var addBtn = document.getElementById('btnOpenAddCatModal');

            function resetToAdd() {
                modalTitle.textContent = 'Add Raw Material Category';
                form.action = storeRoute;
                methodContainer.innerHTML = '';
                nameInput.value = '';
                submitBtn.textContent = 'Save Category';
            }

            if (addBtn) {
                addBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    resetToAdd();
                    openCatModal();
                });
            }

            document.querySelectorAll('.btn-edit-cat').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id;
                    var name = this.dataset.name;

                    modalTitle.textContent = 'Edit Raw Material Category';
                    form.action = '/raw-material-categories/' + id;
                    methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';
                    nameInput.value = name || '';
                    submitBtn.textContent = 'Update Category';

                    openCatModal();
                });
            });

            document.querySelectorAll('[data-dismiss="modal"], [data-bs-dismiss="modal"]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    closeCatModal();
                });
            });

            // SweetAlert Delete Confirmation
            document.querySelectorAll('.btn-delete-cat').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id;
                    var name = this.dataset.name || 'this category';
                    var form = document.getElementById('deleteCatForm' + id);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Delete Category?',
                            html: 'Are you sure you want to delete category <b>\'' + name + '\'</b>?<br><small class="text-muted">This action cannot be undone.</small>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: '<i class="fas fa-trash me-1"></i> Yes, Delete',
                            cancelButtonText: 'Cancel',
                            reverseButtons: true,
                            focusCancel: true
                        }).then(function (result) {
                            if (result.isConfirmed && form) {
                                form.submit();
                            }
                        });
                    } else if (confirm('Are you sure you want to delete category \'' + name + '\'?')) {
                        if (form) form.submit();
                    }
                });
            });

            if (typeof jQuery !== 'undefined') {
                jQuery('#catModal').on('hidden.bs.modal', function () {
                    resetToAdd();
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCatLogic);
        } else {
            initCatLogic();
        }
    })();
</script>
@endsection
