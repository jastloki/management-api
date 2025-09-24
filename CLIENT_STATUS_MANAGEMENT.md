# Client Status Management

This document describes the client status management feature that allows you to set and manage client statuses through a flexible status system.

## Overview

The client status management system provides:
- Dynamic status creation and management through the admin panel
- Individual client status updates via dropdown interface
- Bulk status updates for multiple clients
- API endpoints for programmatic status management
- Backward compatibility with existing client data

## Database Structure

### Status Table
```sql
CREATE TABLE statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Client Table (Status Fields)
```sql
-- Existing fields in clients table
status_id BIGINT UNSIGNED NULL INDEX,  -- References statuses.id
status ENUM('active', 'inactive') DEFAULT 'active'  -- Legacy field (kept for compatibility)
```

## Models and Relationships

### Client Model
```php
// Relationship to Status
public function status(): BelongsTo
{
    return $this->belongsTo(Status::class);
}

// Helper methods
public function getStatusNameAttribute(): ?string
public function hasStatus(string $statusName): bool
public function scopeWithStatusName($query, string $statusName)
public function scopeWithStatus($query, int $statusId)
```

### Status Model
```php
// Relationship to Clients
public function clients(): HasMany
{
    return $this->hasMany(Client::class);
}
```

## Default Statuses

The system comes with predefined statuses that are seeded automatically:

1. **Active** - Client is active and engaged
2. **Inactive** - Client is temporarily inactive
3. **Prospect** - Potential client being evaluated
4. **Lead** - Qualified lead ready for engagement
5. **Contract Signed** - Client has signed a contract
6. **On Hold** - Client engagement is on hold
7. **Completed** - Project or engagement completed
8. **Cancelled** - Client relationship cancelled

## API Endpoints

### Update Individual Client Status
```http
PATCH /admin/clients/{client}/status
Content-Type: application/json

{
    "status_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Status updated successfully.",
    "client": {
        "id": 123,
        "status_id": 1,
        "status": {
            "id": 1,
            "name": "Active"
        }
    }
}
```

### Bulk Update Client Statuses
```http
PATCH /admin/clients/bulk-status
Content-Type: application/json

{
    "client_ids": [1, 2, 3],
    "status_id": 2
}
```

**Response:**
```json
{
    "success": true,
    "message": "Status updated for 3 clients.",
    "updated_count": 3
}
```

### Get All Statuses
```http
GET /admin/api/statuses
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Active",
            "description": "Client is active and engaged",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

## Web Interface Features

### Client Index Page
- **Status Display**: Shows current status with colored badges
- **Quick Status Update**: Click on status badge to change via dropdown
- **Bulk Actions**: Select multiple clients and update their status at once
- **Real-time Updates**: AJAX-powered status changes without page reload

### Client Forms (Create/Edit)
- **Status Dropdown**: Select from available statuses in the database
- **Validation**: Ensures selected status exists
- **Auto-assignment**: Automatically assigns creating user as client owner

### Status Management
- **CRUD Operations**: Full create, read, update, delete for statuses
- **Usage Tracking**: See which clients are using each status
- **Safe Deletion**: Prevents deletion of statuses currently in use

## Usage Examples

### Programmatic Status Updates

```php
// Update single client status
$client = Client::find(1);
$client->update(['status_id' => 2]);

// Bulk update multiple clients
Client::whereIn('id', [1, 2, 3])->update(['status_id' => 4]);

// Query clients by status
$activeClients = Client::withStatusName('Active')->get();
$prospectClients = Client::withStatus(3)->get();

// Check client status
if ($client->hasStatus('Active')) {
    // Client is active
}
```

### JavaScript/AJAX Integration

```javascript
// Update client status via AJAX
fetch('/admin/clients/123/status', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        status_id: 2
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Status updated successfully');
    }
});
```

## Migration and Upgrade

### From Legacy Status System
If you're upgrading from the legacy enum-based status system:

1. **Run Status Seeder**:
   ```bash
   php artisan db:seed --class=StatusSeeder
   ```

2. **Migrate Existing Data** (optional script):
   ```php
   // Convert legacy status values to status_id
   $activeStatus = Status::where('name', 'Active')->first();
   $inactiveStatus = Status::where('name', 'Inactive')->first();
   
   Client::where('status', 'active')->update(['status_id' => $activeStatus->id]);
   Client::where('status', 'inactive')->update(['status_id' => $inactiveStatus->id]);
   ```

## Validation Rules

### Client Status Validation
```php
'status_id' => 'required|exists:statuses,id'
```

### Bulk Update Validation
```php
'client_ids' => 'required|array',
'client_ids.*' => 'exists:clients,id',
'status_id' => 'required|exists:statuses,id'
```

## Security Considerations

- **Authorization**: All status endpoints require admin authentication
- **CSRF Protection**: All form submissions include CSRF tokens
- **Input Validation**: Status IDs are validated against existing statuses
- **Mass Assignment Protection**: Only allowed fields can be updated

## Performance Notes

- **Eager Loading**: Status relationships are loaded efficiently (`Client::with('status')`)
- **Indexed Fields**: `status_id` field is indexed for fast queries
- **Bulk Operations**: Bulk updates use single queries for efficiency
- **Caching**: Status data can be cached as it changes infrequently

## Troubleshooting

### Common Issues

1. **Status not updating**: Check CSRF token and proper authentication
2. **Dropdown empty**: Ensure StatusSeeder has been run
3. **Bulk actions not working**: Verify JavaScript is enabled and no console errors
4. **Migration errors**: Check database permissions and run migrations

### Debug Commands

```bash
# Check if statuses exist
php artisan tinker
>>> App\Models\Status::count()

# Verify client-status relationships
>>> App\Models\Client::with('status')->first()

# Test status update
>>> $client = App\Models\Client::first();
>>> $client->update(['status_id' => 1]);
```

## Future Enhancements

Potential improvements to consider:
- Status color coding and icons
- Status workflow/transition rules
- Status change history/audit trail
- Email notifications on status changes
- Custom status fields and metadata
- Status-based permissions and access control