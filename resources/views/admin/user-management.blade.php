@extends('admin.layout')

@section('title', 'User Management')
@section('header', 'User Management')

@section('content')
<!-- User Table Card -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header Actions -->
    <div class="p-4 p-md-6 border-b border-gray-200">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3 flex-grow-1 flex-wrap">
                <div class="position-relative">
                    <input type="text" id="searchInput" placeholder="Search users..." class="form-control ps-5 py-2" style="min-width: 200px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select id="roleFilter" class="form-select py-2" style="width: auto; min-width: 120px;">
                    <option value="">All Roles</option>
                    <option value="1">Admin</option>
                    <option value="2">Manager</option>
                    <option value="3">Farmer</option>
                </select>
                <select id="statusFilter" class="form-select py-2" style="width: auto; min-width: 120px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="locked">Locked</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus"></i>
                <span>Create Account</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">User ID</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Name</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Email</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Role</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Phone</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Created At</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($users as $user)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium">USR-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                            <div>
                                <p class="mb-0 fw-medium text-dark">{{ $user->name }}</p>
                                <small class="text-muted">@ {{ $user->username }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $user->email ?? 'N/A' }}</td>
                    <td class="px-4 px-md-6 py-4">
                        @if($user->roleID == 1)
                        <span class="badge bg-dark text-white">Admin</span>
                        @elseif($user->roleID == 2)
                        <span class="badge bg-purple text-white">Manager</span>
                        @else
                        <span class="badge bg-success">Farmer</span>
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $user->Phonenumber ?? 'N/A' }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <x-status-badge :status="ucfirst($user->status)" />
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="View" onclick="viewUser({{ $user->id }})"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-outline-warning" title="Edit" onclick="editUser({{ $user->id }})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-warning" title="Archive" onclick="archiveUser({{ $user->id }})"><i class="fas fa-archive"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-4 text-center text-muted">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-4 px-md-6 py-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <span class="text-muted small">Showing {{ $users->count() }} of {{ $users->total() }} entries</span>
        <nav aria-label="Table pagination">
            <ul class="pagination pagination-sm mb-0">
                @if($users->onFirstPage())
                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                <li class="page-item"><a class="page-link" href="{{ $users->previousPageUrl() }}">Previous</a></li>
                @endif

                @for($i = 1; $i <= $users->lastPage(); $i++)
                <li class="page-item {{ $i == $users->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
                </li>
                @endfor

                @if($users->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $users->nextPageUrl() }}">Next</a></li>
                @else
                <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
</div>

<!-- Bootstrap 5 Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="createModalLabel">
                    <i class="fas fa-user-plus me-2 text-primary"></i>
                    Create New Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="userForm" class="needs-validation" novalidate>
                    <div class="alert alert-light border d-flex align-items-center gap-2 mb-4">
                        <i class="fas fa-user text-primary"></i>
                        <span>This form creates a <strong id="accountTypeLabel">Farmer</strong> account.</span>
                    </div>

                    <!-- Row 0: Account Type -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Account Type <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="roleID" id="roleFarmer" value="3" autocomplete="off" checked>
                                    <label class="card border p-3 text-center cursor-pointer hover-border-primary mb-0 w-100" for="roleFarmer">
                                        <i class="fas fa-seedling fs-4 d-block mb-1 text-muted"></i>
                                        <span class="fw-semibold small">Farmer</span>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="roleID" id="roleManager" value="2" autocomplete="off">
                                    <label class="card border p-3 text-center cursor-pointer hover-border-primary mb-0 w-100" for="roleManager">
                                        <i class="fas fa-user-tie fs-4 d-block mb-1 text-muted"></i>
                                        <span class="fw-semibold small">Manager</span>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="roleID" id="roleAdmin" value="1" autocomplete="off">
                                    <label class="card border p-3 text-center cursor-pointer hover-border-primary mb-0 w-100" for="roleAdmin">
                                        <i class="fas fa-user-shield fs-4 d-block mb-1 text-muted"></i>
                                        <span class="fw-semibold small">Admin</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Full Name -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="Enter full name" name="name" id="nameInput" list="approvedFarmersList" autocomplete="off" required>
                            <datalist id="approvedFarmersList">
                                @foreach($availableFarmers as $availableFarmer)
                                    <option value="{{ $availableFarmer->full_name }}">
                                @endforeach
                            </datalist>
                            <div class="form-text" id="nameHint">Start typing to pick from approved membership requests, or enter a name manually.</div>
                            <div class="invalid-feedback">Please enter full name.</div>
                            <input type="hidden" name="farmer_id" id="farmerIdInput">
                        </div>
                    </div>

                    <!-- Row 2: Username & Email -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" placeholder="username" name="username" id="usernameInput" required>
                            </div>
                            <div class="invalid-feedback">Please enter username.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span id="emailRequiredMark" class="text-muted">(optional)</span></label>
                            <input type="email" class="form-control form-control-lg" placeholder="email@example.com" name="email" id="emailInput">
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>
                    </div>

                    <!-- Row 3: Phone -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control form-control-lg" placeholder="0912-345-6789" name="Phonenumber" id="phoneInput">
                        </div>
                    </div>

                    <!-- Row 4: Password -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <input type="password" class="form-control" placeholder="Enter password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Please enter password.</div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary btn-lg px-4" id="submitUserBtn" onclick="openCreateAccountConfirm()">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Create Account Modal -->
