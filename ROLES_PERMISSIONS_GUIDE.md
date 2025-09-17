# Roles and Permissions System Guide

This guide explains how to use the Spatie Laravel Permission package that has been integrated into your application for managing user roles and permissions.

## Overview

The roles and permissions system allows you to:
- Create custom roles with specific permissions
- Assign roles to users
- Control access to features and routes based on permissions
- Manage fine-grained access control throughout the application

## Getting Started

### Default Roles

Three default roles are created when you run the seeder:

1. **Admin** - Full access to all features
2. **Manager** - Access to most features except user/role management
3. **User** - Limited access to view-only features

### Default Permissions

The system includes the following permission categories:

- **Clients**: `clients.view`, `clients.create`, `clients.edit`, `clients.delete`, `clients.import`, `clients.export`
- **Statuses**: `statuses.view`, `statuses.create`, `statuses.edit`, `statuses.delete`
- **Emails**: `emails.view`, `emails.send`, `emails.analytics`, `emails.providers`
- **Roles**: `roles.view`, `roles.create`, `roles.edit`, `roles.delete`
- **Users**: `users.view`, `users.create`, `users.edit`, `users.delete`
- **Admin**: `admin.dashboard`, `admin.settings`

## Using the Management Interface

### Accessing Roles & Permissions

Navigate to **Admin Panel > Roles & Permissions** to manage the system.

### Creating New Roles

1. Click "Add Role" button
2. Enter role name
3. Select permissions to assign
4. Click "Create Role"

### Editing Roles

1. Click the edit (pencil) icon next to a role
2. Modify name and permissions
3. Click "Update Role"

### Creating Custom Permissions

1. Click "Add Permission" button
2. Enter permission name (format: `module.action`)
3. Click "Create Permission"

### Assigning Roles to Users

1. Find the user in the "User Roles" section
2. Click the gear icon
3. Select/deselect roles
4. Click "Update Roles"

## Code Usage

### Checking Permissions in Controllers

```php
// Check if user has permission
if (auth()->user()->can('clients.edit')) {
    // User can edit clients
}

// Check if user has role
if (auth()->user()->hasRole('admin')) {
    // User is admin
}

// Check if user has any of multiple roles
if (auth()->user()->hasAnyRole(['admin', 'manager'])) {
    // User is admin or manager
}
```

### Using Middleware

Apply permission middleware to routes:

```php
Route::get('/clients', [ClientController::class, 'index'])
    ->middleware('permission:clients.view');

Route::post('/clients', [ClientController::class, 'store'])
    ->middleware('permission:clients.create');
```

### Blade Templates

Use custom Blade directives to conditionally show content:

```blade
@permission('clients.create')
    <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
        Add Client
    </a>
@endpermission

@role('admin')
    <div class="admin-only-content">
        This is only visible to admins
    </div>
@endrole

@anyrole('admin', 'manager')
    <div class="management-content">
        This is visible to admins and managers
    </div>
@endanyrole
```

### Assigning Permissions Programmatically

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create permission
$permission = Permission::create(['name' => 'custom.permission']);

// Create role
$role = Role::create(['name' => 'custom_role']);

// Assign permission to role
$role->givePermissionTo('custom.permission');

// Assign role to user
$user->assignRole('custom_role');

// Give permission directly to user
$user->givePermissionTo('custom.permission');
```

## Best Practices

### Permission Naming Convention

Use the format: `module.action`

Examples:
- `clients.view`
- `users.create`
- `reports.export`
- `settings.manage`

### Role Hierarchy

Consider creating roles with increasing levels of access:
1. **Viewer** - Read-only access
2. **Editor** - Create and edit access
3. **Manager** - Full module access
4. **Admin** - System-wide access

### Security Considerations

1. **Always check permissions** in both routes and views
2. **Use specific permissions** rather than broad role checks when possible
3. **Regularly audit** user roles and permissions
4. **Test permission changes** thoroughly before deploying

## Advanced Usage

### Creating Custom Middleware

```php
// In your middleware
public function handle($request, Closure $next, $permission)
{
    if (!auth()->user()->can($permission)) {
        abort(403);
    }
    return $next($request);
}
```

### Bulk Operations

```php
// Assign multiple permissions to role
$role->syncPermissions(['clients.view', 'clients.edit', 'clients.create']);

// Assign multiple roles to user
$user->syncRoles(['manager', 'editor']);
```

### Checking Permissions with Wildcards

```php
// Check if user has any client permissions
if (auth()->user()->can('clients.*')) {
    // User has some client permissions
}
```

## Troubleshooting

### Common Issues

1. **Permission denied errors**: Ensure user has the required role/permission
2. **Menu items not showing**: Check Blade directive syntax and permission names
3. **Cache issues**: Clear permission cache with `php artisan permission:cache-reset`

### Debugging

```php
// Get all user permissions
$permissions = auth()->user()->getAllPermissions();

// Get all user roles
$roles = auth()->user()->getRoleNames();

// Check specific permission
$hasPermission = auth()->user()->can('clients.view');
```

## Migration from Legacy System

If you have existing users with the old `role` column:

1. The system maintains backward compatibility
2. Users with `role = 'admin'` will automatically have admin access
3. Run the seeder to assign proper roles: `php artisan db:seed --class=RolePermissionSeeder`
4. Gradually migrate to using Spatie roles instead of the legacy column

## Database Tables

The system creates these tables:
- `roles` - Stores role definitions
- `permissions` - Stores permission definitions
- `model_has_permissions` - Links users to permissions
- `model_has_roles` - Links users to roles
- `role_has_permissions` - Links roles to permissions

## Commands

Useful Artisan commands:

```bash
# Reset permission cache
php artisan permission:cache-reset

# Create default permissions and roles
php artisan db:seed --class=RolePermissionSeeder

# Show all permissions
php artisan permission:show
```

This system provides flexible, scalable access control that can grow with your application's needs.