@extends('admin.layouts.app')

@section('title', 'Edit Proxy')
@section('heading', 'Edit Proxy')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('admin.proxies.show', $proxy) }}" class="btn btn-outline-info">
        <i class="bi bi-eye me-2"></i>View Details
    </a>
    <a href="{{ route('admin.proxies.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Proxies
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pencil me-2 text-primary"></i>Edit Proxy: {{ $proxy->name }}
                </h5>
                <p class="text-muted small mb-0">Update proxy server information</p>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.proxies.update', $proxy) }}" method="POST" id="proxyForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-tag me-1"></i>Proxy Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $proxy->name) }}"
                                   placeholder="e.g., US East Coast Proxy" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">
                                <i class="bi bi-gear me-1"></i>Proxy Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select proxy type</option>
                                @foreach(\App\Models\Proxy::getTypes() as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $proxy->type) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="url" class="form-label">
                                <i class="bi bi-globe me-1"></i>URL/IP Address <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('url') is-invalid @enderror"
                                   id="url" name="url" value="{{ old('url', $proxy->url) }}"
                                   placeholder="e.g., proxy.example.com or 192.168.1.100" required>
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="port" class="form-label">
                                <i class="bi bi-ethernet me-1"></i>Port
                            </label>
                            <input type="number" class="form-control @error('port') is-invalid @enderror"
                                   id="port" name="port" value="{{ old('port', $proxy->port) }}"
                                   min="1" max="65535" placeholder="e.g., 8080">
                            @error('port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Authentication Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-shield-lock me-2"></i>Authentication (Optional)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        <i class="bi bi-person me-1"></i>Username
                                    </label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                           id="username" name="username" value="{{ old('username', $proxy->username) }}"
                                           placeholder="Enter username">
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-key me-1"></i>Password
                                    </label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" placeholder="Enter password (leave blank to keep current)">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($proxy->password)
                                        <small class="text-muted">Password is currently set. Leave blank to keep unchanged.</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <!--<div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-geo-alt me-2"></i>Location Information (Optional)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">
                                        <i class="bi bi-flag me-1"></i>Country
                                    </label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror"
                                           id="country" name="country" value="{{ old('country', $proxy->country) }}"
                                           placeholder="e.g., United States">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">
                                        <i class="bi bi-building me-1"></i>City
                                    </label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                           id="city" name="city" value="{{ old('city', $proxy->city) }}"
                                           placeholder="e.g., New York">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>-->

                    <!-- Extra Fields Section -->


                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="bi bi-journal-text me-1"></i>Description
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Optional description or notes about this proxy">{{ old('description', $proxy->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $proxy->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-toggle-on me-1"></i>Active (proxy is ready to use)
                            </label>
                        </div>
                    </div>

                    <!-- Current Status Information -->
                    @if($proxy->last_tested_at || $proxy->status !== 'untested')
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-info-circle me-2"></i>Current Status
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Status:</strong>
                                        <span class="badge {{ $proxy->getStatusBadgeClass() }} ms-2">
                                            {{ ucfirst($proxy->status) }}
                                        </span>
                                    </div>
                                    @if($proxy->response_time)
                                        <div class="col-md-4">
                                            <strong>Response Time:</strong>
                                            <span class="text-muted ms-2">{{ $proxy->response_time }}ms</span>
                                        </div>
                                    @endif
                                    @if($proxy->last_tested_at)
                                        <div class="col-md-4">
                                            <strong>Last Tested:</strong>
                                            <span class="text-muted ms-2">{{ $proxy->last_tested_at->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-outline-info" onclick="testProxy({{ $proxy->id }})">
                                <i class="bi bi-speedometer2 me-2"></i>Test Connection
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.proxies.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Update Proxy
                            </button>
                        </div>
                    </div>
                </form>
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

<style>
    .card-header {
        border-bottom: 1px solid #e9ecef;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .extra-field-row {
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
    }

    .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .badge {
        font-size: 0.75em;
    }
</style>
@endsection

@section('scripts')
<script>
    // let extraFieldIndex = {{ $proxy->extra_fields ? count($proxy->extra_fields) : 0 }};

    // // Add extra field functionality
    // document.getElementById('addExtraField').addEventListener('click', function() {
    //     const container = document.getElementById('extraFieldsContainer');
    //     extraFieldIndex++;

        // const fieldHtml = `
        //     <div class="extra-field-row" id="extraField_${extraFieldIndex}">
        //         <div class="row align-items-end">
        //             <div class="col-md-4">
        //                 <label class="form-label">Field Name</label>
        //                 <input type="text" class="form-control"
        //                        name="extra_field_keys[]"
        //                        placeholder="e.g., provider, speed">
        //             </div>
        //             <div class="col-md-6">
        //                 <label class="form-label">Field Value</label>
        //                 <input type="text" class="form-control"
        //                        name="extra_field_values[]"
        //                        placeholder="Enter value">
        //             </div>
        //             <div class="col-md-2">
        //                 <button type="button" class="btn btn-outline-danger btn-sm w-100"
        //                         onclick="removeExtraField('${extraFieldIndex}')">
        //                     <i class="bi bi-trash"></i>
        //                 </button>
        //             </div>
        //         </div>
        //     </div>
        // `;

    //     container.insertAdjacentHTML('beforeend', fieldHtml);
    // });

    // // Remove extra field
    // function removeExtraField(index) {
    //     const field = document.getElementById(`extraField_${index}`);
    //     if (field) {
    //         field.remove();
    //     }
    // }

    // // Form submission handling
    // document.getElementById('proxyForm').addEventListener('submit', function(e) {
    //     // Process extra fields before submission
    //     const keys = document.querySelectorAll('input[name="extra_field_keys[]"]');
    //     const values = document.querySelectorAll('input[name="extra_field_values[]"]');

    //     // Remove the original arrays
    //     keys.forEach(input => input.remove());
    //     values.forEach(input => input.remove());

    //     // Create new format for extra fields
    //     keys.forEach((keyInput, index) => {
    //         const key = keyInput.value.trim();
    //         const value = values[index].value.trim();

    //         if (key && value) {
    //             // Create hidden input for each extra field
    //             const hiddenInput = document.createElement('input');
    //             hiddenInput.type = 'hidden';
    //             hiddenInput.name = `extra_fields[${key}]`;
    //             hiddenInput.value = value;
    //             this.appendChild(hiddenInput);
    //         }
    //     });
    // });

    // // Auto-fill port based on proxy type
    // document.getElementById('type').addEventListener('change', function() {
    //     const port = document.getElementById('port');
    //     const defaultPorts = {
    //         'http': '8080',
    //         'https': '443',
    //         'socks4': '1080',
    //         'socks5': '1080'
    //     };

    //     if (defaultPorts[this.value] && !port.value) {
    //         port.value = defaultPorts[this.value];
    //     }
    // });

    // // URL validation
    // document.getElementById('url').addEventListener('blur', function() {
    //     const url = this.value.trim();

    //     // Remove protocol if present
    //     if (url.startsWith('http://') || url.startsWith('https://')) {
    //         this.value = url.replace(/^https?:\/\//, '');
    //     }

    //     // Basic IP/domain validation feedback
    //     const isValidIP = /^(\d{1,3}\.){3}\d{1,3}$/.test(this.value);
    //     const isValidDomain = /^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(this.value);

    //     if (this.value && !isValidIP && !isValidDomain) {
    //         this.classList.add('is-invalid');
    //     } else {
    //         this.classList.remove('is-invalid');
    //     }
    // });

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
</script>
@endsection
