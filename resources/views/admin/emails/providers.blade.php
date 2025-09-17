@extends('admin.layouts.app')

@section('title', 'Email Provider Management')
@section('heading', 'Email Provider Management')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.emails.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-left me-2"></i>Back to Queue
    </a>
    <button type="button" class="btn btn-success" onclick="testAllProviders()">
        <i class="bi bi-shield-check me-2"></i>Test All Providers
    </button>
    <button type="button" class="btn btn-info" onclick="refreshProviderStatus()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh Status
    </button>
</div>
@endsection

@section('content')
<!-- Provider Overview Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-start border-primary border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Providers</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ count($providers) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-gear fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-start border-success border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">Available</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ collect($providers)->where('available', true)->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-start border-warning border-4 shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">Default Provider</div>
                        <div class="h6 mb-0 fw-bold text-gray-800">{{ ucfirst(config('mail.default_provider', 'smtp')) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-star fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Provider Configuration Cards -->
<div class="row">
    @foreach($providers as $providerKey => $provider)
    <div class="col-lg-6 col-xl-4 mb-4">
        <div class="card shadow h-100 border-start border-{{ $provider['available'] ? 'success' : 'danger' }} border-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-{{ $provider['available'] ? 'success' : 'danger' }}">
                    {{ $provider['display_name'] }}
                </h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><button class="dropdown-item" onclick="testProvider('{{ $providerKey }}')">
                            <i class="bi bi-shield-check me-2"></i>Test Connection
                        </button></li>
                        <li><button class="dropdown-item" onclick="viewProviderConfig('{{ $providerKey }}')">
                            <i class="bi bi-eye me-2"></i>View Configuration
                        </button></li>
                        @if($provider['available'])
                        <li><hr class="dropdown-divider"></li>
                        <li><button class="dropdown-item" onclick="setDefaultProvider('{{ $providerKey }}')">
                            <i class="bi bi-star me-2"></i>Set as Default
                        </button></li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="card-body">
                <!-- Status Badge -->
                <div class="mb-3">
                    <span class="badge bg-{{ $provider['available'] ? 'success' : 'danger' }} fs-6">
                        @if($provider['available'])
                            <i class="bi bi-check-circle me-1"></i>Available
                        @else
                            <i class="bi bi-x-circle me-1"></i>Unavailable
                        @endif
                    </span>
                    @if(config('mail.default_provider') === $providerKey)
                        <span class="badge bg-warning fs-6 ms-2">
                            <i class="bi bi-star me-1"></i>Default
                        </span>
                    @endif
                </div>

                <!-- Provider Type -->
                <div class="mb-2">
                    <small class="text-muted">Provider Type:</small>
                    <div class="fw-bold">{{ ucfirst($providerKey) }}</div>
                </div>

                <!-- Configuration Status -->
                <div class="mb-2">
                    <small class="text-muted">Configuration:</small>
                    <div class="fw-bold text-{{ $provider['available'] ? 'success' : 'danger' }}">
                        {{ $provider['available'] ? 'Configured' : 'Missing Configuration' }}
                    </div>
                </div>

                @if(isset($provider['error']))
                <!-- Error Message -->
                <div class="alert alert-danger mt-3 py-2">
                    <small><i class="bi bi-exclamation-triangle me-1"></i>{{ $provider['error'] }}</small>
                </div>
                @endif

                <!-- Configuration Hints -->
                @switch($providerKey)
                    @case('smtp')
                        <div class="mt-3">
                            <small class="text-muted">Required Environment Variables:</small>
                            <ul class="list-unstyled mt-1">
                                <li><code class="text-xs">MAIL_HOST</code></li>
                                <li><code class="text-xs">MAIL_PORT</code></li>
                                <li><code class="text-xs">MAIL_USERNAME</code></li>
                                <li><code class="text-xs">MAIL_PASSWORD</code></li>
                            </ul>
                        </div>
                        @break

                    @case('sendgrid')
                        <div class="mt-3">
                            <small class="text-muted">Required Environment Variables:</small>
                            <ul class="list-unstyled mt-1">
                                <li><code class="text-xs">SENDGRID_API_KEY</code></li>
                                <li><code class="text-xs">MAIL_FROM_ADDRESS</code></li>
                            </ul>
                        </div>
                        @break

                    @case('mailgun')
                        <div class="mt-3">
                            <small class="text-muted">Required Environment Variables:</small>
                            <ul class="list-unstyled mt-1">
                                <li><code class="text-xs">MAILGUN_SECRET</code></li>
                                <li><code class="text-xs">MAILGUN_DOMAIN</code></li>
                                <li><code class="text-xs">MAIL_FROM_ADDRESS</code></li>
                            </ul>
                        </div>
                        @break
                @endswitch
            </div>

            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-sm btn-outline-info" onclick="testProvider('{{ $providerKey }}')">
                        <i class="bi bi-shield-check me-1"></i>Test
                    </button>
                    @if($provider['available'])
                        <span class="text-success">
                            <i class="bi bi-check-circle me-1"></i>Ready to Send
                        </span>
                    @else
                        <span class="text-danger">
                            <i class="bi bi-x-circle me-1"></i>Needs Configuration
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Configuration Instructions -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Configuration Instructions</h6>
            </div>
            <div class="card-body">
                <div class="accordion" id="configurationAccordion">
                    <!-- SMTP Configuration -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="smtpHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#smtpCollapse">
                                <i class="bi bi-envelope me-2"></i>SMTP Configuration
                            </button>
                        </h2>
                        <div id="smtpCollapse" class="accordion-collapse collapse" data-bs-parent="#configurationAccordion">
                            <div class="accordion-body">
                                <p>Configure SMTP settings in your <code>.env</code> file:</p>
                                <pre class="bg-light p-3 rounded"><code>MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="${APP_NAME}"</code></pre>
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Common SMTP Providers:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Gmail:</strong> smtp.gmail.com:587 (Use App Password)</li>
                                        <li><strong>Outlook:</strong> smtp-mail.outlook.com:587</li>
                                        <li><strong>Yahoo:</strong> smtp.mail.yahoo.com:587</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SendGrid Configuration -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="sendgridHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sendgridCollapse">
                                <i class="bi bi-cloud me-2"></i>SendGrid Configuration
                            </button>
                        </h2>
                        <div id="sendgridCollapse" class="accordion-collapse collapse" data-bs-parent="#configurationAccordion">
                            <div class="accordion-body">
                                <p>Configure SendGrid API settings in your <code>.env</code> file:</p>
                                <pre class="bg-light p-3 rounded"><code>SENDGRID_API_KEY=your-sendgrid-api-key
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"</code></pre>
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Setup Steps:</strong>
                                    <ol class="mb-0">
                                        <li>Create a SendGrid account at <a href="https://sendgrid.com" target="_blank">sendgrid.com</a></li>
                                        <li>Generate an API key with "Mail Send" permissions</li>
                                        <li>Verify your sender identity (domain or single sender)</li>
                                        <li>Add the API key to your environment variables</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mailgun Configuration -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="mailgunHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mailgunCollapse">
                                <i class="bi bi-rocket me-2"></i>Mailgun Configuration
                            </button>
                        </h2>
                        <div id="mailgunCollapse" class="accordion-collapse collapse" data-bs-parent="#configurationAccordion">
                            <div class="accordion-body">
                                <p>Configure Mailgun API settings in your <code>.env</code> file:</p>
                                <pre class="bg-light p-3 rounded"><code>MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_DOMAIN=yourdomain.com
MAILGUN_REGION=us
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"</code></pre>
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Setup Steps:</strong>
                                    <ol class="mb-0">
                                        <li>Create a Mailgun account at <a href="https://mailgun.com" target="_blank">mailgun.com</a></li>
                                        <li>Add and verify your domain</li>
                                        <li>Get your API key from the dashboard</li>
                                        <li>Set the correct region (us or eu)</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Provider Configuration Modal -->
<div class="modal fade" id="providerConfigModal" tabindex="-1" aria-labelledby="providerConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="providerConfigModalLabel">Provider Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="providerConfigContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Test individual provider
function testProvider(provider) {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;

    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Testing...';
    btn.disabled = true;

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
            showToast(`${provider.toUpperCase()} Test Result`, `Status: ${data.status}`, data.status === 'connected' ? 'success' : 'warning');
        } else {
            showToast(`${provider.toUpperCase()} Test Failed`, data.error, 'error');
        }
    })
    .catch(error => {
        showToast('Test Failed', `Failed to test ${provider} provider: ${error.message}`, 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Test all providers
function testAllProviders() {
    const providers = @json(array_keys($providers));
    let results = [];
    let completed = 0;

    showToast('Testing Providers', 'Testing all available providers...', 'info');

    providers.forEach(provider => {
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
            const status = data.success ? data.status : 'failed';
            results.push({ provider, status, error: data.error });
        })
        .catch(error => {
            results.push({ provider, status: 'error', error: error.message });
        })
        .finally(() => {
            completed++;
            if (completed === providers.length) {
                displayTestResults(results);
            }
        });
    });
}

// Display test results
function displayTestResults(results) {
    let message = 'Provider Test Results:\n\n';
    let successCount = 0;

    results.forEach(result => {
        const status = result.status === 'connected' ? '✅ Connected' :
                      result.status === 'available' ? '⚠️ Available' :
                      result.status === 'failed' ? '❌ Failed' : '❌ Error';
        message += `${result.provider.toUpperCase()}: ${status}\n`;
        if (result.status === 'connected' || result.status === 'available') {
            successCount++;
        }
    });

    const resultType = successCount > 0 ? 'success' : 'warning';
    showToast('Test Results Complete', `${successCount}/${results.length} providers are working`, resultType);

    // Show detailed results in console for debugging
    console.table(results);
}

// Refresh provider status
function refreshProviderStatus() {
    fetch('/admin/emails/providers/status')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Status Refreshed', 'Provider status updated successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Refresh Failed', 'Failed to refresh provider status: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showToast('Refresh Failed', 'Failed to refresh provider status: ' + error.message, 'error');
    });
}

