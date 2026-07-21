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
</head>
<body class="bg-light min-h-screen">
    <div class="app-shell">
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar -->
        <aside class="app-sidebar w-64 shrink-0 border-r border-gray-200 bg-white shadow-sm" id="appSidebar">
            <div class="sidebar-brand p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 mb-0">CFMC Manager</h2>
                <p class="text-sm text-gray-500 mb-0">Cooperative Management</p>
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

                <a href="{{ route('manager.membership') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.membership') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-user-plus w-5"></i>
                    Membership Registration
                </a>

                <a href="{{ route('manager.farmer-profile') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.farmer-profile') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-users w-5"></i>
                    Farmer Profile
                </a>

                <a href="{{ route('manager.schedule-approval') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.schedule-approval') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-calendar-check w-5"></i>
                    Schedule Approval
                </a>

                <a href="{{ route('manager.machine-schedule') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.machine-schedule') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-tractor w-5"></i>
                    Machine Scheduling
                </a>

                <a href="{{ route('manager.financial') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.financial') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-chart-pie w-5"></i>
                    Financial Management
                </a>

                <a href="{{ route('manager.cbu') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.cbu') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-piggy-bank w-5"></i>
                    CBU Management
                </a>

                <a href="{{ route('manager.loan-request') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.loan-request') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-hand-holding-usd w-5"></i>
                    Loan Requests
                </a>

                <a href="{{ route('manager.loan-management') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.loan-management') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-file-invoice-dollar w-5"></i>
                    Loan Management
                </a>

                <a href="{{ route('manager.loan-appointment') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.loan-appointment') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-calendar-alt w-5"></i>
                    Loan Appointments
                </a>

                <a href="{{ route('manager.payment') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.payment') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-money-bill-wave w-5"></i>
                    Payments
                </a>

                <a href="{{ route('manager.machinery') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.machinery') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-cogs w-5"></i>
                    Machinery
                </a>

                <a href="{{ route('manager.complaints') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.complaints') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-exclamation-circle w-5"></i>
                    Complaints
                </a>

                <a href="{{ route('manager.announcement') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.announcement') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-bullhorn w-5"></i>
                    Announcements
                </a>

                <a href="{{ route('manager.reporting') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('manager.reporting') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-file-alt w-5"></i>
                    Reporting
                </a>

                <hr class="my-2 text-gray-200">

                <button onclick="confirmLogout()" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-danger w-100 border-0 bg-transparent">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    Logout
                </button>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0">
            <!-- Top Header -->
            <header class="app-topbar bg-white border-b border-gray-200 px-4 px-md-8 py-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="icon-btn text-secondary sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-900 mb-0">@yield('header', 'Dashboard')</h1>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <x-notification-bell :notifications="$announcementNotifications ?? collect()" :unread-count="$unreadAnnouncementCount ?? 0" />
                    <x-profile-menu />
                </div>
            </header>

            <!-- Page Content -->
            <div class="app-main-content p-4 p-md-8">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <x-notification-modals :notifications="$announcementNotifications ?? collect()" />
    <x-profile-picture-modal />

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0 pt-3 px-3">
                    <h5 class="modal-title fw-bold" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-2">
                    <div class="text-center mb-4">
                        <div class="logout-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <p class="mb-0 text-dark">Are you sure you want to logout?</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill py-2" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="{{ route('logout') }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 py-2">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .logout-icon-badge {
            width: 64px;
            height: 64px;
            font-size: 1.75rem;
            background-color: var(--brand-danger-light);
            color: var(--brand-danger);
        }
    </style>

    <script>
        function confirmLogout() {
            var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        }

        (function () {
            var sidebar = document.getElementById('appSidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            var toggle = document.getElementById('sidebarToggle');

            function closeSidebar() {
                sidebar.classList.remove('is-open');
                backdrop.classList.remove('is-open');
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    sidebar.classList.toggle('is-open');
                    backdrop.classList.toggle('is-open');
                });
            }
            backdrop.addEventListener('click', closeSidebar);
        })();
    </script>
</body>
</html>
