@extends('admin.layouts.app')

@section('title', 'Client Details')
@section('heading', 'Client Details')

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-2"></i>Edit Client
    </a>
    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Clients
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Main Client Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white border-0">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center me-4">
                        <span class="text-white font-weight-bold h4 mb-0">
                            {{ strtoupper(substr($client->name, 0, 2)) }}
                        </span>
                    </div>
                    <div>
                        <h4 class="card-title mb-1">{{ $client->name }}</h4>
                        <p class="text-muted mb-0">
                            <i class="bi bi-person-badge me-1"></i>Client ID: #{{ $client->id }}
                        </p>
                    </div>
                    <div class="ms-auto">
                        @if($client->status)
                            <span class="badge bg-success-soft text-success px-3 py-2 fs-6">
                                <i class="bi bi-check-circle me-1"></i>{{ $client->status->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary-soft text-secondary px-3 py-2 fs-6">
                                <i class="bi bi-question-circle me-1"></i>No Status
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">
                <h6 class="text-uppercase text-muted fw-bold mb-3">Contact Information</h6>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-primary-soft text-primary">
                                    <i class="bi bi-envelope"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Email Address</h6>
                                <a href="mailto:{{ $client->email }}" class="text-decoration-none d-block">
                                    {{ $client->email }}
                                </a>
                                @if($client->is_email_valid)
                                    <span class="badge bg-success-soft text-success mt-1">
                                        <i class="bi bi-check-circle me-1"></i>Valid
                                    </span>
                                @else
                                    <span class="badge bg-danger-soft text-danger mt-1">
                                        <i class="bi bi-x-circle me-1"></i>Invalid
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-success-soft text-success">
                                    <i class="bi bi-telephone"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Phone Number</h6>
                                @if($client->phone)
                                    <a href="tel:{{ $client->phone }}" class="text-decoration-none">
                                        {{ $client->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-info-soft text-info">
                                    <i class="bi bi-building"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Company</h6>
                                <p class="mb-0">{{ $client->company ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-warning-soft text-warning">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Address</h6>
                                @if($client->address)
                                    <p class="mb-0">{{ $client->address }}</p>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="text-uppercase text-muted fw-bold mb-3 mt-4">Assignment Information</h6>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-info-soft text-info">
                                    <i class="bi bi-person-check"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Assigned User</h6>
                                @if($client->user)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <span class="text-white small fw-bold">
                                                {{ strtoupper(substr($client->user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $client->user->name }}</div>
                                            <small class="text-muted">{{ $client->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-success-soft text-success">
                                    <i class="bi bi-toggle-on"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Current Status</h6>
                                @if($client->status)
                                    <span class="badge bg-success-soft text-success px-3 py-2">
                                        <i class="bi bi-check-circle me-1"></i>{{ $client->status->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary px-3 py-2">
                                        <i class="bi bi-question-circle me-1"></i>No Status
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card mt-4">
            @livewire('client-comments', ['client' => $client])
        </div>
    </div>

    <!-- Client Statistics & Actions -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <h6 class="card-title mb-0">
                    <i class="bi bi-lightning me-2 text-primary"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Edit Details
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="showEmailModal()">
                        <i class="bi bi-envelope me-2"></i>Send Email
                    </button>
                    @if($client->phone)
                        <a href="{{sprintf("%s?phone=%s&client=%s", env('CALL_LINK_PREFIX'),urlencode($client->phone), $client->email) }}" class="btn btn-outline-info">
                            <i class="bi bi-telephone me-2"></i>Call Client
                        </a>
                    @endif
                    <button class="btn btn-outline-danger" onclick="confirmDelete()">
                        <i class="bi bi-trash me-2"></i>Delete Client
                    </button>
                </div>
            </div>
        </div>

        <!-- Client Timeline -->
        <div class="card">
            <div class="card-header bg-white border-0">
                <h6 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Timeline
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Client Created</h6>
                            <p class="timeline-text text-muted small mb-0">
                                {{ $client->created_at->format('M d, Y \a\t g:i A') }}
                            </p>
                            <small class="text-muted">{{ $client->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    @if($client->updated_at->ne($client->created_at))
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Last Updated</h6>
                                <p class="timeline-text text-muted small mb-0">
                                    {{ $client->updated_at->format('M d, Y \a\t g:i A') }}
                                </p>
                                <small class="text-muted">{{ $client->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endif
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
                <p>Are you sure you want to delete <strong>{{ $client->name }}</strong>?</p>
                <p class="text-muted small">This action cannot be undone and will permanently remove all client data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" style="display: inline;">
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
    .avatar-lg {
        width: 72px;
        height: 72px;
        font-size: 1.5rem;
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }

    .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .bg-primary-soft {
        background-color: rgba(102, 126, 234, 0.1) !important;
    }

    .bg-success-soft {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    .bg-info-soft {
        background-color: rgba(23, 162, 184, 0.1) !important;
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

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -12px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #e9ecef;
    }

    .timeline-content {
        margin-left: 0.5rem;
    }

    .timeline-title {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
    }
</style>

<!-- Email Template Selection Modal -->
<div class="modal fade" id="emailTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-envelope me-2"></i>Send Email to {{ $client->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('send.html.email') }}" id="emailForm">
                @csrf
                <input type="hidden" name="ids[]" value="{{ $client->id }}">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Sending email to: <strong>{{ $client->email }}</strong>
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
                                $proxies = \App\Models\Proxy::active()->working()->orderBy('name')->get();
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
    function confirmDelete() {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Email template modal functions
    function showEmailModal() {
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
                        // Replace variables with client data for preview
                        let subject = data.subject;
                        let content = data.content;

                        // Simple replacement for preview
                        subject = subject.replace(/\{\{client_name\}\}/g, '{{ $client->name }}');
                        subject = subject.replace(/\{\{client_email\}\}/g, '{{ $client->email }}');
                        content = content.replace(/\{\{client_name\}\}/g, '{{ $client->name }}');
                        content = content.replace(/\{\{client_email\}\}/g, '{{ $client->email }}');

                        document.getElementById('previewSubject').textContent = subject;
                        document.getElementById('previewContent').innerHTML = content;
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
</script>
@endsection
