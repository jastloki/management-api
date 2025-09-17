@extends('admin.layouts.app')

@section('title', 'Users')
@section('heading', 'User Management')

@section('page-actions')
<div class="d-flex gap-2 flex-wrap">
    @permission('users.create')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add New User
    </a>
    @endpermission

    <!-- Bulk Actions -->
    <div class="btn-group bulk-actions" style="display: none;">
        <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-check-square me-2"></i>Bulk Actions
        </button>
        <ul class="dropdown-menu">
            <li><h6 class="dropdown-header">Update Status</h6></li>
            <li>
                <a class="dropdown-item bulk-status-update"
                   href="#"
                   data-status="active">
                    <i class="bi bi-check-circle me-2"></i>Activate Selected
                </a>
            </li>
            <li>
                <a class="dropdown-item bulk-status-update"
                   href="#"
                   data-status="inactive">
                    <i class="bi bi-x-circle me-2"></i>Deactivate Selected
                </a>
            </li>
        </ul>
    </div>
</div>
@endsection

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text"
                       class="form-control"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by name or email...">
            </div>

            <div class="col-md-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}"
                                {{ request('role') === $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-people me-2"></i>Users
            <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
        </h5>
    </div>

    <div class="card-body p-0">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Last Activity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           class="form-check-input user-checkbox"
                                           value="{{ $user->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted">ID: {{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $user->email }}</span>
                                    @if($user->email_verified_at)
                                        <i class="bi bi-patch-check-fill text-success ms-1" title="Verified"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-info me-1">{{ ucfirst($role->name) }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No roles assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->updated_at)
                                        <span class="text-muted">{{ $user->updated_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @permission('users.view')
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="btn btn-sm btn-outline-info"
                                           title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @endpermission

                                        @permission('users.edit')
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endpermission

                                        <!-- Status Toggle -->
                                        @permission('users.edit')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-{{ $user->email_verified_at ? 'warning' : 'success' }} status-toggle"
                                                data-user-id="{{ $user->id }}"
                                                data-current-status="{{ $user->email_verified_at ? 'active' : 'inactive' }}"
                                                title="{{ $user->email_verified_at ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $user->email_verified_at ? 'pause' : 'play' }}"></i>
                                        </button>
                                        @endpermission

                                        @permission('users.delete')
                                        @if($user->id !== auth()->id())
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-user"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                        @endpermission
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                    </div>
                    {{ $users->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No users found</h5>
                <p class="text-muted">No users match your current filters.</p>
                @permission('users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add First User
                </a>
                @endpermission
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="bulkActionType"></span> <strong id="bulkSelectedCount"></strong> selected users?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="bulkActionForm" method="POST" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="user_ids" id="bulkUserIds">
                    <input type="hidden" name="status" id="bulkStatus">
                    <button type="submit" class="btn btn-primary" id="bulkActionButton">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #5a5c69;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        font-size: 0.75rem;
    }

    .btn-group .btn {
        margin-right: 0;
    }

    .bulk-actions {
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');

    selectAllCheckbox?.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionsVisibility();
    });

    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActionsVisibility();

            // Update select all checkbox
            const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === userCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < userCheckboxes.length;
        });
    });

    function updateBulkActionsVisibility() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'block';
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Status Toggle
    document.querySelectorAll('.status-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const currentStatus = this.dataset.currentStatus;
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            fetch(`{{ route('admin.users.index') }}/${userId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {

                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating user status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating user status');
            });
        });
    });

    // Delete User
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;

            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteForm').action = `{{ route('admin.users.index') }}/${userId}`;

            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });

    // Bulk Actions
    document.querySelectorAll('.bulk-status-update').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const status = this.dataset.status;
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            const userIds = Array.from(checkedBoxes).map(cb => cb.value);

            if (userIds.length === 0) {
                alert('Please select users first');
                return;
            }

            document.getElementById('bulkActionType').textContent = status === 'active' ? 'activate' : 'deactivate';
            document.getElementById('bulkSelectedCount').textContent = userIds.length;
            document.getElementById('bulkUserIds').value = JSON.stringify(userIds);
            document.getElementById('bulkStatus').value = status;
            document.getElementById('bulkActionForm').action = '{{ route('admin.users.bulk.status') }}';

            new bootstrap.Modal(document.getElementById('bulkActionModal')).show();
        });
    });

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
