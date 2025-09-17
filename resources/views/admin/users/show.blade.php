@extends('admin.layouts.app')

@section('title', 'View User')
@section('heading', 'User Details: ' . $user->name)

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Users
    </a>
    @permission('users.edit')
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-2"></i>Edit User
    </a>
    @endpermission
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- User Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-xl mx-auto mb-3">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>

                <div class="mb-3">
                    <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'secondary' }} fs-6">
                        <i class="bi bi-{{ $user->email_verified_at ? 'check-circle' : 'x-circle' }} me-1"></i>
                        {{ $user->email_verified_at ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="row text-center">
                    <div class="col-4">
                        <h5 class="mb-0 text-primary">{{ $user->roles->count() }}</h5>
                        <small class="text-muted">Roles</small>
                    </div>
                    <div class="col-4">
                        <h5 class="mb-0 text-success">{{ $user->getAllPermissions()->count() }}</h5>
                        <small class="text-muted">Permissions</small>
                    </div>
                    <div class="col-4">
                        <h5 class="mb-0 text-info">{{ $user->created_at->diffInDays() }}</h5>
                        <small class="text-muted">Days Old</small>
                    </div>
                </div>

                <hr>

                @permission('users.edit')
                <div class="d-grid gap-2">
                    <button type="button"
                            class="btn btn-outline-{{ $user->email_verified_at ? 'warning' : 'success' }} status-toggle"
                            data-user-id="{{ $user->id }}"
                            data-current-status="{{ $user->email_verified_at ? 'active' : 'inactive' }}">
                        <i class="bi bi-{{ $user->email_verified_at ? 'pause' : 'play' }} me-1"></i>
                        {{ $user->email_verified_at ? 'Deactivate' : 'Activate' }} User
                    </button>
                </div>
                @endpermission
            </div>
        </div>

        <!-- Account Information Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Account Information
                </h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">User ID:</dt>
                    <dd class="col-sm-7">{{ $user->id }}</dd>

                    <dt class="col-sm-5">Email:</dt>
                    <dd class="col-sm-7">
                        {{ $user->email }}
                        @if($user->email_verified_at)
                            <i class="bi bi-patch-check-fill text-success ms-1" title="Verified"></i>
                        @endif
                    </dd>

                    <dt class="col-sm-5">Status:</dt>
                    <dd class="col-sm-7">
                        <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'secondary' }}">
                            {{ $user->email_verified_at ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>

                    <dt class="col-sm-5">Created:</dt>
                    <dd class="col-sm-7">
                        {{ $user->created_at->format('M d, Y') }}
                        <br>
                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                    </dd>

                    <dt class="col-sm-5">Last Updated:</dt>
                    <dd class="col-sm-7">
                        {{ $user->updated_at->format('M d, Y') }}
                        <br>
                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                    </dd>

                    @if($user->email_verified_at)
                    <dt class="col-sm-5">Email Verified:</dt>
                    <dd class="col-sm-7">
                        {{ $user->email_verified_at->format('M d, Y') }}
                        <br>
                        <small class="text-muted">{{ $user->email_verified_at->diffForHumans() }}</small>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Roles Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check me-2"></i>Assigned Roles
                    <span class="badge bg-primary ms-2">{{ $user->roles->count() }}</span>
                </h5>
            </div>
            <div class="card-body">
                @if($user->roles->count() > 0)
                    <div class="row">
                        @foreach($user->roles as $role)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title mb-1">
                                                    <span class="badge bg-info">{{ ucfirst($role->name) }}</span>
                                                </h6>
                                                <p class="text-muted small mb-2">
                                                    {{ $role->permissions->count() }} permission(s)
                                                </p>
                                            </div>
                                        </div>

                                        @if($role->permissions->count() > 0)
                                            <div class="mt-2">
                                                <small class="text-muted">Key permissions:</small>
                                                <div class="mt-1">
                                                    @foreach($role->permissions->take(4) as $permission)
                                                        <span class="badge bg-light text-dark me-1 mb-1">{{ $permission->name }}</span>
                                                    @endforeach
                                                    @if($role->permissions->count() > 4)
                                                        <span class="badge bg-secondary">+{{ $role->permissions->count() - 4 }} more</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-shield-x display-4 text-muted"></i>
                        <h6 class="mt-3 text-muted">No Roles Assigned</h6>
                        <p class="text-muted">This user has no roles assigned.</p>
                        @permission('users.edit')
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Assign Roles
                        </a>
                        @endpermission
                    </div>
                @endif
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-key me-2"></i>All Permissions
                    <span class="badge bg-success ms-2">{{ $user->getAllPermissions()->count() }}</span>
                </h5>
            </div>
            <div class="card-body">
                @if($user->getAllPermissions()->count() > 0)
                    <div class="row">
                        @php
                            $groupedPermissions = $user->getAllPermissions()->groupBy(function($permission) {
                                return explode('.', $permission->name)[0];
                            });
                        @endphp

                        @foreach($groupedPermissions as $group => $permissions)
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary mb-2">
                                    <i class="bi bi-folder me-1"></i>{{ ucfirst($group) }} Permissions
                                </h6>
                                <div class="permission-group">
                                    @foreach($permissions as $permission)
                                        <span class="badge bg-light text-dark me-1 mb-1">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-key display-4 text-muted"></i>
                        <h6 class="mt-3 text-muted">No Permissions</h6>
                        <p class="text-muted">This user has no permissions assigned through roles.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Activity Timeline (if you want to add it later) -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Account Created</h6>
                            <p class="text-muted mb-1">User account was created</p>
                            <small class="text-muted">{{ $user->created_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>

                    @if($user->updated_at != $user->created_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Profile Updated</h6>
                            <p class="text-muted mb-1">User information was last updated</p>
                            <small class="text-muted">{{ $user->updated_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    @endif

                    @if($user->email_verified_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Email Verified</h6>
                            <p class="text-muted mb-1">Email address was verified</p>
                            <small class="text-muted">{{ $user->email_verified_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change User Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="statusAction"></span> this user?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="statusWarning"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="statusChangeForm" method="POST" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" id="newStatus">
                    <button type="submit" class="btn btn-primary" id="confirmStatusChange">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .avatar-xl {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 24px;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -25px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
    }

    .timeline-content {
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid #007bff;
    }

    .permission-group {
        max-height: 150px;
        overflow-y: auto;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status toggle functionality
    const statusToggleBtn = document.querySelector('.status-toggle');

    if (statusToggleBtn) {
        statusToggleBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const currentStatus = this.dataset.currentStatus;
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'deactivate';

            // Update modal content
            document.getElementById('statusAction').textContent = action;
            document.getElementById('newStatus').value = newStatus;
            document.getElementById('statusChangeForm').action = `{{ route('admin.users.index') }}/${userId}/status`;

            // Set warning message
            const warningElement = document.getElementById('statusWarning');
            if (newStatus === 'inactive') {
                warningElement.textContent = 'The user will not be able to log in after deactivation.';
            } else {
                warningElement.textContent = 'The user will be able to log in after activation.';
            }

            // Show modal
            new bootstrap.Modal(document.getElementById('statusChangeModal')).show();
        });
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endsection
