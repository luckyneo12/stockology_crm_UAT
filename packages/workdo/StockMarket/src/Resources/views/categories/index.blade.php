@extends('layouts.main')

@section('page-title'){{ __('Stock Categories') }}@endsection
@section('page-breadcrumb'){{ __('Stock Market') }}, {{ __('Categories') }}@endsection

@section('content')
    <div class="row g-4">
        @can('stock category create')
            <div class="col-md-4">
                <div class="card" style="border-radius:16px;">
                    <div class="card-header fw-bold">{{ __('Add Category') }}</div>
                    <div class="card-body">
                        <form action="{{ route('stock-categories.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Equity, F&O" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Type</label>
                                <select name="type" class="form-select">
                                    <option value="equity">Equity</option>
                                    <option value="fo">F&O (Futures & Options)</option>
                                    <option value="commodity">Commodity</option>
                                    <option value="currency">Currency</option>
                                    <option value="index">Index</option>
                                </select>
                            </div>
                            <button type="submit" class="btn w-100" style="background:#2db57a;color:#fff;border-radius:10px;">
                                Add Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        <div class="col">
            <div class="card" style="border-radius:16px;">
                <div class="card-header fw-bold">{{ __('All Categories') }}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f9fafb;">
                                <tr>
                                    <th class="px-4">#</th>
                                    <th>Category Name</th>
                                    <th>Type</th>
                                    <th>Signals</th>
                                    @canany(['stock category edit', 'stock category delete'])
                                        <th class="text-end pe-4">Actions</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $i => $cat)
                                    <tr>
                                        <td class="px-4">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $cat->name }}</td>
                                        <td>
                                            <span class="badge" style="background:#e8f0fe; color:#3b5bdb;">
                                                {{ strtoupper($cat->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $cat->signals_count ?? 0 }}</td>
                                        @canany(['stock category edit', 'stock category delete'])
                                            <td class="text-end pe-4">
                                                @can('stock category edit')
                                                    <button class="btn btn-sm btn-outline-secondary"
                                                        onclick="editCategory({{ $cat->id }}, '{{ $cat->name }}', '{{ $cat->type }}')">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                @endcan
                                                @can('stock category delete')
                                                    <form action="{{ route('stock-categories.destroy', $cat->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Delete this category?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No categories yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editCatModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="border-radius:14px;">
                <form id="editCatForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold">Edit Category</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" id="editCatName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" id="editCatType" class="form-select">
                                <option value="equity">Equity</option>
                                <option value="fo">F&O</option>
                                <option value="commodity">Commodity</option>
                                <option value="currency">Currency</option>
                                <option value="index">Index</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function editCategory(id, name, type) {
            document.getElementById('editCatName').value = name;
            document.getElementById('editCatType').value = type;
            document.getElementById('editCatForm').action = `/stock-categories/${id}`;
            new bootstrap.Modal(document.getElementById('editCatModal')).show();
        }
    </script>
@endsection