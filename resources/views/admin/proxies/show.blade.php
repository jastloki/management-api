@extends('admin.layouts.app')

@section('title', 'Proxy Details')
@section('heading', 'Proxy Details')

@section('page-actions')
<div class="btn-group" role="group">
    <button type="button" class="btn btn-outline-info" onclick="testProxy({{ $proxy->id }})">
        <i class="bi bi-speedometer2 me-2"></i>Test Connection
    </button>
    <a href="{{ route('admin.proxies.edit', $proxy) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil me-2"></i>Edit
    </a>
    <button type="button" class="btn btn-outline-secondary" onclick="duplicateProxy({{ $proxy->id }})">
        <i class="bi bi-files me-2"></i>Duplicate
    </button>
    <a href="{{ route('admin.proxies.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to List
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Proxy Name</label>
                        <div class="fw-bold">{{ $proxy->name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Type</label>
                        <div>
                            <span class="badge {{ $proxy->getTypeBadgeClass() }} fs-6">
                                {{ strtoupper($proxy->type) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label text-muted">URL/IP Address</label>
                        <div class="fw-bold">{{ $proxy->url }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Port</label>
                        <div class="fw-bold">
                            @if($proxy->port)
                                {{ $proxy->port }}
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($proxy->description)
                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <div>{{ $proxy->description }}</div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            @if($proxy->is_active)
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            @else
                                <span class="badge bg-secondary fs-6">
                                    <i class="bi bi-x-circle me-1"></i>Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Full URL</label>
                        <div class="font-monospace bg-light p-2 rounded border">
                            {{ $proxy->full_url }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication Information -->
        @if($proxy->username || $proxy->password)
            <div class="card mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-lock me-2 text-primary"></i>Authentication
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Username</label>
                            <div class="fw-bold">
                                @if($proxy->username)
                                    {{ $proxy->username }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Password</label>
                            <div>
                                @if($proxy->password)
                                    <span class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>Set
                                    </span>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Location Information -->
        @if($proxy->country || $proxy->city)
            <div class="card mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>Location
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Country</label>
                            <div class="fw-bold">
                                @if($proxy->country)
                                    <i class="bi bi-flag me-2"></i>{{ $proxy->country }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">City</label>
                            <div class="fw-bold">
                                @if($proxy->city)
                                    <i class="bi bi-building me-2"></i>{{ $proxy->city }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Extra Fields -->
        @if($proxy->extra_fields && count($proxy->extra_fields) > 0)
            <div class="card mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-plus-square me-2 text-primary"></i>Extra Fields
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($proxy->extra_fields as $key => $value)
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                                <div class="fw-bold">{{ $value }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Status Card -->
        <!--<div class="card mb-4">-->
            <!--<div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-activity me-2 text-primary"></i>Connection Status
                </h5>
            </div>
            <div class="card-body">-->
                <!--<div class="text-center mb-3">
                    <div class="mb-2">
                        <span class="badge {{ $proxy->getStatusBadgeClass() }} fs-5">
                            {{ ucfirst($proxy->status) }}
                        </span>
                    </div>
                    @if($proxy->response_time)
                        <div class="text-muted">
                            Response Time: <strong>{{ $proxy->response_time }}ms</strong>
                        </div>
                    @endif
                </div>-->

                <!--@if($proxy->last_tested_at)
                    <div class="mb-3">
                        <label class="form-label text-muted">Last Tested</label>
                        <div>
                            <i class="bi bi-clock me-1"></i>{{ $proxy->last_tested_at->format('M d, Y g:i A') }}
                        </div>
                        <small class="text-muted">{{ $proxy->last_tested_at->diffForHumans() }}</small>
                    </div>
                @endif-->

                <!--@if($proxy->needsTesting())
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This proxy needs testing
                    </div>
                @endif-->

                <!--<button type="button" class="btn btn-primary w-100" onclick="testProxy({{ $proxy->id }})">
                    <i class="bi bi-speedometer2 me-2"></i>Test Connection
                </button>-->
            <!--</div>-->
        <!--</div>-->

        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>Quick Stats
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h5 mb-1">{{ $proxy->created_at->format('M d') }}</div>
                            <small class="text-muted">Created</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h5 mb-1">{{ $proxy->updated_at->diffForHumans() }}</div>
                        <small class="text-muted">Last Updated</small>
                    </div>
                </div>

                @if($proxy->response_time)
                    <hr>
                    <div class="text-center">
                        <div class="progress mb-2">
                            @php
                                $speedClass = 'bg-success';
                                $speedWidth = 100;
                                if($proxy->response_time > 1000) {
                                    $speedClass = 'bg-danger';
                                    $speedWidth = 25;
                                } elseif($proxy->response_time > 500) {
                                    $speedClass = 'bg-warning';
                                    $speedWidth = 50;
                                } elseif($proxy->response_time > 200) {
                                    $speedClass = 'bg-info';
                                    $speedWidth = 75;
                                }
                            @endphp
                            <div class="progress-bar {{ $speedClass }}" role="progressbar" style="width: {{ $speedWidth }}%"></div>
                        </div>
                        <small class="text-muted">
                            @if($proxy->response_time < 200)
                                Excellent Speed
                            @elseif($proxy->response_time < 500)
                                Good Speed
                            @elseif($proxy->response_time < 1000)
                                Average Speed
                            @else
                                Slow Speed
                            @endif
                        </small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions Card -->
        <div class="card">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2 text-primary"></i>Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.proxies.edit', $proxy) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Edit Proxy
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="duplicateProxy({{ $proxy->id }})">
                        <i class="bi bi-files me-2"></i>Duplicate Proxy
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="toggleStatus({{ $proxy->id }})">
                        <i class="bi bi-toggle-{{ $proxy->is_active ? 'off' : 'on' }} me-2"></i>
                        {{ $proxy->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <hr>
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $proxy->id }})">
                        <i class="bi bi-trash me-2"></i>Delete Proxy
                    </button>
                </div>
            </div>
        </div>
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
                <p>Are you sure you want to delete the proxy <strong>{{ $proxy->name }}</strong>?</p>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.proxies.destroy', $proxy) }}" method="POST" style="display: inline;">
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

<style>
    .card-header {
        border-bottom: 1px solid #e9ecef;
    }

    .form-label {
        font-weight: 500;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .badge {
        font-size: 0.875em;
    }

    .progress {
        height: 8px;
        background-color: #e9ecef;
    }

    .font-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.875rem;
    }

    .border-end {
        border-right: 1px solid #dee2e6 !important;
    }
</style>
@endsection

@section('scripts')
<script>
    // Test proxy function
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

    // Toggle status
    function toggleStatus(proxyId) {
        if (confirm('Are you sure you want to change the proxy status?')) {
            const form = document.getElementById('toggleStatusForm');
            form.action = `{{ route('admin.proxies.index') }}/${proxyId}/toggle-status`;
            form.submit();
        }
    }

    // Duplicate proxy
    function duplicateProxy(proxyId) {
        if (confirm('Are you sure you want to duplicate this proxy?')) {
            const form = document.getElementById('duplicateForm');
            form.action = `{{ route('admin.proxies.index') }}/${proxyId}/duplicate`;
            form.submit();
        }
    }

    // Delete confirmation
    function confirmDelete(proxyId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
</script>
@endsection
