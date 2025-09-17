@extends('admin.layouts.app')

@section('title', 'View Email Template')
@section('heading', $template->name)

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Templates
    </a>
    <a href="{{ route('admin.email-templates.edit', $template) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-2"></i>Edit Template
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Template Details Card -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-envelope-open me-2 text-primary"></i>Template Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted fw-semibold">
                        <i class="bi bi-tag me-1"></i>Name:
                    </div>
                    <div class="col-md-9">
                        {{ $template->name }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted fw-semibold">
                        <i class="bi bi-toggle-on me-1"></i>Status:
                    </div>
                    <div class="col-md-9">
                        @if($template->is_active)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Active
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-x-circle me-1"></i>Inactive
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted fw-semibold">
                        <i class="bi bi-envelope me-1"></i>Subject:
                    </div>
                    <div class="col-md-9">
                        <code class="text-dark">{{ $template->subject }}</code>
                    </div>
                </div>

                @if($template->description)
                <div class="row mb-3">
                    <div class="col-md-3 text-muted fw-semibold">
                        <i class="bi bi-text-paragraph me-1"></i>Description:
                    </div>
                    <div class="col-md-9">
                        {{ $template->description }}
                    </div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-3 text-muted fw-semibold">
                        <i class="bi bi-braces me-1"></i>Variables:
                    </div>
                    <div class="col-md-9">
                        @if($template->variables && count($template->variables) > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($template->variables as $variable)
                                    <span class="badge bg-info">{{  $variable  }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">No variables defined</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted fw-semibold">
                        <i class="bi bi-calendar me-1"></i>Created:
                    </div>
                    <div class="col-md-9">
                        {{ $template->created_at->format('F d, Y \a\t g:i A') }}
                        <span class="text-muted">({{ $template->created_at->diffForHumans() }})</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 text-muted fw-semibold">
                        <i class="bi bi-clock me-1"></i>Last Updated:
                    </div>
                    <div class="col-md-9">
                        {{ $template->updated_at->format('F d, Y \a\t g:i A') }}
                        <span class="text-muted">({{ $template->updated_at->diffForHumans() }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template Content Card -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-file-richtext me-2 text-primary"></i>Template Content
                </h5>
            </div>
            <div class="card-body">
                <div class="border rounded p-3" style="background-color: #f8f9fa;">
                    {!! $template->content !!}
                </div>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-eye me-2 text-primary"></i>Preview with Sample Data
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadPreview()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh Preview
                    </button>
                </div>
            </div>
            <div class="card-body" id="previewContainer">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading preview...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4">
        <!-- Quick Actions Card -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h6 class="card-title mb-0">
                    <i class="bi bi-lightning me-2 text-warning"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.email-templates.edit', $template) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Edit Template
                    </a>

                    <form action="{{ route('admin.email-templates.toggle-status', $template) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-{{ $template->is_active ? 'warning' : 'success' }} w-100">
                            <i class="bi bi-{{ $template->is_active ? 'pause' : 'play' }} me-2"></i>
                            {{ $template->is_active ? 'Deactivate' : 'Activate' }} Template
                        </button>
                    </form>

                    <form action="{{ route('admin.email-templates.duplicate', $template) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="bi bi-files me-2"></i>Duplicate Template
                        </button>
                    </form>

                    <hr class="my-2">

                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                        <i class="bi bi-trash me-2"></i>Delete Template
                    </button>
                </div>
            </div>
        </div>

        <!-- Usage Tips Card -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h6 class="card-title mb-0">
                    <i class="bi bi-lightbulb me-2 text-info"></i>Available Variables
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">These variables can be used in the template and will be replaced with actual data when sending emails:</p>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <code>client_name</code> - Recipient's name
                    </li>
                    <li class="mb-2">
                        <code>client_email</code> - Recipient's email
                    </li>
                    <li class="mb-2">
                        <code>app_name</code> - Application name
                    </li>
                    <li class="mb-2">
                        <code>current_date</code> - Current date
                    </li>
                    <li class="mb-2">
                        <code>current_time</code> - Current time
                    </li>
                    <li class="mb-2">
                        <code>company_name</code> - Company name
                    </li>
                    <li class="mb-2">
                        <code>support_email</code> - Support email
                    </li>
                </ul>
            </div>
        </div>

        <!-- Template Info Card -->
        <div class="card">
            <div class="card-header bg-white border-0">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2 text-secondary"></i>Template Information
                </h6>
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">Template ID:</dt>
                    <dd class="col-7">#{{ $template->id }}</dd>

                    <dt class="col-5 text-muted">Status:</dt>
                    <dd class="col-7">
                        @if($template->is_active)
                            <span class="text-success">Active</span>
                        @else
                            <span class="text-secondary">Inactive</span>
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Variables:</dt>
                    <dd class="col-7">{{ $template->variables ? count($template->variables) : 0 }} detected</dd>

                    <dt class="col-5 text-muted">Created:</dt>
                    <dd class="col-7">{{ $template->created_at->format('M d, Y') }}</dd>

                    <dt class="col-5 text-muted">Updated:</dt>
                    <dd class="col-7">{{ $template->updated_at->format('M d, Y') }}</dd>
                </dl>
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
                <p>Are you sure you want to delete the template "<strong>{{ $template->name }}</strong>"?</p>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.email-templates.destroy', $template) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Delete Template
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        font-weight: 600;
    }

    code {
        padding: 2px 6px;
        background-color: #f8f9fa;
        border-radius: 3px;
    }

    .badge {
        font-weight: 500;
    }

    dl.row dt {
        font-weight: 600;
    }

    dl.row dd {
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@section('scripts')
<script>
    // Load preview on page load
    window.addEventListener('DOMContentLoaded', function() {
        loadPreview();
    });

    // Load preview function
    function loadPreview() {
        const container = document.getElementById('previewContainer');
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading preview...</p>
            </div>
        `;

        fetch('{{ route("admin.email-templates.preview", $template) }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    container.innerHTML = `
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Subject:</h6>
                            <div class="border rounded p-2 bg-light">
                                ${data.subject}
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-2">Content:</h6>
                            <div class="border rounded p-3" style="background-color: #ffffff;">
                                ${data.content}
                            </div>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Failed to load preview
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error loading preview: ${error.message}
                    </div>
                `;
            });
    }

    // Delete confirmation
    function confirmDelete() {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
</script>
@endsection
