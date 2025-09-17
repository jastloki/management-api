@extends('admin.layouts.app')
@section('title', 'Clients')
@section('heading', 'Client Management')

@section('page-actions')
<div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add New Client
    </a>
    <a href="{{ route('admin.clients.import.show') }}" class="btn btn-success">
        <i class="bi bi-file-earmark-excel me-2"></i>Import from Excel
    </a>
    <form method="POST" action="{{ route('admin.clients.check.email.validity') }}" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-warning">
            <i class="bi bi-envelope-check me-2"></i>Check Email Validity
        </button>
    </form>
    <button type="button" class="btn btn-warning" onclick="showBulkEmailModal()">
        <i class="bi bi-envelope me-2"></i>Send Email to current list
    </button>
    <a href="{{ route('admin.clients.template') }}" class="btn btn-outline-secondary">
        <i class="bi bi-download me-2"></i>Download Template
    </a>

    <!-- Bulk Actions -->
    <div class="btn-group bulk-actions" style="display: none;">
        <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-check-square me-2"></i>Bulk Actions
        </button>
        <ul class="dropdown-menu">
            <li><h6 class="dropdown-header">Update Status</h6></li>
            @php
                $statuses = \App\Models\Status::orderBy('name')->get();
            @endphp
            @foreach($statuses as $status)
                <li>
                    <a class="dropdown-item bulk-status-update"
                       href="#"
                       data-status-id="{{ $status->id }}"
                       data-status-name="{{ $status->name }}">
                        <i class="bi bi-tag me-2"></i>Set to {{ $status->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

@section('content')

<!-- Import Errors Details -->
@if(session('import_errors'))
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>Import Errors Details
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Field</th>
                            <th>Error</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('import_errors') as $error)
                            <tr>
                                <td>{{ $error['row'] }}</td>
                                <td>{{ $error['attribute'] }}</td>
                                <td>{{ implode(', ', $error['errors']) }}</td>
                                <td>{{ isset($error['values'][$error['attribute']]) ? $error['values'][$error['attribute']] : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header bg-white border-0">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people me-2 text-primary"></i>All Clients
                </h5>
                <p class="text-muted small mb-0">Manage and view all your clients</p>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row mt-3">
            <div class="col">
                <form method="GET" action="{{ route('admin.clients.index') }}" class="d-flex gap-3 align-items-end flex-wrap">
                    <!-- Search -->
                    <div class="flex-fill" style="min-width: 250px;">
                        <label for="search" class="form-label small text-muted">Search</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search by name, email, or company...">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div style="min-width: 150px;">
                        <label for="status_id" class="form-label small text-muted">Status</label>
                        <select name="status_id" id="status_id" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div style="min-width: 150px;">
                        <label for="user_id" class="form-label small text-muted">Assigned User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($clients->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="clientsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Client</th>
                            <th>Contact Information</th>
                            <th>Email Validation</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Comments</th>
                            <th>Joined</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr data-client-id="{{ $client->id }}">
                            <td class="ps-3">
                                <input type="checkbox" class="form-check-input client-checkbox" value="{{ $client->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <span class="text-white font-weight-bold">
                                            {{ strtoupper(substr($client->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $client->name }}</h6>
                                        <small class="text-muted">ID: #{{ $client->id }}</small>
                                        @if($client->user)
                                            <div><small class="text-info"><i class="bi bi-person me-1"></i>{{ $client->user->name }}</small></div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div><i class="bi bi-envelope me-1 text-muted"></i>{{ $client->email }}</div>
                                    @if($client->phone)
                                        <div><i class="bi bi-telephone me-1 text-muted"></i>{{ $client->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($client->is_email_valid)
                                    <span class="badge bg-success-soft text-success px-3 py-2">
                                        <i class="bi bi-check-circle me-1"></i>Valid
                                    </span>
                                @else
                                    <span class="badge bg-danger-soft text-danger px-3 py-2">
                                        <i class="bi bi-x-circle me-1"></i>Invalid
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $client->company ?? '-' }}
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle status-badge"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            data-client-id="{{ $client->id }}"
                                            style="border: none; background: transparent; padding: 4px 8px;">
                                        @if($client->getRelation("status"))
                                            <span class="badge bg-success-soft text-success px-3 py-2">
                                                <i class="bi bi-check-circle me-1"></i>{{ $client->getRelation("status")->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-soft text-secondary px-3 py-2">
                                                <i class="bi bi-question-circle me-1"></i>No Status
                                            </span>
                                        @endif
                                    </button>
                                    <ul class="dropdown-menu">
                                        @php
                                            $statuses = \App\Models\Status::orderBy('name')->get();
                                        @endphp
                                        @foreach($statuses as $status)
                                            <li>
                                                <a class="dropdown-item status-option"
                                                   href="#"
                                                   data-client-id="{{ $client->id }}"
                                                   data-status-id="{{ $status->id }}"
                                                   data-status-name="{{ $status->name }}">
                                                    <i class="bi bi-check-circle me-2"></i>{{ $status->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                            <td style="min-width: 250px; max-width: 300px;">
                                @livewire('client-comments-compact', ['client' => $client], key('client-comments-'.$client->id))
                            </td>
                            <td>
                                <div>{{ $client->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $client->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @can('clients.view')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.clients.show', $client) }}">
                                                <i class="bi bi-eye me-2"></i>View Details
                                            </a>
                                        </li>
                                        @endcan
                                        @can('clients.edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.clients.edit', $client) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('clients.delete')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDelete({{ $client->id }})">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-success" onclick="showSingleEmailModal({{ $client->id }})">
                                                <i class="bi bi-envelope me-2"></i> Send Email
                                            </button>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($clients->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $clients->firstItem() }} to {{ $clients->lastItem() }} of {{ $clients->total() }} results
                        </div>
                        {{ $clients->links() }}
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted mb-4"></i>
                <h4 class="text-muted">No Clients Found</h4>
                <p class="text-muted mb-4">You haven't added any clients yet. Start by adding your first client.</p>
                <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-lg me-2"></i>Add Your First Client
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
                <p>Are you sure you want to delete this client? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Delete Client
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-md {
        width: 48px;
        height: 48px;
        font-size: 1rem;
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }

    .bg-success-soft {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    .bg-warning-soft {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-danger-soft {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    .bg-secondary-soft {
        background-color: rgba(108, 117, 125, 0.1) !important;
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
</style>

<!-- Email Template Selection Modal -->
<div class="modal fade" id="emailTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-envelope me-2"></i>Send Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('send.html.email') }}" id="emailForm">
                @csrf
                <div class="modal-body">
                    <div id="recipientInfo" class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="recipientText">Loading...</span>
                    </div>

                    <div class="mb-3">
                        <label for="template_id" class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-text me-1"></i>Select Email Template <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="template_id" name="template_id" required onchange="loadTemplatePreview()">
                            <option value="">-- Choose a template --</option>
                            @php
                                $templates = \App\Models\EmailTemplate::active()->orderBy('name')->get();
                            @endphp
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}"
                                        data-subject="{{ $template->subject }}"
                                        data-description="{{ $template->description }}">
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text" id="templateDescription">
                            Select a template to see its description
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="proxy_id" class="form-label fw-semibold">
                            <i class="bi bi-shield-lock me-1"></i>Select Proxy (Optional)
                        </label>
                        <select class="form-select" id="proxy_id" name="proxy_id" title="Select a proxy server for email routing">
                            <option value="">🌐 No proxy (direct connection)</option>
                            @php
                                $proxies = \App\Models\Proxy::active()
                                //->working()
                                ->orderBy('name')->get();
                                $proxyCount = $proxies->count();
                            @endphp
                            @if($proxyCount > 0)
                                <optgroup label="Available Proxies ({{ $proxyCount }} active)">
                                    @foreach($proxies as $proxy)
                                        <option value="{{ $proxy->id }}"
                                                data-type="{{ $proxy->type }}"
                                                data-location="{{ $proxy->location ?? 'N/A' }}"
                                                data-response-time="{{ $proxy->response_time ?? 'N/A' }}"
                                                title="Type: {{ strtoupper($proxy->type) }} | Location: {{ $proxy->location ?? 'Unknown' }} | Response: {{ $proxy->response_time ? $proxy->response_time.'ms' : 'N/A' }}">
                                            🔒 {{ $proxy->name }}
                                            @if($proxy->location)
                                                📍 {{ $proxy->location }}
                                            @endif
                                            ⚡ {{ strtoupper($proxy->type) }}
                                            @if($proxy->response_time)
                                                ({{ $proxy->response_time }}ms)
                                            @endif
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <option disabled>⚠️ No active working proxies available</option>
                            @endif
                        </select>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Choose a proxy to route the email sending through. Leave empty for direct connection.
                            @if($proxyCount > 0)
                                <span class="text-success">{{ $proxyCount }} working proxy(ies) available.</span>
                            @else
                                <span class="text-warning">No working proxies found. Emails will be sent directly.</span>
                            @endif
                        </div>
                    </div>

                    <!-- Proxy Information Display -->
                    <div id="proxyInfo" class="d-none">
                        <div class="alert alert-info">
                            <i class="bi bi-shield-lock me-2"></i>
                            <strong>Selected Proxy:</strong>
                            <span id="proxyInfoText">-</span>
                        </div>
                    </div>

                    <!-- Template Preview -->
                    <div id="templatePreview" class="d-none">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-eye me-2"></i>Template Preview
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Subject:</strong>
                                    <div id="previewSubject" class="text-muted mt-1">-</div>
                                </div>
                                <div>
                                    <strong>Content:</strong>
                                    <div id="previewContent" class="border rounded p-3 mt-1" style="background-color: #f8f9fa; max-height: 300px; overflow-y: auto;">
                                        -
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden inputs for client IDs -->
                    <div id="clientIdsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Search functionality
    document.getElementById('clientSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const tableRows = document.querySelectorAll('#clientsTable tbody tr');

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Delete confirmation
    function confirmDelete(clientId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.clients.index') }}/${clientId}`;
        modal.show();
    }

    // Bulk selection functionality
    document.getElementById('selectAll').addEventListener('change', function(e) {
        const checkboxes = document.querySelectorAll('.client-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        updateBulkActionsVisibility();
    });

    // Individual checkbox change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('client-checkbox')) {
            updateBulkActionsVisibility();

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.client-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.client-checkbox:checked');
            const selectAllCheckbox = document.getElementById('selectAll');

            if (checkedCheckboxes.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedCheckboxes.length === allCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }
    });

    function updateBulkActionsVisibility() {
        const checkedCheckboxes = document.querySelectorAll('.client-checkbox:checked');
        const bulkActions = document.querySelector('.bulk-actions');

        if (checkedCheckboxes.length > 0) {
            bulkActions.style.display = 'block';
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Bulk status update
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('bulk-status-update')) {
            e.preventDefault();

            const statusId = e.target.getAttribute('data-status-id');
            const statusName = e.target.getAttribute('data-status-name');
            const checkedCheckboxes = document.querySelectorAll('.client-checkbox:checked');
            const clientIds = Array.from(checkedCheckboxes).map(cb => cb.value);

            if (clientIds.length === 0) {
                showToast('Please select at least one client', 'error');
                return;
            }

            if (confirm(`Are you sure you want to update ${clientIds.length} clients to "${statusName}" status?`)) {
                // Update status via AJAX
                fetch(`{{ route('admin.clients.bulk.update.status') }}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        client_ids: clientIds,
                        status_id: statusId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show updated statuses
                        location.reload();
                    } else {
                        showToast('Failed to update statuses', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while updating statuses', 'error');
                });
            }
        }
    });

    // Status update functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('status-option')) {
            e.preventDefault();

            const clientId = e.target.getAttribute('data-client-id');
            const statusId = e.target.getAttribute('data-status-id');
            const statusName = e.target.getAttribute('data-status-name');

            // Update status via AJAX
            fetch(`{{ route('admin.clients.index') }}/${clientId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status_id: statusId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the button text
                    const button = document.querySelector(`[data-client-id="${clientId}"].status-badge`);
                    button.innerHTML = `<span class="badge bg-success-soft text-success px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i>${statusName}
                    </span>`;

                    // Show success message
                    showToast('Status updated successfully!', 'success');
                } else {
                    showToast('Failed to update status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating status', 'error');
            });
        }
    });

    // Auto-submit form on filter change
    document.getElementById('status_id').addEventListener('change', function() {
        this.form.submit();
    });

    document.getElementById('user_id').addEventListener('change', function() {
        this.form.submit();
    });

    // Live search functionality
    let searchTimeout;
    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchValue = this.value;

        searchTimeout = setTimeout(() => {
            if (searchValue.length >= 3 || searchValue.length === 0) {
                this.form.submit();
            }
        }, 500);
    });

    // Toast notification function
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1050';
            document.body.appendChild(container);
        }

        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';

        const toastHTML = `
            <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        document.getElementById('toast-container').insertAdjacentHTML('beforeend', toastHTML);

        const toast = new bootstrap.Toast(document.getElementById(toastId));
        toast.show();

        // Remove toast after it's hidden
        document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }

    // Email template modal functions
    function showBulkEmailModal() {
        const checkedBoxes = document.querySelectorAll('.client-checkbox:checked');
        const clientIds = [];

        // If no checkboxes are selected, use all current page clients
        if (checkedBoxes.length === 0) {
            @foreach($clients as $client)
                clientIds.push({{ $client->id }});
            @endforeach
        } else {
            checkedBoxes.forEach(box => {
                clientIds.push(box.value);
            });
        }

        if (clientIds.length === 0) {
            showToast('No clients available to send email to', 'error');
            return;
        }

        // Update recipient info
        const recipientText = document.getElementById('recipientText');
        recipientText.textContent = `This email will be sent to ${clientIds.length} client(s)`;

        // Clear and add client IDs to form
        const container = document.getElementById('clientIdsContainer');
        container.innerHTML = '';
        clientIds.forEach(id => {
            container.innerHTML += `<input type="hidden" name="ids[]" value="${id}">`;
        });

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('emailTemplateModal'));
        modal.show();
    }

    function showSingleEmailModal(clientId) {
        // Update recipient info
        const recipientText = document.getElementById('recipientText');
        recipientText.textContent = 'This email will be sent to 1 client';

        // Clear and add client ID to form
        const container = document.getElementById('clientIdsContainer');
        container.innerHTML = `<input type="hidden" name="ids[]" value="${clientId}">`;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('emailTemplateModal'));
        modal.show();
    }

    function loadTemplatePreview() {
        const select = document.getElementById('template_id');
        const selectedOption = select.options[select.selectedIndex];
        const templateDescription = document.getElementById('templateDescription');
        const previewDiv = document.getElementById('templatePreview');

        if (select.value) {
            // Show description
            const description = selectedOption.getAttribute('data-description');
            if (description) {
                templateDescription.innerHTML = `<i class="bi bi-info-circle me-1"></i>${description}`;
            } else {
                templateDescription.innerHTML = '<i class="bi bi-info-circle me-1"></i>No description available';
            }

            // Load preview via AJAX
            fetch(`{{ route('admin.email-templates.index') }}/${select.value}/preview`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('previewSubject').textContent = data.subject;
                        document.getElementById('previewContent').innerHTML = data.content;
                        previewDiv.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.log('Error loading template preview:', error);
                    document.getElementById('previewSubject').textContent = selectedOption.getAttribute('data-subject') || 'N/A';
                    document.getElementById('previewContent').innerHTML = '<em>Preview not available</em>';
                    previewDiv.classList.remove('d-none');
                });
        } else {
            templateDescription.innerHTML = 'Select a template to see its description';
            previewDiv.classList.add('d-none');
        }
    }

    // Proxy selection functionality
    function updateProxyInfo() {
        const proxySelect = document.getElementById('proxy_id');
        const selectedOption = proxySelect.options[proxySelect.selectedIndex];
        const proxyInfoDiv = document.getElementById('proxyInfo');
        const proxyInfoText = document.getElementById('proxyInfoText');

        if (proxySelect.value) {
            const type = selectedOption.getAttribute('data-type');
            const location = selectedOption.getAttribute('data-location');
            const proxyName = selectedOption.text.replace(/🔒|📍|⚡/g, '').trim();
            const responseTime = selectedOption.getAttribute('data-response-time');

            let infoHtml = `${proxyName} - <strong>${type.toUpperCase()}</strong>`;
            if (location !== 'N/A') {
                infoHtml += ` <span class="badge bg-secondary">${location}</span>`;
            }
            if (responseTime !== 'N/A') {
                infoHtml += ` <span class="badge bg-info">${responseTime}ms</span>`;
            }

            proxyInfoText.innerHTML = infoHtml;
            proxyInfoDiv.classList.remove('d-none');
        } else {
            proxyInfoDiv.classList.add('d-none');
        }
    }

    // Initialize proxy dropdown change handler
    document.addEventListener('DOMContentLoaded', function() {
        const proxySelect = document.getElementById('proxy_id');
        if (proxySelect) {
            proxySelect.addEventListener('change', updateProxyInfo);
        }
    });

    // Show active filters indicator
    function updateActiveFiltersIndicator() {
        const statusFilter = document.getElementById('status_id').value;
        const userFilter = document.getElementById('user_id').value;
        const searchFilter = document.getElementById('search').value;

        let activeFilters = 0;
        if (statusFilter) activeFilters++;
        if (userFilter) activeFilters++;
        if (searchFilter) activeFilters++;

        const filterButton = document.querySelector('button[type="submit"]');
        if (activeFilters > 0) {
            filterButton.innerHTML = `<i class="bi bi-funnel-fill me-1"></i>Filter (${activeFilters})`;
            filterButton.classList.remove('btn-primary');
            filterButton.classList.add('btn-success');
        } else {
            filterButton.innerHTML = '<i class="bi bi-funnel me-1"></i>Filter';
            filterButton.classList.remove('btn-success');
            filterButton.classList.add('btn-primary');
        }
    }

    // Initialize filter indicator
    updateActiveFiltersIndicator();

    // Update indicator when filters change
    document.getElementById('status_id').addEventListener('change', updateActiveFiltersIndicator);
    document.getElementById('user_id').addEventListener('change', updateActiveFiltersIndicator);
    document.getElementById('search').addEventListener('input', updateActiveFiltersIndicator);
</script>
@endsection
