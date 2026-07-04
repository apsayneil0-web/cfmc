<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Manager Dashboard') - CFMC</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover { background-color: #f3f4f6; }
        .sidebar-link.active { background-color: #e5e7eb; border-left: 3px solid #2563eb; }
        .stat-card { transition: transform 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-light min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 shrink-0 border-r border-gray-200 bg-white shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">CFMC Manager</h2>
                <p class="text-sm text-gray-500">Cooperative Management</p>
            </div>

            <nav class="p-4 space-y-1">
                <a href="{{ route('manager.dashboard') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.dashboard') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    Dashboard
                </a>

                <a href="{{ route('manager.user-management') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.user-management') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-user-cog w-5"></i>
                    User Management
                </a>

                <a href="{{ route('manager.membership') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-user-plus w-5"></i>
                    Membership Registration
                </a>

                <a href="{{ route('manager.farmer-profile') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-users w-5"></i>
                    Farmer Profile
                </a>

                <a href="{{ route('manager.schedule-approval') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-calendar-check w-5"></i>
                    Schedule Approval
                </a>

                <a href="{{ route('manager.machine-schedule') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-tractor w-5"></i>
                    Machine Scheduling
                </a>

                <a href="{{ route('manager.financial') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-chart-pie w-5"></i>
                    Financial Management
                </a>

                <a href="{{ route('manager.cbu') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-piggy-bank w-5"></i>
                    CBU Management
                </a>

                <a href="{{ route('manager.loan-request') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-hand-holding-usd w-5"></i>
                    Loan Requests
                </a>

                <a href="{{ route('manager.loan-management') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-file-invoice-dollar w-5"></i>
                    Loan Management
                </a>

                <a href="{{ route('manager.payment') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-money-bill-wave w-5"></i>
                    Payments
                </a>

                <a href="{{ route('manager.machinery') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-cogs w-5"></i>
                    Machinery
                </a>

                <a href="{{ route('manager.complaints') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-exclamation-circle w-5"></i>
                    Complaints
                </a>

                <a href="{{ route('manager.announcement') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-bullhorn w-5"></i>
                    Announcements
                </a>

                <a href="{{ route('manager.reporting') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600">
                    <i class="fas fa-file-alt w-5"></i>
                    Reporting
                </a>

                <button onclick="confirmLogout()" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-danger w-100 border-0 bg-transparent">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    Logout
                </button>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Top Header -->
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">@yield('header', 'Dashboard')</h1>
                <div class="flex items-center gap-4">
                    <button class="p-2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium">
                            M
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Manager</p>
                            <p class="text-xs text-gray-500">Admin</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 p-md-8">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-sign-out-alt text-danger" style="font-size: 48px;"></i>
                        </div>
                        <p class="mb-0">Are you sure you want to logout?</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        }
    </script>
</body>
</html>