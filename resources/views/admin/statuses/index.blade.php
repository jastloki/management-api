@extends('admin.layouts.app')

@section('title', 'Statuses')
@section('heading', 'Status Management')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('admin.statuses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add New Status
    </a>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header bg-white border-0">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    <i class="bi bi-tags me-2 text-primary"></i>All Statuses
                </h5>
                <p class="text-muted small mb-0">Manage and view all status types</p>
            </div>
            <div class="col-auto">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search statuses..." id="statusSearch">
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($statuses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="statusesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statuses as $status)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <span class="text-white font-weight-bold">
                                            {{ $status->id }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $status->name }}</h6>
                                    <small class="text-muted">Status Name</small>
                                </div>
                            </td>
                            <td>
                                <div class="text-wrap" style="max-width: 300px;">
                                    {{ $status->description ?: '-' }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $status->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $status->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div>{{ $status->updated_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $status->updated_at->format('H:i') }}</small>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.statuses.show', $status) }}">
                                                <i class="bi bi-eye me-2"></i>View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.statuses.edit', $status) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDelete({{ $status->id }})">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($statuses->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $statuses->firstItem() }} to {{ $statuses->lastItem() }} of {{ $statuses->total() }} results
                        </div>
                        {{ $statuses->links() }}
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="bi bi-tags display-1 text-muted mb-4"></i>
                <h4 class="text-muted">No Statuses Found</h4>
                <p class="text-muted mb-4">You haven't added any statuses yet. Start by adding your first status.</p>
                <a href="{{ route('admin.statuses.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-lg me-2"></i>Add Your First Status
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this status? This action cannot be undone and may affect other records that use this status.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Delete Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.875rem;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem 0.75rem;
    }

    .table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 0.5rem;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .text-wrap {
        word-wrap: break-word;
        white-space: normal;
    }
</style>
@endsection

@section('scripts')
<script>
    // Search functionality
    document.getElementById('statusSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const tableRows = document.querySelectorAll('#statusesTable tbody tr');

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Delete confirmation
    function confirmDelete(statusId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.statuses.index') }}/${statusId}`;
        modal.show();
    }
</script>
@endsection