<div class="modal fade" id="confirmCreateModal" tabindex="-1" aria-labelledby="confirmCreateModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0 pt-3 px-3">
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4 pt-0">
                <div class="text-center mb-4">
                    <div class="confirm-account-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="confirmCreateModalLabel">Confirm Account Creation</h5>
                    <p class="text-muted small mb-0">Please review the details before creating this account</p>
                </div>

                <div class="confirm-account-summary border rounded-3 px-3">
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-id-badge text-primary me-2 fa-fw"></i>Account Type</span>
                        <span class="fw-semibold text-end" id="confirmAccRole">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-user text-primary me-2 fa-fw"></i>Full Name</span>
                        <span class="fw-semibold text-end" id="confirmAccName">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-at text-primary me-2 fa-fw"></i>Username</span>
                        <span class="fw-semibold text-end" id="confirmAccUsername">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-envelope text-primary me-2 fa-fw"></i>Email</span>
                        <span class="fw-semibold text-end" id="confirmAccEmail">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-phone text-primary me-2 fa-fw"></i>Phone</span>
                        <span class="fw-semibold text-end" id="confirmAccPhone">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2">
                        <span class="text-muted small"><i class="fas fa-toggle-on text-primary me-2 fa-fw"></i>Status</span>
                        <span class="fw-semibold text-end">Inactive until first login</span>
                    </div>
                </div>

                <div class="info-banner variant-info mt-3 mb-4" id="confirmAccLinkNote" style="display: none;">
                    <i class="fas fa-link mt-1"></i>
                    <p class="small mb-0">This account will be linked to the matching approved membership record.</p>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill py-2" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-2"></i>Go Back
                    </button>
                    <button type="button" class="btn btn-primary flex-fill py-2" id="confirmCreateAccountBtn">
                        <i class="fas fa-check-circle me-2"></i>Confirm &amp; Create
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="viewModalLabel">
                    <i class="fas fa-user me-2 text-primary"></i>
                    User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto mb-3" id="viewAvatar" style="width: 80px; height: 80px; font-size: 32px;">JD</div>
                    <h4 class="mb-0" id="viewName">User Name</h4>
                    <p class="text-muted" id="viewUsername">@username</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Email</small>
                            <strong id="viewEmail">email@example.com</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Phone Number</small>
                            <strong id="viewPhone">0912-345-6789</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Role</small>
                            <strong id="viewRole">Farmer</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Status</small>
                            <strong id="viewStatus">Active</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">User ID</small>
                            <strong id="viewUserId">USR-001</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Created At</small>
                            <strong id="viewCreatedAt">Jul 01, 2026</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="editModalLabel">
                    <i class="fas fa-user-edit me-2 text-warning"></i>
                    Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" class="needs-validation" novalidate>
                    <input type="hidden" id="editUserId" name="userId">

                    <!-- Row 0: Account Type -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Account Type <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="roleID" id="editRoleFarmer" value="3" autocomplete="off">
                                    <label class="card border p-3 text-center cursor-pointer hover-border-primary mb-0 w-100" for="editRoleFarmer">
                                        <i class="fas fa-seedling fs-4 d-block mb-1 text-muted"></i>
                                        <span class="fw-semibold small">Farmer</span>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="roleID" id="editRoleManager" value="2" autocomplete="off">
                                    <label class="card border p-3 text-center cursor-pointer hover-border-primary mb-0 w-100" for="editRoleManager">
                                        <i class="fas fa-user-tie fs-4 d-block mb-1 text-muted"></i>
                                        <span class="fw-semibold small">Manager</span>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="roleID" id="editRoleAdmin" value="1" autocomplete="off">
                                    <label class="card border p-3 text-center cursor-pointer hover-border-primary mb-0 w-100" for="editRoleAdmin">
                                        <i class="fas fa-user-shield fs-4 d-block mb-1 text-muted"></i>
                                        <span class="fw-semibold small">Admin</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Full Name -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="Enter full name" name="name" id="editName" required>
                            <div class="invalid-feedback">Please enter full name.</div>
                        </div>
                    </div>

                    <!-- Row 2: Username & Email -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" placeholder="username" name="username" id="editUsername" required>
                            </div>
                            <div class="invalid-feedback">Please enter username.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span id="editEmailRequiredMark" class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg" placeholder="email@example.com" name="email" id="editEmail" required>
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>
                    </div>

                    <!-- Row 3: Phone & Status -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control form-control-lg" placeholder="0912-345-6789" name="Phonenumber" id="editPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" name="status" id="editStatus" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="locked">Locked</option>
                            </select>
                            <div class="invalid-feedback">Please select status.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning btn-lg px-4" id="updateUserBtn" onclick="updateUser()">
                    <i class="fas fa-save me-2"></i>Update User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notifyModal" tabindex="-1" aria-labelledby="notifyMessage" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0">
            <div class="modal-body px-4 pt-4 pb-4 text-center">
                <div class="notify-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3" id="notifyIconBadge">
                    <i class="fas" id="notifyIcon"></i>
                </div>
                <p class="mb-4" id="notifyMessage">-</p>
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .btn-check:checked + .card {
        border-color: var(--brand-primary) !important;
        background-color: rgba(31, 111, 92, 0.05);
        box-shadow: 0 0 0 3px rgba(31, 111, 92, 0.12);
    }
    .btn-check:checked + .card i {
        color: var(--brand-primary) !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .hover-border-primary:hover {
        border-color: var(--brand-primary) !important;
    }
    .form-control-lg, .form-select-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    .notify-icon-badge {
        width: 64px;
        height: 64px;
        font-size: 1.75rem;
    }
    .notify-icon-badge.success {
        background-color: var(--brand-success-light);
        color: var(--brand-success);
    }
    .notify-icon-badge.danger {
        background-color: var(--brand-danger-light);
        color: var(--brand-danger);
    }
    .confirm-account-icon-badge {
        width: 64px;
        height: 64px;
        font-size: 1.75rem;
        background-color: var(--brand-primary-light);
        color: var(--brand-primary);
    }
    .confirm-account-summary > div:last-child {
        border-bottom: none !important;
    }
    @media (max-width: 767.98px) {
        .modal-dialog {
            margin: 0.5rem;
        }
        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    }
</style>

<script>
    // Shows a centered notification modal in place of the native alert().
    // `onClose`, if given, runs once after the modal is dismissed.
    function showAlert(message, type, onClose) {
        type = type === 'danger' ? 'danger' : 'success';

        var badge = document.getElementById('notifyIconBadge');
        badge.className = 'notify-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3 ' + type;
        document.getElementById('notifyIcon').className = 'fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle');
        document.getElementById('notifyMessage').textContent = message;

        var modalEl = document.getElementById('notifyModal');
        if (onClose) {
            modalEl.addEventListener('hidden.bs.modal', function handler() {
                modalEl.removeEventListener('hidden.bs.modal', handler);
                onClose();
            });
        }

        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    }

    var ROLE_LABELS = { '1': 'Admin', '2': 'Manager', '3': 'Farmer' };

    // Enable Bootstrap validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Map approved farmer full names to their membership record, so picking a
    // suggested name from the datalist links the account to that record and
    // auto-fills the details already captured during registration.
    var approvedFarmersByName = {};
    @foreach($availableFarmers as $availableFarmer)
        approvedFarmersByName[@json($availableFarmer->full_name).toLowerCase()] = {
            id: {{ $availableFarmer->id }},
            phone: @json($availableFarmer->contact_number),
            firstName: @json($availableFarmer->first_name),
            lastName: @json($availableFarmer->last_name)
        };
    @endforeach

    // Builds a lowercase, no-punctuation username suggestion from a farmer's name.
    function suggestUsername(firstName, lastName) {
        return (firstName + lastName).toLowerCase().replace(/[^a-z0-9]/g, '');
    }

    var nameInput = document.getElementById('nameInput');
    var farmerIdInput = document.getElementById('farmerIdInput');
    var phoneInput = document.getElementById('phoneInput');
    var usernameInput = document.getElementById('usernameInput');
    var emailInput = document.getElementById('emailInput');
    var emailRequiredMark = document.getElementById('emailRequiredMark');
    var accountTypeLabel = document.getElementById('accountTypeLabel');
    var nameHint = document.getElementById('nameHint');

    function getSelectedRole() {
        var checked = document.querySelector('#userForm input[name="roleID"]:checked');
        return checked ? checked.value : '3';
    }

    // The Full Name field only suggests/links approved farmer records when
    // creating a Farmer account; email is only required for Admin/Manager.
    function applyRoleRules() {
        var role = getSelectedRole();
        var isFarmer = role === '3';

        accountTypeLabel.textContent = ROLE_LABELS[role];

        emailInput.required = !isFarmer;
        emailRequiredMark.textContent = isFarmer ? '(optional)' : '*';
        emailRequiredMark.className = isFarmer ? 'text-muted' : 'text-danger';

        if (isFarmer) {
            nameInput.setAttribute('list', 'approvedFarmersList');
            nameHint.style.display = '';
        } else {
            nameInput.removeAttribute('list');
            nameHint.style.display = 'none';
            farmerIdInput.value = '';
        }
    }

    document.querySelectorAll('#userForm input[name="roleID"]').forEach(function(input) {
        input.addEventListener('change', applyRoleRules);
    });
    applyRoleRules();

    nameInput.addEventListener('input', function() {
        if (getSelectedRole() !== '3') {
            farmerIdInput.value = '';
            return;
        }

        var match = approvedFarmersByName[nameInput.value.trim().toLowerCase()];
        if (match) {
            farmerIdInput.value = match.id;
            phoneInput.value = match.phone || '';
            usernameInput.value = suggestUsername(match.firstName, match.lastName);
        } else {
            farmerIdInput.value = '';
        }
    });

    document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
        var form = document.getElementById('userForm');
        form.reset();
        form.classList.remove('was-validated');
        farmerIdInput.value = '';
        applyRoleRules();
    });

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        var input = this.previousElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            this.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            this.innerHTML = '<i class="fas fa-eye"></i>';
        }
    });

    // Validates the create-account form and, if valid, shows a review
    // step before the account is actually created.
    function openCreateAccountConfirm() {
        var form = document.getElementById('userForm');
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        document.getElementById('confirmAccRole').textContent = ROLE_LABELS[getSelectedRole()];
        document.getElementById('confirmAccName').textContent = nameInput.value.trim();
        document.getElementById('confirmAccUsername').textContent = '@' + usernameInput.value.trim();
        document.getElementById('confirmAccEmail').textContent = emailInput.value.trim() || 'Not provided';
        document.getElementById('confirmAccPhone').textContent = phoneInput.value.trim() || 'Not provided';
        document.getElementById('confirmAccLinkNote').style.display = farmerIdInput.value ? 'flex' : 'none';

        new bootstrap.Modal(document.getElementById('confirmCreateModal')).show();
    }

    document.getElementById('confirmCreateAccountBtn').addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('confirmCreateModal')).hide();
        submitUserForm();
    });

    // Form submission function
    function submitUserForm() {
        var form = document.getElementById('userForm');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        var formData = new FormData(form);
        var submitBtn = document.getElementById('submitUserBtn');
        var originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';

        fetch('{{ route("admin.user.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function(response) {
            return response.json().catch(function() {
                return { success: false, message: 'Unknown error', errors: {} };
            });
        })
        .then(function(data) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
                showAlert(data.message, 'success', function() {
                    window.location.reload();
                });
            } else {
                var errorMsg = data.message || 'Validation failed';
                if (data.errors) {
                    var errorsArray = [];
                    for (var key in data.errors) {
                        errorsArray.push(key + ': ' + data.errors[key].join(', '));
                    }
                    errorMsg = errorsArray.join('\n');
                }
                showAlert(errorMsg, 'danger');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            showAlert(error.message, 'danger');
        });
    }

    // Search and Filter Functionality
    var searchInput = document.getElementById('searchInput');
    var roleFilter = document.getElementById('roleFilter');
    var statusFilter = document.getElementById('statusFilter');

    function filterUsers() {
        var searchTerm = searchInput.value.toLowerCase();
        var roleValue = roleFilter.value;
        var statusValue = statusFilter.value;
        var rows = document.querySelectorAll('tbody tr');

        rows.forEach(function(row) {
            var name = row.querySelector('td:nth-child(2)') ? row.querySelector('td:nth-child(2)').textContent.toLowerCase() : '';
            var email = row.querySelector('td:nth-child(3)') ? row.querySelector('td:nth-child(3)').textContent.toLowerCase() : '';
            var role = row.querySelector('td:nth-child(4)') ? row.querySelector('td:nth-child(4)').textContent.trim() : '';
            var status = row.querySelector('td:nth-child(6)') ? row.querySelector('td:nth-child(6)').textContent.trim() : '';
            var phone = row.querySelector('td:nth-child(5)') ? row.querySelector('td:nth-child(5)').textContent.toLowerCase() : '';

            var matchesSearch = searchTerm === '' ||
                name.includes(searchTerm) ||
                email.includes(searchTerm) ||
                phone.includes(searchTerm);

            var matchesRole = roleValue === '' ||
                (roleValue === '1' && role === 'Admin') ||
                (roleValue === '2' && role === 'Manager') ||
                (roleValue === '3' && role === 'Farmer');

            var matchesStatus = statusValue === '' ||
                (statusValue === 'active' && status === 'Active') ||
                (statusValue === 'inactive' && status === 'Inactive') ||
                (statusValue === 'locked' && status === 'Locked') ||
                (statusValue === 'archived' && status === 'Archived');

            if (matchesSearch && matchesRole && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterUsers);
    roleFilter.addEventListener('change', filterUsers);
    statusFilter.addEventListener('change', filterUsers);

    // Archive User Function
    function archiveUser(userId) {
        if (confirm('Are you sure you want to archive this user?')) {
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            fetch('/admin/user-management/' + userId + '/archive', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    showAlert(data.message, 'success', function() {
                        window.location.reload();
                    });
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                showAlert(error.message, 'danger');
            });
        }
    }

    // View User Function
    function viewUser(userId) {
        fetch('/admin/user-management/' + userId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                var user = data.user;
                document.getElementById('viewAvatar').textContent = user.name.substring(0, 2).toUpperCase();
                document.getElementById('viewName').textContent = user.name;
                document.getElementById('viewUsername').textContent = '@' + user.username;
                document.getElementById('viewEmail').textContent = user.email || 'N/A';
                document.getElementById('viewPhone').textContent = user.Phonenumber || 'N/A';
                document.getElementById('viewRole').textContent = ROLE_LABELS[String(user.roleID)] || 'Farmer';
                document.getElementById('viewStatus').textContent = user.status.charAt(0).toUpperCase() + user.status.slice(1);
                document.getElementById('viewUserId').textContent = 'USR-' + String(user.id).padStart(3, '0');
                document.getElementById('viewCreatedAt').textContent = new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

                var viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
                viewModal.show();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showAlert(error.message, 'danger');
        });
    }

    // Applies the email-required rule to the Edit form based on its selected role.
    function applyEditRoleRules() {
        var checked = document.querySelector('#editUserForm input[name="roleID"]:checked');
        var role = checked ? checked.value : '3';
        var isFarmer = role === '3';
        var editEmail = document.getElementById('editEmail');
        var mark = document.getElementById('editEmailRequiredMark');

        editEmail.required = !isFarmer;
        mark.textContent = isFarmer ? '(optional)' : '*';
        mark.className = isFarmer ? 'text-muted' : 'text-danger';
    }

    document.querySelectorAll('#editUserForm input[name="roleID"]').forEach(function(input) {
        input.addEventListener('change', applyEditRoleRules);
    });

    // Edit User Function
    function editUser(userId) {
        fetch('/admin/user-management/' + userId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                var user = data.user;
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editName').value = user.name;
                document.getElementById('editUsername').value = user.username;
                document.getElementById('editEmail').value = user.email || '';
                document.getElementById('editPhone').value = user.Phonenumber || '';
                document.getElementById('editStatus').value = user.status;

                var roleRadio = document.getElementById(
                    user.roleID == 1 ? 'editRoleAdmin' : (user.roleID == 2 ? 'editRoleManager' : 'editRoleFarmer')
                );
                roleRadio.checked = true;
                applyEditRoleRules();

                var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showAlert(error.message, 'danger');
        });
    }

    // Update User Function
    function updateUser() {
        var form = document.getElementById('editUserForm');
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        var userId = document.getElementById('editUserId').value;
        var formData = new FormData(form);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PUT');

        var submitBtn = document.getElementById('updateUserBtn');
        var originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

        fetch('/admin/user-management/' + userId, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                showAlert(data.message, 'success', function() {
                    window.location.reload();
                });
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            showAlert(error.message, 'danger');
        });
    }
</script>
@endsection
