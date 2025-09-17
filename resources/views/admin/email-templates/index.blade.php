@extends('admin.layouts.app')

@section('title', 'Email Templates')
@section('heading', 'Email Template Management')

@section('page-actions')
<div class="btn-group" role="group">
    <button type="button" class="btn btn-outline-secondary" onclick="loadDefaults()">
        <i class="bi bi-download me-2"></i>Load Default Templates
    </button>
    <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add New Template
    </a>
</div>
@endsection

@section('content')
<style>
tbody tr::nts-last-child(2) ul {
    position: absolute !important;
      inset: auto 0px 0px auto !important;
      margin: 0px !important;
      transform: translate3d(-8.5px, -33px, 0px) !important;
}
</style>
<div class="card">
    <div class="card-header bg-white border-0">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    <i class="bi bi-envelope-open me-2 text-primary"></i>All Email Templates
                </h5>
                <p class="text-muted small mb-0">Manage and customize email templates</p>
            </div>
            <div class="col-auto">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search templates..." id="templateSearch">
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($templates->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="templatesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Status</th>
                            <th>Template Name</th>
                            <th>Subject</th>
                            <th>Variables</th>
                            <th>Last Updated</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                        <tr>
                            <td class="ps-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           id="status_{{ $template->id }}"
                                           {{ $template->is_active ? 'checked' : '' }}
                                           onchange="toggleStatus({{ $template->id }})">
                                    <label class="form-check-label" for="status_{{ $template->id }}">
                                        @if($template->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $template->name }}</h6>
                                    <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="text-wrap" style="max-width: 250px;">
                                    {{ Str::limit($template->subject, 60) }}
                                </div>
                            </td>
                            <td>
                                <div class="text-wrap" style="max-width: 200px;">
                                    @if($template->variables && count($template->variables) > 0)
                                        @foreach(array_slice($template->variables, 0, 3) as $var)
                                            <span class="badge bg-info me-1">{{  $var }}</span>
                                        @endforeach
                                        @if(count($template->variables) > 3)
                                            <span class="badge bg-secondary">+{{ count($template->variables) - 3 }} more</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No variables</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>{{ $template->updated_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $template->updated_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.email-templates.show', $template) }}">
                                                <i class="bi bi-eye me-2"></i>View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="previewTemplate({{ $template->id }})">
                                                <i class="bi bi-file-earmark-text me-2"></i>Preview
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.email-templates.edit', $template) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" onclick="duplicateTemplate({{ $template->id }})">
                                                <i class="bi bi-files me-2"></i>Duplicate
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDelete({{ $template->id }})">
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
            @if($templates->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{ $templates->total() }} results
                        </div>
                        {{ $templates->links() }}
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="bi bi-envelope display-1 text-muted mb-4"></i>
                <h4 class="text-muted">No Email Templates Found</h4>
                <p class="text-muted mb-4">Start by creating your first email template or load the default templates.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-outline-primary" onclick="loadDefaults()">
                        <i class="bi bi-download me-2"></i>Load Default Templates
                    </button>
                    <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Create Template
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>Template Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Subject:</label>
                    <div id="preview-subject" class="border rounded p-2 bg-light"></div>
                </div>
                <div>
                    <label class="form-label fw-bold">Content:</label>
                    <div id="preview-content" class="border rounded p-3" style="background-color: #f8f9fa;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                <p>Are you sure you want to delete this email template?</p>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
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

<!-- Toggle Status Form -->
<form id="toggleStatusForm" method="POST" style="display: none;">
    @csrf
</form>

<!-- Duplicate Form -->
<form id="duplicateForm" method="POST" style="display: none;">
    @csrf
</form>

<!-- Load Defaults Form -->
<form id="loadDefaultsForm" action="{{ route('admin.email-templates.load-defaults') }}" method="POST" style="display: none;">
    @csrf
</form>

<style>
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
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
    document.getElementById('templateSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const tableRows = document.querySelectorAll('#templatesTable tbody tr');

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Delete confirmation
    function confirmDelete(templateId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.email-templates.index') }}/${templateId}`;
        modal.show();
    }

    // Toggle status
    function toggleStatus(templateId) {
        const form = document.getElementById('toggleStatusForm');
        form.action = `{{ route('admin.email-templates.index') }}/${templateId}/toggle-status`;
        form.submit();
    }

    // Preview template
    function previewTemplate(templateId) {
        fetch(`{{ route('admin.email-templates.index') }}/${templateId}/preview`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('preview-subject').textContent = data.subject;
                    document.getElementById('preview-content').innerHTML = data.content;
                    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                    modal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load preview');
            });
    }

    // Duplicate template
    function duplicateTemplate(templateId) {
        if (confirm('Are you sure you want to duplicate this template?')) {
            const form = document.getElementById('duplicateForm');
            form.action = `{{ route('admin.email-templates.index') }}/${templateId}/duplicate`;
            form.submit();
        }
    }

    // Load default templates
    function loadDefaults() {
        if (confirm('This will load default email templates. Existing templates with the same name will not be overwritten. Continue?')) {
            document.getElementById('loadDefaultsForm').submit();
        }
    }
</script>
@endsection
