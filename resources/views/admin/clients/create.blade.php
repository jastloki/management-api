@extends('admin.layouts.app')

@section('title', 'Add New Client')
@section('heading', 'Add New Client')

@section('page-actions')
<a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back to Clients
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-plus me-2 text-primary"></i>Client Information
                </h5>
                <p class="text-muted small mb-0">Fill in the details to add a new client</p>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.clients.store') }}">
                    @csrf

                    <div class="row">
                        <!-- Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-person me-1"></i>Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Enter client's full name"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Enter email address"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Validation -->
                        <div class="col-md-6 mb-3">
                            <label for="is_email_valid" class="form-label">
                                <i class="bi bi-shield-check me-1"></i>Email Validation
                            </label>
                            <select class="form-select @error('is_email_valid') is-invalid @enderror"
                                    id="is_email_valid"
                                    name="is_email_valid">
                                <option value="0" {{ old('is_email_valid', '0') == '0' ? 'selected' : '' }}>
                                    Invalid
                                </option>
                                <option value="1" {{ old('is_email_valid') == '1' ? 'selected' : '' }}>
                                    Valid
                                </option>
                            </select>
                            @error('is_email_valid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone me-1"></i>Phone Number
                            </label>
                            <input type="tel"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="Enter phone number">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Company -->
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">
                                <i class="bi bi-building me-1"></i>Company Name
                            </label>
                            <input type="text"
                                   class="form-control @error('company') is-invalid @enderror"
                                   id="company"
                                   name="company"
                                   value="{{ old('company') }}"
                                   placeholder="Enter company name">
                            @error('company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label">
                            <i class="bi bi-geo-alt me-1"></i>Address
                        </label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address"
                                  name="address"
                                  rows="3"
                                  placeholder="Enter full address">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status_id" class="form-label">
                                <i class="bi bi-toggle-on me-1"></i>Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status_id') is-invalid @enderror"
                                    id="status_id"
                                    name="status_id"
                                    required>
                                <option value="">Select Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Assigned User -->
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">
                                <i class="bi bi-person-check me-1"></i>Assigned User <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('user_id') is-invalid @enderror"
                                    id="user_id"
                                    name="user_id"
                                    required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', auth()->id()) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-light">
                            <i class="bi bi-x-lg me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Create Client
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-info-circle text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-title">Client Information Guidelines</h6>
                        <ul class="list-unstyled text-muted small mb-0">
                            <li><i class="bi bi-check text-success me-1"></i>Name and email are required fields</li>
                            <li><i class="bi bi-check text-success me-1"></i>Email addresses must be unique</li>
                            <li><i class="bi bi-check text-success me-1"></i>Phone numbers should include area codes</li>
                            <li><i class="bi bi-check text-success me-1"></i>Clients are set to active status by default</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
    }

    .card {
        transition: transform 0.2s ease-in-out;
    }

    .invalid-feedback {
        font-size: 0.875rem;
    }

    .text-danger {
        color: #dc3545 !important;
    }
</style>
@endsection

@section('scripts')
<script>
    // Form validation enhancement
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input[required], select[required]');

        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        });

        // Email validation
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('is-invalid');
            }
        });
    });
</script>
@endsection