// View provider configuration
function viewProviderConfig(provider) {
    const providers = @json($providers);
    const config = providers[provider];

    let configHtml = `
        <h6 class="text-primary">${config.display_name} Configuration</h6>
        <div class="mt-3">
            <strong>Status:</strong>
            <span class="badge bg-${config.available ? 'success' : 'danger'}">
                ${config.available ? 'Available' : 'Unavailable'}
            </span>
        </div>
    `;

    if (config.error) {
        configHtml += `
            <div class="alert alert-danger mt-3">
                <strong>Error:</strong> ${config.error}
            </div>
        `;
    }

    configHtml += `
        <div class="mt-3">
            <strong>Provider Class:</strong> <code>${config.class}</code>
        </div>
    `;

    document.getElementById('providerConfigContent').innerHTML = configHtml;

    const modal = new bootstrap.Modal(document.getElementById('providerConfigModal'));
    modal.show();
}

// Set default provider
function setDefaultProvider(provider) {
    if (confirm(`Set ${provider.toUpperCase()} as the default email provider?`)) {
        // This would require a backend endpoint to update the configuration
        showToast('Feature Coming Soon', 'Setting default provider will be available in a future update', 'info');
    }
}

// Toast notification function
function showToast(title, message, type = 'info') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();

    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' :
                   type === 'error' ? 'bg-danger' :
                   type === 'warning' ? 'bg-warning' : 'bg-info';

    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${bgClass} text-white border-0">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    // Remove toast after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Create toast container if it doesn't exist
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
