@extends('admin.layouts.app')

@section('title', 'Email Queue Management')
@section('heading', 'Email Queue Management')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.emails.analytics') }}" class="btn btn-info">
        <i class="bi bi-graph-up me-2"></i>Analytics
    </a>
    <a href="{{ route('admin.emails.providers') }}" class="btn btn-outline-primary">
        <i class="bi bi-gear me-2"></i>Providers
    </a>
    <button type="button" class="btn btn-secondary" onclick="refreshProviderStatus()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh Providers
    </button>
</div>
@endsection

@section('content')
<!-- Email Provider Status Cards -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow mb-3">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">Email Provider Status</h6>
                <button class="btn btn-sm btn-outline-primary" onclick="testAllProviders()">
                    <i class="bi bi-shield-check me-1"></i>Test All
                </button>
            </div>
            <div class="card-body">
                <div class="row" id="provider-status-cards">
                    @foreach($providers as $providerKey => $provider)
                    <div class="col-md-4 mb-3">
                        <div class="card border-start border-{{ $provider['available'] ? 'success' : 'danger' }} border-4 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1">{{ $provider['display_name'] }}</h6>
                                        <span class="badge bg-{{ $provider['available'] ? 'success' : 'danger' }} mb-2">
                                            {{ $provider['available'] ? 'Available' : 'Unavailable' }}
                                        </span>
                                        @if(isset($provider['error']))
                                            <small class="text-danger d-block">{{ $provider['error'] }}</small>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-sm btn-outline-info" onclick="testProvider('{{ $providerKey }}')">
                                            <i class="bi bi-shield-check"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-primary border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-warning border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['pending'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-info border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">Queued</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['queued'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-list fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-secondary border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-secondary text-uppercase mb-1">Sending</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['sending'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-send fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-success border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">Sent</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['sent'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-danger border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-danger text-uppercase mb-1">Failed</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['failed'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-success border-4 shadow h-100 py-2 cursor-pointer" onclick="filterByValidEmails()" title="Click to filter valid emails">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">Valid Emails</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['valid_emails'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-danger border-4 shadow h-100 py-2 cursor-pointer" onclick="filterByInvalidEmails()" title="Click to filter invalid emails">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-danger text-uppercase mb-1">Invalid Emails</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['invalid_emails'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-x-circle fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card border-start border-info border-4 shadow h-100 py-2 cursor-pointer" onclick="filterQueueEligible()" title="Click to filter queue eligible clients">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">Queue Eligible</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $statistics['queue_eligible'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-envelope-check fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions and Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary">Bulk Actions & Filters</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Filters -->
            <div class="col-md-8">
                <form method="GET" action="{{ route('admin.emails.index') }}" class="d-flex gap-2 flex-wrap">
                    <select name="email_status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('email_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="queued" {{ request('email_status') == 'queued' ? 'selected' : '' }}>Queued</option>
                        <option value="sending" {{ request('email_status') == 'sending' ? 'selected' : '' }}>Sending</option>
                        <option value="sent" {{ request('email_status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('email_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                    <select name="is_email_valid" class="form-select form-select-sm">
                        <option value="">All Email Types</option>
                        <option value="1" {{ request('is_email_valid') == '1' ? 'selected' : '' }}>Valid Emails</option>
                        <option value="0" {{ request('is_email_valid') == '0' ? 'selected' : '' }}>Invalid Emails</option>
                    </select>
                    <select name="queue_eligible" class="form-select form-select-sm">
                        <option value="">All Clients</option>
                        <option value="1" {{ request('queue_eligible') == '1' ? 'selected' : '' }}>Queue Eligible</option>
                    </select>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Name, email, company..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.emails.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="col-md-4">
                <div class="d-flex justify-content-end gap-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" id="queueDropdown">
                            <i class="bi bi-send me-1"></i>Queue Emails
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">Selected Clients</h6></li>
                            <li><button class="dropdown-item" onclick="showProviderModal('queueSelected')">Queue Selected</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Bulk Operations</h6></li>
                            <li><button class="dropdown-item" onclick="showProviderModal('queueAll', 'pending')">Queue All Pending</button></li>
                            <li><button class="dropdown-item" onclick="showProviderModal('queueAll', 'failed')">Queue All Failed</button></li>
                            <li><button class="dropdown-item" onclick="showProviderModal('queueAll', 'all')">Queue All Eligible</button></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Status
                        </button>
                        <ul class="dropdown-menu">
                            <li><button class="dropdown-item" onclick="resetSelected()">Reset Selected</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clients Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary">Email Queue Status</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable">
                <thead>
                    <tr>
                        <th width="30px">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleAllCheckboxes()">
                        </th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Email Valid</th>
                        <th>Email Status</th>
                        <th>Provider</th>
                        <th>Email Sent At</th>
                        <th width="200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <input type="checkbox" class="client-checkbox" value="{{ $client->id }}">
                            </td>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->company ?: '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $client->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </td>
                            <td>
                                @if($client->is_email_valid)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Valid
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>Invalid
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($client->email_status) {
                                        'pending' => 'warning',
                                        'queued' => 'info',
                                        'sending' => 'secondary',
                                        'sent' => 'success',
                                        'failed' => 'danger',
                                        default => 'light'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ ucfirst($client->email_status) }}
                                </span>
                            </td>
                            <td>
                                @if($client->email_provider)
                                    <span class="badge bg-primary">{{ ucfirst($client->email_provider) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $client->email_sent_at ? $client->email_sent_at->format('M d, Y H:i') : '-' }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(in_array($client->email_status, ['pending', 'failed']))
                                        @if($client->is_email_valid)
                                            <button type="button" class="btn btn-success btn-sm" onclick="showSingleProviderModal({{ $client->id }}, '{{ $client->name }}')" title="Queue Email">
                                                <i class="bi bi-send"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-secondary btn-sm" disabled title="Cannot queue - invalid email">
                                                <i class="bi bi-send-slash"></i>
                                            </button>
                                        @endif
                                    @endif

                                    @if(!in_array($client->email_status, ['pending', 'sending']))
                                        <form method="POST" action="{{ route('admin.emails.reset.single', $client) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" title="Reset Status">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-info btn-sm" title="View Client">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No clients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $clients->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Email Provider Selection Modal -->
<div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="providerModalLabel">Select Email Provider</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modalDescription"></p>

                <!-- Provider Selection -->
                <div class="mb-3">
                    <label for="selectedProvider" class="form-label">Choose Email Provider:</label>
                    <select class="form-select" id="selectedProvider" name="email_provider">
                        <option value="">Select Provider...</option>
                        @foreach($providers as $providerKey => $provider)
                            @if($provider['available'])
                                <option value="{{ $providerKey }}">
                                    {{ $provider['display_name'] }}
                                    <span class="text-success">✓ Available</span>
                                </option>
                            @else
                                <option value="{{ $providerKey }}" disabled>
                                    {{ $provider['display_name'] }}
                                    <span class="text-danger">✗ Unavailable</span>
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <div class="form-text">Choose which email provider to use for sending emails.</div>
                </div>

                <!-- Provider Status -->
                <div class="alert alert-info" id="providerInfo" style="display: none;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="providerInfoText"></span>
                    </div>
                </div>

                <!-- Error Display -->
                <div class="alert alert-danger" id="providerError" style="display: none;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="providerErrorText"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAction" onclick="executeAction()">
                    <i class="bi bi-send me-1"></i>Queue Emails
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms for bulk actions -->
<form id="queueBatchForm" method="POST" action="{{ route('admin.emails.queue.batch') }}" style="display: none;">
    @csrf
    <div id="queueBatchInputs"></div>
    <input type="hidden" name="email_provider" id="batchProvider">
</form>

<form id="queueAllForm" method="POST" action="{{ route('admin.emails.queue.all') }}" style="display: none;">
    @csrf
    <input type="hidden" name="status" id="queueAllStatus">
    <input type="hidden" name="email_provider" id="allProvider">
</form>

<form id="queueSingleForm" method="POST" action="" style="display: none;">
    @csrf
    <input type="hidden" name="email_provider" id="singleProvider">
</form>

<form id="resetBatchForm" method="POST" action="{{ route('admin.emails.reset.batch') }}" style="display: none;">
    @csrf
    <div id="resetBatchInputs"></div>
</form>
@endsection

@section('scripts')
<script>
// Global variables to track current action
let currentAction = '';
let currentStatus = '';
let currentClientId = '';
let currentClientName = '';

function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.client-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function getSelectedClientIds() {
    const checkboxes = document.querySelectorAll('.client-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Show provider selection modal for bulk actions
function showProviderModal(action, status = '') {
    currentAction = action;
    currentStatus = status;

    let description = '';
    let selectedIds = [];

    if (action === 'queueSelected') {
        selectedIds = getSelectedClientIds();
        if (selectedIds.length === 0) {
            alert('Please select at least one client.');
            return;
        }
        description = `Queue emails for ${selectedIds.length} selected clients.`;
    } else if (action === 'queueAll') {
        const statusText = status === 'all' ? 'all eligible' : status;
        description = `Queue emails for all ${statusText} clients.`;
    }

    document.getElementById('modalDescription').textContent = description;
    document.getElementById('selectedProvider').value = '';
    hideProviderAlerts();

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('providerModal'));
    modal.show();
}

// Show provider selection modal for single client
function showSingleProviderModal(clientId, clientName) {
    currentAction = 'queueSingle';
    currentClientId = clientId;
    currentClientName = clientName;

    document.getElementById('modalDescription').textContent = `Queue email for ${clientName}.`;
    document.getElementById('selectedProvider').value = '';
    hideProviderAlerts();

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('providerModal'));
    modal.show();
}

// Execute the selected action
function executeAction() {
    const provider = document.getElementById('selectedProvider').value;

    if (!provider) {
        showProviderError('Please select an email provider.');
        return;
    }

    // Hide modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('providerModal'));
    modal.hide();

    // Execute based on current action
    if (currentAction === 'queueSelected') {
        queueSelected(provider);
    } else if (currentAction === 'queueAll') {
        queueAll(currentStatus, provider);
    } else if (currentAction === 'queueSingle') {
        queueSingle(currentClientId, provider);
    }
}

function queueSelected(provider) {
    const selectedIds = getSelectedClientIds();

    if (selectedIds.length === 0) {
        alert('Please select at least one client.');
        return;
    }

    if (confirm(`Queue emails for ${selectedIds.length} selected clients using ${provider} provider?`)) {
        const form = document.getElementById('queueBatchForm');
        const inputs = document.getElementById('queueBatchInputs');

        inputs.innerHTML = '';
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'client_ids[]';
            input.value = id;
            inputs.appendChild(input);
        });

        document.getElementById('batchProvider').value = provider;
        form.submit();
    }
}

function queueAll(status, provider) {
    const statusText = status === 'all' ? 'all eligible' : status;

    if (confirm(`Queue emails for all ${statusText} clients using ${provider} provider?`)) {
        document.getElementById('queueAllStatus').value = status;
        document.getElementById('allProvider').value = provider;
        document.getElementById('queueAllForm').submit();
    }
}

function queueSingle(clientId, provider) {
    if (confirm(`Queue email for ${currentClientName} using ${provider} provider?`)) {
        const form = document.getElementById('queueSingleForm');
        form.action = `/admin/emails/queue/${clientId}`;
        document.getElementById('singleProvider').value = provider;
        form.submit();
    }
}

function resetSelected() {
    const selectedIds = getSelectedClientIds();

    if (selectedIds.length === 0) {
        alert('Please select at least one client.');
        return;
    }

    if (confirm(`Reset email status for ${selectedIds.length} selected clients?`)) {
        const form = document.getElementById('resetBatchForm');
        const inputs = document.getElementById('resetBatchInputs');

        inputs.innerHTML = '';
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'client_ids[]';
            input.value = id;
            inputs.appendChild(input);
        });

        form.submit();
    }
}

// Provider testing functions
function testProvider(provider) {
    fetch('/admin/emails/providers/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ provider: provider })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${provider.toUpperCase()} provider test: ${data.status}`);
        } else {
            alert(`${provider.toUpperCase()} provider test failed: ${data.error}`);
        }
    })
    .catch(error => {
        alert(`Failed to test ${provider} provider: ${error.message}`);
    });
}

function testAllProviders() {
    const providers = @json(array_keys($providers));
    let results = [];

    Promise.all(providers.map(provider =>
        fetch('/admin/emails/providers/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ provider: provider })
        })
        .then(response => response.json())
        .then(data => ({ provider, data }))
    ))
    .then(allResults => {
        let message = 'Provider Test Results:\n\n';
        allResults.forEach(result => {
            const status = result.data.success ? result.data.status : 'failed';
            message += `${result.provider.toUpperCase()}: ${status}\n`;
        });
        alert(message);
    })
    .catch(error => {
        alert(`Failed to test providers: ${error.message}`);
    });
}

function refreshProviderStatus() {
    fetch('/admin/emails/providers/status')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to refresh provider status: ' + data.error);
        }
    })
    .catch(error => {
        alert('Failed to refresh provider status: ' + error.message);
    });
}

