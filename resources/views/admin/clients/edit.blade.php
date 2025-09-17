@extends('admin.layouts.app')

@section('title', 'Edit Client')
@section('heading', 'Edit Client')

@section('page-actions')
<div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-outline-info">
        <i class="bi bi-eye me-2"></i>View Details
    </a>
    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Clients
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white border-0">
                <div class="d-flex align-items-center">
                    <div class="avatar-md bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                        <span class="text-white font-weight-bold">
                            {{ strtoupper(substr($client->name, 0, 2)) }}
                        </span>
                    </div>
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pencil me-2 text-primary"></i>Edit {{ $client->name }}
                        </h5>
                        <p class="text-muted small mb-0">Update client information</p>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.clients.update', $client) }}">
                    @csrf
                    @method('PUT')

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
                                   value="{{ old('name', $client->name) }}"
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
                                   value="{{ old('email', $client->email) }}"
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
                                <option value="0" {{ old('is_email_valid', $client->is_email_valid ? '1' : '0') == '0' ? 'selected' : '' }}>
                                    Invalid
                                </option>
                                <option value="1" {{ old('is_email_valid', $client->is_email_valid ? '1' : '0') == '1' ? 'selected' : '' }}>
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
                                   value="{{ old('phone', $client->phone) }}"
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
                                   value="{{ old('company', $client->company) }}"
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
                                  placeholder="Enter full address">{{ old('address', $client->address) }}</textarea>
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
                            @can('clients.status.update')
                            <select class="form-select @error('status_id') is-invalid @enderror"
                                    id="status_id"
                                    name="status_id"
                                    required>
                                <option value="">Select Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('status_id', $client->status_id) == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            @else
                            @php($st = $statuses->where('id', $client->status_id)->first())
                            {{$st->name ?? 'No status'}}
                            @endcan

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
                                    <option value="{{ $user->id }}" {{ old('user_id', $client->user_id) == $user->id ? 'selected' : '' }}>
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
                        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-light">
                            <i class="bi bi-x-lg me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Update Client
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Client Comments -->
        <div class="mt-4">
            @livewire('client-comments', ['client' => $client])
        </div>

        <!-- Client Info -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-info-circle text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-title">Client Record Information</h6>
                        <div class="row text-muted small">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Created:</strong> {{ $client->created_at->format('M d, Y \a\t g:i A') }}
                                </div>
                                <div>
                                    <strong>Client ID:</strong> #{{ $client->id }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Last Updated:</strong> {{ $client->updated_at->format('M d, Y \a\t g:i A') }}
                                </div>
                                <div class="mb-2">
                                    <strong>Status:</strong>
                                    @if($client->status)
                                        <span class="badge bg-success">{{ $client->status->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">No Status</span>
                                    @endif
                                </div>
                                <div>
                                    <strong>Assigned User:</strong>
                                    @if($client->user)
                                        <span class="badge bg-info">{{ $client->user->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">No User</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
