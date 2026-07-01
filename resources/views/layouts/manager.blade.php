<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manager Dashboard - Cooperative Management System">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/managerdashboard.css') }}">
    <style>
        /* Page-specific header styles */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-content h1 {
            font-size: 28px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 4px;
        }

        .header-content p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        /* Search & Filter Card */
        .search-filter-card {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-box, .filter-box {
            flex: 1;
            min-width: 200px;
        }

        /* Table Card */
        .table-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        /* Responsive page header */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-header .btn {
                width: 100%;
            }

            .search-filter-card {
                flex-direction: column;
            }

            .search-box, .filter-box {
                width: 100%;
                min-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .header-content h1 {
                font-size: 22px;
            }

            .header-content p {
                font-size: 13px;
            }
        }

        @media (max-width: 360px) {
            .header-content h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="profile-block">
                <div class="avatar">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <div class="profile-info">
                    <span class="profile-label">Manager Account</span>
                    <span class="profile-role">Admin Dashboard</span>
                </div>
            </div>
            <button class="profile-toggle" type="button" aria-label="Profile options">
                <span class="profile-arrow">›</span>
            </button>
        </div>

        <div class="divider"></div>

        <nav class="sidebar-menu">
            <?php
            $currentUrl = request()->url();
            function isActive($url, $currentUrl) {
                return strpos($currentUrl, $url) !== false ? 'active' : '';
            }
            ?>
            <a href="/dashboard/manager" class="nav-item {{ isActive('/dashboard/manager', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                </span>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="/membership" class="nav-item {{ isActive('/membership', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </span>
                <span class="nav-label">Membership</span>
            </a>
            <a href="/manager/users" class="nav-item {{ isActive('/manager/users', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V20h14v-3.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V20h6v-3.5C23 14.17 18.33 13 16 13z"/>
                    </svg>
                </span>
                <span class="nav-label">User Manager</span>
            </a>
            <a href="/farmer-profile" class="nav-item {{ isActive('/farmer-profile', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                </span>
                <span class="nav-label">Farmer Profile</span>
            </a>
            <a href="/loan-request" class="nav-item {{ isActive('/loan-request', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                    </svg>
                </span>
                <span class="nav-label">Loan Request</span>
            </a>
            <a href="/loan-management" class="nav-item {{ isActive('/loan-management', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    </svg>
                </span>
                <span class="nav-label">Loan Management</span>
            </a>
            <a href="/payment" class="nav-item {{ isActive('/payment', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                    </svg>
                </span>
                <span class="nav-label">Payment</span>
            </a>
            <a href="/financial" class="nav-item {{ isActive('/financial', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zm5.6 8H19v6h-2.8z"/>
                    </svg>
                </span>
                <span class="nav-label">Financial</span>
            </a>
            <a href="/machinery" class="nav-item {{ isActive('/machinery', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19.77 7.23l.01-.01-3.72-3.72L15 4.56l2.11 2.11c-.94.36-1.61 1.26-1.61 2.33 0 1.38 1.12 2.5 2.5 2.5.36 0 .69-.08 1-.21v7.21c0 .55-.45 1-1 1s-1-.45-1-1V14c0-1.1-.9-2-2-2h-1V5c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v16h10v-7.5h1.5v5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V9c0-.69-.28-1.32-.73-1.77zM12 10H6V5h6v5zm6 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>
                    </svg>
                </span>
                <span class="nav-label">Machinery</span>
            </a>
            <a href="/complaints" class="nav-item {{ isActive('/complaints', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </span>
                <span class="nav-label">Complaints</span>
            </a>
            <a href="/announcements" class="nav-item {{ isActive('/announcements', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                    </svg>
                </span>
                <span class="nav-label">Announcements</span>
            </a>
            <a href="/reports" class="nav-item {{ isActive('/reports', $currentUrl) }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    </svg>
                </span>
                <span class="nav-label">Reports</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout" id="logoutTrigger" class="nav-item logout">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                </span>
                <span class="nav-label">Logout</span>
            </a>
        </div>
    </div>

    <!-- SIDEBAR OVERLAY -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- LOGOUT CONFIRMATION MODAL -->
    <div class="modal-backdrop" id="logoutModal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
            <button type="button" class="modal-close" id="logoutClose" aria-label="Close logout confirmation">×</button>
            <div class="modal-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 13v-2H7V8l-5 4 5 4v-3h9zM20 3H10c-1.1 0-2 .9-2 2v4h2V5h10v14H10v-4H8v4c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                </svg>
            </div>
            <h2 id="logoutModalTitle">Confirm Logout</h2>
            <p>Are you sure you want to log out? You will be returned to the sign-in page.</p>
            <div class="modal-actions">
                <button type="button" class="modal-button secondary" id="logoutCancel">Cancel</button>
                <a href="/logout" class="modal-button primary" id="logoutConfirm">Logout</a>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">

        <!-- NAVBAR -->
        <div class="navbar">
            <h3>
                @if(request()->is('dashboard/admin'))
                    Admin Dashboard
                @else
                    Manager Dashboard
                @endif
            </h3>
            <button class="menu-toggle" id="sidebarToggle" type="button" aria-label="Open sidebar menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                </svg>
                <span>Menu</span>
            </button>
        </div>

        <!-- PAGE CONTENT -->
        <div class="content">
            @yield('content')
        </div>

    </div>

</div>

<script src="{{ asset('js/modules.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (sidebar.classList.contains('active')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && sidebar && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        const logoutTrigger = document.getElementById('logoutTrigger');
        const logoutModal = document.getElementById('logoutModal');
        const logoutClose = document.getElementById('logoutClose');
        const logoutCancel = document.getElementById('logoutCancel');

        function openLogoutModal(event) {
            event.preventDefault();
            logoutModal.classList.add('active');
            logoutModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeLogoutModal() {
            logoutModal.classList.remove('active');
            logoutModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (logoutTrigger) {
            logoutTrigger.addEventListener('click', openLogoutModal);
        }

        if (logoutClose) {
            logoutClose.addEventListener('click', closeLogoutModal);
        }

        if (logoutCancel) {
            logoutCancel.addEventListener('click', closeLogoutModal);
        }

        if (logoutModal) {
            logoutModal.addEventListener('click', function(event) {
                if (event.target === logoutModal) {
                    closeLogoutModal();
                }
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && logoutModal && logoutModal.classList.contains('active')) {
                closeLogoutModal();
            }
        });
    });

    </script>
@stack('scripts')
</body>
</html>