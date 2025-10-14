@extends('admin.layouts.app')

@section('title', 'Proxies')
@section('heading', 'Proxy Management')

@section('page-actions')
<div class="btn-group" role="group">
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-download me-2"></i>Export
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('admin.proxies.export', ['format' => 'txt']) }}">
                <i class="bi bi-filetype-txt me-2"></i>Text Format
            </a></li>
            <li><a class="dropdown-item" href="{{ route('admin.proxies.export', ['format' => 'csv']) }}">
                <i class="bi bi-filetype-csv me-2"></i>CSV Format
            </a></li>
            <li><a class="dropdown-item" href="{{ route('admin.proxies.export', ['format' => 'json']) }}">
                <i class="bi bi-filetype-json me-2"></i>JSON Format
            </a></li>
        </ul>
    </div>
    <!--<button type="button" class="btn btn-outline-info" onclick="bulkTest()">
        <i class="bi bi-speedometer2 me-2"></i>Bulk Test
    </button>-->
    <a href="{{ route('admin.proxies.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add New Proxy
    </a>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header bg-white border-0">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    <i class="bi bi-globe me-2 text-primary"></i>All Proxies
                </h5>
                <p class="text-muted small mb-0">Manage and test your proxy servers</p>
            </div>
            <div class="col-auto">
                <div class="row g-2">
                    <div class="col">
                        <select class="form-select form-select-sm" id="typeFilter">
                            <option value="">All Types</option>
                            @foreach(\App\Models\Proxy::getTypes() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="working">Working</option>
                            <option value="failed">Failed</option>
                            <option value="untested">Untested</option>
                        </select>
                    </div>
                    <div class="col">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Search proxies..." id="proxySearch">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($proxies->count() > 0)
            <!-- Bulk Actions -->
            <div class="border-bottom p-3 bg-light" id="bulkActions" style="display: none;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span id="selectedCount">0</span> proxy(ies) selected
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" onclick="testSelected()">
                            <i class="bi bi-speedometer2 me-1"></i>Test Selected
                        </button>
                        <button class="btn btn-outline-success" onclick="activateSelected()">
                            <i class="bi bi-check-circle me-1"></i>Activate
                        </button>
                        <button class="btn btn-outline-secondary" onclick="deactivateSelected()">
                            <i class="bi bi-x-circle me-1"></i>Deactivate
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteSelected()">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="proxiesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th>Status</th>
                            <th>Name</th>
                            <th>URL & Details</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Test Status</th>
                            <th>Last Tested</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proxies as $proxy)
                        <tr data-type="{{ $proxy->type }}" data-status="{{ $proxy->status }}">
                            <td class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input proxy-checkbox" type="checkbox" value="{{ $proxy->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           id="status_{{ $proxy->id }}"
                                           {{ $proxy->is_active ? 'checked' : '' }}
                                           onchange="toggleStatus({{ $proxy->id }})">
                                    <label class="form-check-label" for="status_{{ $proxy->id }}">
                                        @if($proxy->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $proxy->name }}</h6>
                                    <small class="text-muted">{{ Str::limit($proxy->description, 50) }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $proxy->url }}</strong>
                                    @if($proxy->port)
                                        <span class="text-muted">:{{ $proxy->port }}</span>
                                    @endif
                                </div>
                                @if($proxy->username)
                                    <small class="text-muted">
                                        <i class="bi bi-person-check me-1"></i>{{ $proxy->username }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $proxy->getTypeBadgeClass() }}">
                                    {{ strtoupper($proxy->type) }}
                                </span>
                            </td>
                            <td>
                                @if($proxy->location)
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt me-1 text-muted"></i>
                                        {{ $proxy->location }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge {{ $proxy->getStatusBadgeClass() }} me-2">
                                        {{ ucfirst($proxy->status) }}
                                    </span>
                                    @if($proxy->response_time)
                                        <small class="text-muted">{{ $proxy->response_time }}ms</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($proxy->last_tested_at)
                                    <div>{{ $proxy->last_tested_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $proxy->last_tested_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.proxies.show', $proxy) }}">
                                                <i class="bi bi-eye me-2"></i>View Details
                                            </a>
                                        </li>
                                        <!--<li>
                                            <button class="dropdown-item" onclick="testProxy({{ $proxy->id }})">
                                                <i class="bi bi-speedometer2 me-2"></i>Test Connection
                                            </button>
                                        </li>-->
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.proxies.edit', $proxy) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" onclick="duplicateProxy({{ $proxy->id }})">
                                                <i class="bi bi-files me-2"></i>Duplicate
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDelete({{ $proxy->id }})">
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
            @if($proxies->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $proxies->firstItem() }} to {{ $proxies->lastItem() }} of {{ $proxies->total() }} results
                        </div>
                        {{ $proxies->links() }}
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="bi bi-globe display-1 text-muted mb-4"></i>
                <h4 class="text-muted">No Proxies Found</h4>
                <p class="text-muted mb-4">Start by adding your first proxy server to manage your connections.</p>
                <a href="{{ route('admin.proxies.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add First Proxy
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Test Result Modal -->
<div class="modal fade" id="testResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-speedometer2 me-2"></i>Proxy Test Result
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="test-result-content"></div>
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
                <p>Are you sure you want to delete this proxy?</p>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Delete Proxy
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form id="toggleStatusForm" method="POST" style="display: none;">
    @csrf
</form>

<form id="duplicateForm" method="POST" style="display: none;">
    @csrf
</form>

<form id="bulkTestForm" action="{{ route('admin.proxies.bulk-test') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="proxy_ids" id="bulkTestIds">
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

    .badge {
        font-size: 0.75em;
    }
</style>
@endsection

@section('scripts')
<script>
    // Search functionality
    document.getElementById('proxySearch').addEventListener('input', function(e) {
        filterTable();
    });

    // Filter functionality
    document.getElementById('typeFilter').addEventListener('change', function(e) {
        filterTable();
    });

    document.getElementById('statusFilter').addEventListener('change', function(e) {
        filterTable();
    });

    function filterTable() {
        const searchTerm = document.getElementById('proxySearch').value.toLowerCase();
        const typeFilter = document.getElementById('typeFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const tableRows = document.querySelectorAll('#proxiesTable tbody tr');

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const type = row.dataset.type;
            const status = row.dataset.status;

            const matchesSearch = text.includes(searchTerm);
            const matchesType = !typeFilter || type === typeFilter;
            const matchesStatus = !statusFilter || status === statusFilter;

            row.style.display = (matchesSearch && matchesType && matchesStatus) ? '' : 'none';
        });
    }

    // Checkbox handling
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.proxy-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    document.querySelectorAll('.proxy-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.proxy-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = checkedBoxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Delete confirmation
    function confirmDelete(proxyId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.proxies.index') }}/${proxyId}`;
        modal.show();
    }

    // Toggle status
    function toggleStatus(proxyId) {
        const form = document.getElementById('toggleStatusForm');
        form.action = `{{ route('admin.proxies.index') }}/${proxyId}/toggle-status`;
        form.submit();
    }

    // Test proxy
    function testProxy(proxyId) {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Testing...';
        button.disabled = true;

        fetch(`{{ route('admin.proxies.index') }}/${proxyId}/test`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const modal = new bootstrap.Modal(document.getElementById('testResultModal'));
            const content = document.getElementById('test-result-content');

            if (data.success) {
                content.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>${data.message}
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle me-2"></i>${data.message}
                    </div>
                `;
            }

            modal.show();

            // Refresh the page to update the status
            setTimeout(() => {
                location.reload();
            }, 2000);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to test proxy');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    // Duplicate proxy
    function duplicateProxy(proxyId) {
        if (confirm('Are you sure you want to duplicate this proxy?')) {
            const form = document.getElementById('duplicateForm');
            form.action = `{{ route('admin.proxies.index') }}/${proxyId}/duplicate`;
            form.submit();
        }
    }

    // Bulk test
    function bulkTest() {
        const checkedBoxes = document.querySelectorAll('.proxy-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select proxies to test');
            return;
        }

        if (confirm(`Test ${checkedBoxes.length} selected proxy(ies)?`)) {
            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            document.getElementById('bulkTestIds').value = JSON.stringify(ids);
            document.getElementById('bulkTestForm').submit();
        }
    }

    // Test selected proxies
    function testSelected() {
        bulkTest();
    }

    // Activate selected
    function activateSelected() {
        const checkedBoxes = document.querySelectorAll('.proxy-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select proxies to activate');
            return;
        }
        // Implementation for bulk activate
        alert('Bulk activate functionality to be implemented');
    }

    // Deactivate selected
    function deactivateSelected() {
        const checkedBoxes = document.querySelectorAll('.proxy-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select proxies to deactivate');
            return;
        }
        // Implementation for bulk deactivate
        alert('Bulk deactivate functionality to be implemented');
    }

    // Delete selected
    function deleteSelected() {
        const checkedBoxes = document.querySelectorAll('.proxy-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select proxies to delete');
            return;
        }

        if (confirm(`Delete ${checkedBoxes.length} selected proxy(ies)? This action cannot be undone!`)) {
            // Implementation for bulk delete
            alert('Bulk delete functionality to be implemented');
        }
    }
</script>
@endsection
