@extends('admin.layouts.app')

@section('title', 'Status Details')
@section('heading', 'Status Details')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('admin.statuses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Statuses
    </a>
    <a href="{{ route('admin.statuses.edit', $status) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-2"></i>Edit Status
    </a>
    <button class="btn btn-outline-danger" onclick="confirmDelete({{ $status->id }})">
        <i class="bi bi-trash me-2"></i>Delete Status
    </button>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <!-- Main Status Information -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-tag me-2 text-primary"></i>{{ $status->name }}
                        </h5>
                        <p class="text-muted small mb-0">Status ID: #{{ $status->id }}</p>
                    </div>
                    <div class="col-auto">
                        <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center">
                            <span class="text-white font-weight-bold fs-4">
                                {{ strtoupper(substr($status->name, 0, 2)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Status Name -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-semibold mb-2">
                            <i class="bi bi-tag me-1"></i>Status Name
                        </h6>
                        <p class="mb-0 fs-5">{{ $status->name }}</p>
                    </div>

                    <!-- Status ID -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-semibold mb-2">
                            <i class="bi bi-hash me-1"></i>Status ID
                        </h6>
                        <p class="mb-0 text-muted">#{{ $status->id }}</p>
                    </div>

                    <!-- Description -->
                    <div class="col-12 mb-4">
                        <h6 class="fw-semibold mb-2">
                            <i class="bi bi-text-paragraph me-1"></i>Description
                        </h6>
                        @if($status->description)
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $status->description }}</p>
                            </div>
                        @else
                            <p class="text-muted fst-italic">No description provided</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Timestamps Information -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2 text-info"></i>Timeline Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="bi bi-calendar-plus text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Created</h6>
                                <p class="mb-0 text-muted">{{ $status->created_at->format('l, F j, Y \a\t g:i A') }}</p>
                                <small class="text-muted">{{ $status->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="bi bi-calendar-check text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Last Updated</h6>
                                <p class="mb-0 text-muted">{{ $status->updated_at->format('l, F j, Y \a\t g:i A') }}</p>
                                <small class="text-muted">{{ $status->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2 text-secondary"></i>System Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded text-center">
                            <i class="bi bi-database display-6 text-primary mb-2"></i>
                            <h6 class="mb-1">Database ID</h6>
                            <p class="mb-0 fw-bold">{{ $status->id }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded text-center">
                            <i class="bi bi-table display-6 text-info mb-2"></i>
                            <h6 class="mb-1">Table</h6>
                            <p class="mb-0 fw-bold">statuses</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded text-center">
                            <i class="bi bi-key display-6 text-warning mb-2"></i>
                            <h6 class="mb-1">Model</h6>
                            <p class="mb-0 fw-bold">Status</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2 text-warning"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="{{ route('admin.statuses.edit', $status) }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-pencil-square me-2"></i>Edit This Status
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('admin.statuses.create') }}" class="btn btn-outline-success w-100">
                            <i class="bi bi-plus-circle me-2"></i>Create New Status
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete the status "<strong>{{ $status->name }}</strong>"?</p>
                <p class="text-muted small">This may affect other records that reference this status.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.statuses.destroy', $status) }}" method="POST" style="display: inline;">
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
    .avatar-lg {
        width: 80px;
        height: 80px;
        font-size: 1.5rem;
    }

    .bg-success-soft {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .bg-warning-soft {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-primary-soft {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    .card {
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .btn-outline-primary:hover,
    .btn-outline-success:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@section('scripts')
<script>
    // Delete confirmation
    function confirmDelete(statusId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Add smooth scroll behavior for page navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Add tooltips to action buttons
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endsection
