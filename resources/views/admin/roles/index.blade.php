@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')
@section('heading', 'Roles & Permissions')

@section('page-actions')
<div class="btn-group">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
        <i class="bi bi-plus-lg me-1"></i>Add Role
    </button>
    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
        <i class="bi bi-shield-plus me-1"></i>Add Permission
    </button>
    <a href="{{ route('admin.roles.create-defaults') }}" class="btn btn-outline-secondary">
        <i class="bi bi-gear me-1"></i>Create Defaults
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Roles Management -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people-fill me-2 text-primary"></i>Roles Management
                </h5>
            </div>
            <div class="card-body">
                @if($roles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Role Name</th>
                                    <th>Permissions</th>
                                    <th>Users Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-person-badge text-white"></i>
                                            </div>
                                            <div>
                                                <strong>{{ ucfirst($role->name) }}</strong>
                                                <div class="small text-muted">Created {{ $role->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($role->permissions->take(3) as $permission)
                                                <span class="badge bg-info">{{ $permission->name }}</span>
                                            @empty
                                                <span class="text-muted small">No permissions</span>
                                            @endforelse
                                            @if($role->permissions->count() > 3)
                                                <span class="badge bg-secondary">+{{ $role->permissions->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $role->users->count() }} users</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                    onclick="editRole({{ $role->id }}, '{{ $role->name }}', {{ $role->permissions->pluck('id') }})"
                                                    data-bs-toggle="modal" data-bs-target="#editRoleModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @if($role->users->count() == 0)
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this role?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <p class="text-muted mt-2">No roles found yet.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                            <i class="bi bi-plus-lg me-1"></i>Create First Role
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Users Role Assignment -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-gear me-2 text-primary"></i>User Roles
                </h5>
            </div>
            <div class="card-body">
                @if($users->count() > 0)
                    @foreach($users as $user)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2">
                                <span class="text-white small font-weight-bold">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <div class="small text-muted">{{ $user->email }}</div>
                                @if($user->roles->count() > 0)
                                    <div class="small">
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="assignUserRoles({{ $user->id }}, '{{ $user->name }}', {{ $user->roles->pluck('id') }})"
                                data-bs-toggle="modal" data-bs-target="#assignRolesModal">
                            <i class="bi bi-gear"></i>
                        </button>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-person-x display-6 text-muted"></i>
                        <p class="text-muted small mt-2">No users found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Permissions Overview -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check me-2 text-primary"></i>Permissions Overview
                </h5>
            </div>
            <div class="card-body">
                @if($permissions->count() > 0)
                    <div class="row">
                        @foreach($permissions as $module => $modulePermissions)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-primary mb-2">
                                    <i class="bi bi-folder2 me-1"></i>{{ ucfirst($module) }} Module
                                </h6>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($modulePermissions as $permission)
                                        <span class="badge bg-light text-dark border">
                                            {{ $permission->name }}
                                            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline ms-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link p-0 text-danger"
                                                        style="font-size: 0.75rem; line-height: 1;"
                                                        onclick="return confirm('Are you sure you want to delete this permission?')">
                                                    ×
                                                </button>
                                            </form>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-shield-x display-4 text-muted"></i>
                        <p class="text-muted mt-2">No permissions found yet.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
                            <i class="bi bi-shield-plus me-1"></i>Create First Permission
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="role_name" name="name" required>
                    </div>

                    @if($permissions->count() > 0)
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            @foreach($permissions as $module => $modulePermissions)
                                <div class="mb-3">
                                    <h6 class="text-primary mb-2">{{ ucfirst($module) }} Module</h6>
                                    @foreach($modulePermissions as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[]" value="{{ $permission->id }}"
                                               id="perm_{{ $permission->id }}">
                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRoleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_role_name" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="edit_role_name" name="name" required>
                    </div>

                    @if($permissions->count() > 0)
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            @foreach($permissions as $module => $modulePermissions)
                                <div class="mb-3">
                                    <h6 class="text-primary mb-2">{{ ucfirst($module) }} Module</h6>
                                    @foreach($modulePermissions as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input edit-permission" type="checkbox"
                                               name="permissions[]" value="{{ $permission->id }}"
                                               id="edit_perm_{{ $permission->id }}">
                                        <label class="form-check-label" for="edit_perm_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Permission Modal -->
<div class="modal fade" id="createPermissionModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="permission_name" class="form-label">Permission Name</label>
                        <input type="text" class="form-control" id="permission_name" name="name"
                               placeholder="e.g. clients.view" required>
                        <div class="form-text">Use format: module.action (e.g., clients.view, users.create)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign User Roles Modal -->
<div class="modal fade" id="assignRolesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignRolesForm" method="POST">
            <input type="hidden" id="userIdInput" name="user_id">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Roles to <span id="userName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($roles->count() > 0)
                    <div class="mb-3">
                        <label class="form-label">Available Roles</label>
                        @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input user-role" type="checkbox"
                                   name="roles[]" value="{{ $role->id }}"
                                   id="user_role_{{ $role->id }}">
                            <label class="form-check-label" for="user_role_{{ $role->id }}">
                                <strong>{{ ucfirst($role->name) }}</strong>
                                <div class="small text-muted">{{ $role->permissions->count() }} permissions</div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-3">
                        <p class="text-muted">No roles available. Create roles first.</p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Roles</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .badge {
        font-size: 0.75rem;
    }

    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>

<script>
function editRole(roleId, roleName, permissions) {
    document.getElementById('edit_role_name').value = roleName;
    document.getElementById('editRoleForm').action = '{{ route("admin.roles.index") }}/' + roleId;

    // Clear all checkboxes
    document.querySelectorAll('.edit-permission').forEach(checkbox => {
        checkbox.checked = false;
    });

    // Check the permissions for this role
    permissions.forEach(permissionId => {
        const checkbox = document.getElementById('edit_perm_' + permissionId);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}

function assignUserRoles(userId, userName, userRoles) {
    document.getElementById('userName').textContent = userName;
    document.getElementById('assignRolesForm').action = '{{ route("admin.users.attach.roles") }}';
    document.getElementById('userIdInput').value = userId;

    // Clear all checkboxes
    document.querySelectorAll('.user-role').forEach(checkbox => {
        checkbox.checked = false;
    });

    // Check the roles for this user
    userRoles.forEach(roleId => {
        const checkbox = document.getElementById('user_role_' + roleId);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}
</script>
@endsection