// Helper functions for modal alerts
function showProviderInfo(text) {
    document.getElementById('providerInfoText').textContent = text;
    document.getElementById('providerInfo').style.display = 'block';
    document.getElementById('providerError').style.display = 'none';
}

function showProviderError(text) {
    document.getElementById('providerErrorText').textContent = text;
    document.getElementById('providerError').style.display = 'block';
    document.getElementById('providerInfo').style.display = 'none';
}

function hideProviderAlerts() {
    document.getElementById('providerInfo').style.display = 'none';
    document.getElementById('providerError').style.display = 'none';
}

// Provider selection change handler
document.getElementById('selectedProvider').addEventListener('change', function() {
    const selectedProvider = this.value;

    if (selectedProvider) {
        const providers = @json($providers);
        const provider = providers[selectedProvider];

        if (provider && provider.available) {
            showProviderInfo(`${provider.display_name} is available and ready to send emails.`);
        } else if (provider) {
            showProviderError(`${provider.display_name} is not available: ${provider.error || 'Unknown error'}`);
        }
    } else {
        hideProviderAlerts();
    }
});

// Filter helper functions
function filterByValidEmails() {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('is_email_valid', '1');
    window.location.search = urlParams.toString();
}

function filterByInvalidEmails() {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('is_email_valid', '0');
    window.location.search = urlParams.toString();
}

function filterQueueEligible() {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('queue_eligible', '1');
    window.location.search = urlParams.toString();
}

// Show tooltip for disabled buttons and add cursor styles
document.addEventListener('DOMContentLoaded', function() {
    const disabledButtons = document.querySelectorAll('button[disabled]');
    disabledButtons.forEach(button => {
        if (button.title.includes('invalid email')) {
            button.style.cursor = 'not-allowed';
        }
    });

    // Add cursor pointer style for clickable cards
    const style = document.createElement('style');
    style.textContent = '.cursor-pointer { cursor: pointer; } .cursor-pointer:hover { transform: translateY(-2px); transition: transform 0.2s ease; }';
    document.head.appendChild(style);
});

// Auto-refresh page every 30 seconds to show updated statuses
setTimeout(() => {
    location.reload();
}, 30000);
</script>
@endsection
