@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h5 font-weight-bold">{{ $stats['total_clients'] }}</div>
                        <div class="small">Total Clients</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people" style="font-size: 2rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h5 font-weight-bold">{{ $stats['active_clients'] }}</div>
                        <div class="small">Active Clients</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-check" style="font-size: 2rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h5 font-weight-bold">{{ $stats['inactive_clients'] }}</div>
                        <div class="small">Inactive Clients</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-x" style="font-size: 2rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h5 font-weight-bold">{{ $stats['total_users'] }}</div>
                        <div class="small">Total Users</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-gear" style="font-size: 2rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Clients -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Recent Clients
                    </h5>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>View All
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($recent_clients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_clients as $client)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <span class="text-white small font-weight-bold">
                                                    {{ strtoupper(substr($client->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <strong>{{ $client->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $client->email }}</td>
                                    <td>{{ $client->company ?? '-' }}</td>
                                    <td>
                                        @if($client->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $client->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="text-muted mt-2">No clients found yet.</p>
                        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Add First Client
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2 text-primary"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Add New Client
                    </a>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-people me-2"></i>Manage Clients
                    </a>
                </div>
            </div>
        </div>

        <!-- Welcome Card -->
        <div class="card mt-4">
            <div class="card-body text-center">
                <i class="bi bi-emoji-smile display-4 text-primary mb-3"></i>
                <h5 class="card-title">Welcome, {{ auth()->user()->name }}!</h5>
                <p class="card-text text-muted">
                    You are logged in as an administrator. You have full access to manage clients and system settings.
                </p>
                <div class="small text-muted">
                    <i class="bi bi-shield-check me-1"></i>
                    Admin Access Level
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .card-header {
        padding: 1.25rem 1.25rem 0.75rem;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
    
    .card {
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endsection 