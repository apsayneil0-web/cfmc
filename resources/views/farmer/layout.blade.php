<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Farmer Dashboard') - CFMC</title>
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
                <h2 class="text-xl font-bold text-gray-900 mb-0">CFMC</h2>
                <p class="text-sm text-gray-500 mb-0">Farmer Portal</p>
            </div>

            <nav class="p-4 space-y-1">
                <a href="{{ route('farmer.dashboard') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('farmer.dashboard') ? 'active text-gray-900' : 'text-gray-600' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    Dashboard
                </a>

                <span class="sidebar-link d-flex align-items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-400" style="pointer-events: none;">
                    <i class="fas fa-id-card w-5"></i>
                    My Membership
                    <span class="badge bg-secondary-subtle text-secondary ms-auto">Soon</span>
                </span>

                <span class="sidebar-link d-flex align-items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-400" style="pointer-events: none;">
                    <i class="fas fa-tractor w-5"></i>
                    Machine Schedule
                    <span class="badge bg-secondary-subtle text-secondary ms-auto">Soon</span>
                </span>

                <span class="sidebar-link d-flex align-items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-400" style="pointer-events: none;">
                    <i class="fas fa-piggy-bank w-5"></i>
                    CBU & Loans
                    <span class="badge bg-secondary-subtle text-secondary ms-auto">Soon</span>
                </span>

                <span class="sidebar-link d-flex align-items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-400" style="pointer-events: none;">
                    <i class="fas fa-bullhorn w-5"></i>
                    Announcements
                    <span class="badge bg-secondary-subtle text-secondary ms-auto">Soon</span>
                </span>

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
                    <button class="icon-btn text-secondary" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-initials text-success" style="width: 2.5rem; height: 2.5rem;">
                            F
                        </div>
                        <div class="d-none d-sm-block">
                            <p class="text-sm font-medium text-gray-900 mb-0">Farmer</p>
                            <p class="text-xs text-gray-500 mb-0">Member</p>
                        </div>
                    </div>
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
