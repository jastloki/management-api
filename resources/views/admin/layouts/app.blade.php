<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Custom CSS -->
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-weight: 600;
            color: white !important;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        main {
            margin-left: 240px;
            padding-top: 48px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .badge {
            border-radius: 20px;
        }

        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-gray-300 {
            color: #dddfeb !important;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .border-4 {
            border-width: 4px !important;
        }

        .chart-area {
            position: relative;
            height: 400px;
        }

        .chart-pie {
            position: relative;
            height: 300px;
        }

        .dropdown-menu {
            border-radius: 8px;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: #f8f9fc;
        }

        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }

        /* Avatar classes */
        .avatar-xs {
            width: 1.5rem;
            height: 1.5rem;
            font-size: 0.6rem;
        }

        .avatar-sm {
            width: 2rem;
            height: 2rem;
            font-size: 0.75rem;
        }

        .avatar-md {
            width: 3rem;
            height: 3rem;
            font-size: 1rem;
        }

        /* Soft color backgrounds */
        .bg-success-soft {
            background-color: #d1e7dd !important;
        }

        .bg-warning-soft {
            background-color: #fff3cd !important;
        }

        .bg-danger-soft {
            background-color: #f8d7da !important;
        }

        .bg-secondary-soft {
            background-color: #e2e3e5 !important;
        }

        .bg-info-soft {
            background-color: #d1ecf1 !important;
        }

        .bg-primary-soft {
            background-color: #d1ecf1 !important;
        }

        /* Text colors for soft backgrounds */
        .text-success {
            color: #0f5132 !important;
        }

        .text-warning {
            color: #664d03 !important;
        }

        .text-danger {
            color: #842029 !important;
        }

        .text-secondary {
            color: #41464b !important;
        }

        .text-info {
            color: #055160 !important;
        }

        .text-primary {
            color: #055160 !important;
        }

        /* Client Comments Compact Styles */
        .client-comments-compact .last-comment {
            border: 1px solid #e9ecef !important;
            transition: all 0.2s ease;
        }

        .client-comments-compact .last-comment:hover {
            border-color: #dee2e6 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .client-comments-compact .quick-comment-form {
            border: 1px solid #dee2e6 !important;
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .client-comments-compact .min-width-0 {
            min-width: 0;
        }

        .client-comments-compact .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
        }

        /* Client Comments Full View Styles */
        .comment-item {
            transition: all 0.2s ease;
        }

        .comment-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .comment-content p {
            line-height: 1.5;
            color: #333;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark fixed-top flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-shield-check me-2"></i>Admin Panel
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link px-3 btn btn-link text-white">
                        <i class="bi bi-box-arrow-right me-1"></i>Sign out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        @permission('admin.dashboard')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-house-door me-2"></i>Dashboard
                            </a>
                        </li>
                        @endpermission

                        @permission('clients.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.clients.*') && request('converted') !== 'false' ? 'active' : '' }}" href="{{ route('admin.clients.index', ['converted' => 'true']) }}">
                                <i class="bi bi-people me-2"></i>Clients
                            </a>
                        </li>
                        @permission('clients.view.leads')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.clients.*') && request('converted') === 'false' ? 'active' : '' }}" href="{{ route('admin.clients.index', ['converted' => 'false']) }}">
                                <i class="bi bi-person-plus me-2"></i>Leads
                            </a>
                        </li>
                        @endpermission
                        @endpermission

                        @permission('users.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-person-gear me-2"></i>Users
                            </a>
                        </li>
                        @endpermission

                        @permission('statuses.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.statuses.*') ? 'active' : '' }}" href="{{ route('admin.statuses.index') }}">
                                <i class="bi bi-tags me-2"></i>Statuses
                            </a>
                        </li>
                        @endpermission

                        @permission('emails.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.emails.index') ? 'active' : '' }}" href="{{ route('admin.emails.index') }}">
                                <i class="bi bi-envelope me-2"></i>Email Queue
                            </a>
                        </li>
                        @endpermission

                        @permission('emails.analytics')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.emails.analytics') ? 'active' : '' }}" href="{{ route('admin.emails.analytics') }}">
                                <i class="bi bi-graph-up me-2"></i>Email Analytics
                            </a>
                        </li>
                        @endpermission

                        @permission('emails.providers')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.emails.providers') ? 'active' : '' }}" href="{{ route('admin.emails.providers') }}">
                                <i class="bi bi-gear me-2"></i>Email Providers
                            </a>
                        </li>
                        @endpermission

                        @permission('emails.templates')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}" href="{{ route('admin.email-templates.index') }}">
                                <i class="bi bi-file-earmark-text me-2"></i>Email Templates
                            </a>
                        </li>
                        @endpermission

                        @permission('proxies.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.proxies.*') ? 'active' : '' }}" href="{{ route('admin.proxies.index') }}">
                                <i class="bi bi-globe me-2"></i>Proxies
                            </a>
                        </li>
                        @endpermission

                        @permission('roles.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                                <i class="bi bi-shield-check me-2"></i>Roles & Permissions
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('heading', 'Dashboard')</h1>
                    @yield('page-actions')
                </div>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Livewire Scripts -->
    @livewireScripts

    @yield('scripts')
</body>
</html>
