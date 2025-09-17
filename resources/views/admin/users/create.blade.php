@extends('admin.layouts.app')

@section('title', 'Create User')
@section('heading', 'Create New User')

@section('page-actions')
<a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back to Users
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-plus me-2"></i>User Information
                </h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               placeholder="Enter full name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               placeholder="Enter email address">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   required
                                   placeholder="Enter password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   placeholder="Confirm password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Roles Field -->
                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div class="border rounded p-3 bg-light">
                            @if($roles->count() > 0)
                                <div class="row">
                                    @foreach($roles as $role)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="role_{{ $role->name }}"
                                                       name="roles[]"
                                                       value="{{ $role->name }}"
                                                       {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role_{{ $role->name }}">
                                                    <strong>{{ ucfirst($role->name) }}</strong>
                                                    @if($role->permissions->count() > 0)
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $role->permissions->count() }} permission(s)
                                                        </small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No roles available. Please create roles first.</p>
                            @endif
                        </div>
                        @error('roles')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        @error('roles.*')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Help Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Information
                </h6>
            </div>
            <div class="card-body">
                <h6>Password Requirements:</h6>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle text-success me-1"></i>At least 8 characters</li>
                    <li><i class="bi bi-check-circle text-success me-1"></i>Mix of letters and numbers recommended</li>
                    <li><i class="bi bi-check-circle text-success me-1"></i>Special characters recommended</li>
                </ul>

                <hr>

                <h6>User Roles:</h6>
                <p class="text-muted small">
                    Roles determine what actions a user can perform in the system.
                    You can assign multiple roles to a user.
                </p>

                @if($roles->count() > 0)
                    <div class="mt-3">
                        @foreach($roles as $role)
                            <div class="mb-2">
                                <strong>{{ ucfirst($role->name) }}</strong>
                                @if($role->permissions->count() > 0)
                                    <br>
                                    <small class="text-muted">
                                        Permissions: {{ $role->permissions->pluck('name')->take(3)->implode(', ') }}
                                        @if($role->permissions->count() > 3)
                                            and {{ $role->permissions->count() - 3 }} more...
                                        @endif
                                    </small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Account Status Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-shield-check me-2"></i>Account Status
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    New users will be created with an <strong>active</strong> status and
                    their email will be automatically verified.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    function setupPasswordToggle(passwordFieldId, toggleButtonId) {
        const passwordField = document.getElementById(passwordFieldId);
        const toggleButton = document.getElementById(toggleButtonId);
        const icon = toggleButton.querySelector('i');

        toggleButton.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                passwordField.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    }

    setupPasswordToggle('password', 'togglePassword');
    setupPasswordToggle('password_confirmation', 'togglePasswordConfirm');

    // Password strength indicator
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');

    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        return strength;
    }

    function updatePasswordStrength() {
        const password = passwordField.value;
        const strength = checkPasswordStrength(password);

        passwordField.classList.remove('border-danger', 'border-warning', 'border-success');

        if (password.length > 0) {
            if (strength < 2) {
                passwordField.classList.add('border-danger');
            } else if (strength < 4) {
                passwordField.classList.add('border-warning');
            } else {
                passwordField.classList.add('border-success');
            }
        }
    }

    function checkPasswordMatch() {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;

        confirmPasswordField.classList.remove('border-danger', 'border-success');

        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                confirmPasswordField.classList.add('border-success');
            } else {
                confirmPasswordField.classList.add('border-danger');
            }
        }
    }

    passwordField.addEventListener('input', updatePasswordStrength);
    confirmPasswordField.addEventListener('input', checkPasswordMatch);

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            confirmPasswordField.focus();
        }
    });

    // Role selection helper
    const roleCheckboxes = document.querySelectorAll('input[name="roles[]"]');

    // If admin role is selected, show warning
    roleCheckboxes.forEach(checkbox => {
        if (checkbox.value === 'admin') {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    if (!confirm('You are about to assign admin privileges to this user. Admin users have full access to the system. Continue?')) {
                        this.checked = false;
                    }
                }
            });
        }
    });
});
</script>
@endsection
