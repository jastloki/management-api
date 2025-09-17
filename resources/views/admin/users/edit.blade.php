@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('heading', 'Edit User: ' . $user->name)

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Users
    </a>
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info">
        <i class="bi bi-eye me-2"></i>View User
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pencil me-2"></i>User Information
                </h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $user->name) }}"
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
                               value="{{ old('email', $user->email) }}"
                               required
                               placeholder="Enter email address">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   placeholder="Leave blank to keep current password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Leave blank to keep the current password. Minimum 8 characters if changing.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Confirm new password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status Field -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $user->email_verified_at ? 'active' : 'inactive') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ old('status', $user->email_verified_at ? 'active' : 'inactive') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                        <div class="form-text">
                            Active users can log in and access the system. Inactive users cannot log in.
                        </div>
                        @error('status')
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
                                                       {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
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
                            <i class="bi bi-check-lg me-1"></i>Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- User Info Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-person me-2"></i>Current User Info
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-large mx-auto mb-2">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h6 class="mb-1">{{ $user->name }}</h6>
                    <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'secondary' }}">
                        {{ $user->email_verified_at ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="mb-0">{{ $user->roles->count() }}</h6>
                        <small class="text-muted">Roles</small>
                    </div>
                    <div class="col-6">
                        <h6 class="mb-0">{{ $user->getAllPermissions()->count() }}</h6>
                        <small class="text-muted">Permissions</small>
                    </div>
                </div>

                <hr>

                <h6>Account Details:</h6>
                <ul class="list-unstyled small">
                    <li><strong>ID:</strong> {{ $user->id }}</li>
                    <li><strong>Email:</strong> {{ $user->email }}</li>
                    <li><strong>Created:</strong> {{ $user->created_at->format('M d, Y') }}</li>
                    <li><strong>Last Updated:</strong> {{ $user->updated_at->diffForHumans() }}</li>
                    @if($user->email_verified_at)
                        <li><strong>Email Verified:</strong> {{ $user->email_verified_at->format('M d, Y') }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Current Roles Card -->
        @if($user->roles->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-shield-check me-2"></i>Current Roles
                </h6>
            </div>
            <div class="card-body">
                @foreach($user->roles as $role)
                    <div class="mb-2">
                        <span class="badge bg-info me-1">{{ ucfirst($role->name) }}</span>
                        @if($role->permissions->count() > 0)
                            <br>
                            <small class="text-muted">
                                {{ $role->permissions->count() }} permissions
                            </small>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Security Warning -->
        @if($user->id === auth()->id())
        <div class="card mt-3">
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> You are editing your own account.
                    Be careful when changing roles or status to avoid locking yourself out.
                </div>
            </div>
        </div>
        @endif

        <!-- Help Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Information
                </h6>
            </div>
            <div class="card-body">
                <h6>Password Requirements:</h6>
                <ul class="list-unstyled small">
                    <li><i class="bi bi-check-circle text-success me-1"></i>At least 8 characters</li>
                    <li><i class="bi bi-check-circle text-success me-1"></i>Mix of letters and numbers recommended</li>
                    <li><i class="bi bi-check-circle text-success me-1"></i>Special characters recommended</li>
                </ul>

                <hr>

                <h6>Account Status:</h6>
                <p class="text-muted small">
                    <strong>Active:</strong> User can log in and access the system.<br>
                    <strong>Inactive:</strong> User cannot log in but account data is preserved.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .avatar-large {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 20px;
    }
</style>
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

        if (password.length === 0) {
            passwordField.classList.remove('border-danger', 'border-warning', 'border-success');
            return;
        }

        const strength = checkPasswordStrength(password);
        passwordField.classList.remove('border-danger', 'border-warning', 'border-success');

        if (strength < 2) {
            passwordField.classList.add('border-danger');
        } else if (strength < 4) {
            passwordField.classList.add('border-warning');
        } else {
            passwordField.classList.add('border-success');
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

        // Only validate passwords if they are provided
        if (password.length > 0 || confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPasswordField.focus();
                return;
            }

            if (password.length > 0 && password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                passwordField.focus();
                return;
            }
        }
    });

    // Role selection helper
    const roleCheckboxes = document.querySelectorAll('input[name="roles[]"]');
    const currentUserId = {{ $user->id }};
    const authUserId = {{ auth()->id() }};

    // If editing own account and removing admin role, show warning
    roleCheckboxes.forEach(checkbox => {
        if (checkbox.value === 'admin' && currentUserId === authUserId) {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    if (!confirm('You are about to remove admin privileges from your own account. You may lose access to administrative functions. Continue?')) {
                        this.checked = true;
                    }
                }
            });
        }
    });

    // Status change warning for own account
    const statusSelect = document.getElementById('status');
    if (currentUserId === authUserId) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'inactive') {
                if (!confirm('You are about to deactivate your own account. You will not be able to log in after this change. Continue?')) {
                    this.value = 'active';
                }
            }
        });
    }
});
</script>
@endsection
