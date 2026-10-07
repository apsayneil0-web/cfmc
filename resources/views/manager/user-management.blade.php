@extends('manager.layout')

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
                    <input type="text" id="searchInput" placeholder="Search users..." class="form-control ps-5 pe-5 py-2" style="min-width: 220px;" value="{{ request('search') }}" autocomplete="off">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" id="searchIcon" style="font-size: 14px;"></i>
                    <button type="button" id="searchClearBtn" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y text-muted p-0 pe-3 border-0 bg-transparent {{ request('search') ? '' : 'd-none' }}" title="Clear search" aria-label="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <select id="statusFilter" class="form-select py-2" style="width: auto; min-width: 120px;">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">User ID</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Name</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Role</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Phone</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Temp Password</th>
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
                    <td class="px-4 px-md-6 py-4">
                        @if($user->roleID == 2)
                        <span class="badge bg-purple text-white">Manager</span>
                        @else
                        <span class="badge bg-success">Farmer</span>
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $user->Phonenumber ?? 'N/A' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">
                        @if($user->temp_password)
                        <span class="d-flex align-items-center gap-2">
                            <span class="temp-password-mask" data-password="{{ $user->temp_password }}">••••••••••</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Show/hide password" onclick="toggleTempPassword(this)"><i class="fas fa-eye"></i></button>
                        </span>
                        @else
                        &mdash;
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        <x-status-badge :status="ucfirst($user->status)" />
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="viewUser({{ $user->id }}); return false;"><i class="fas fa-eye me-2 text-primary fa-fw"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" onclick="editUser({{ $user->id }}); return false;"><i class="fas fa-edit me-2 text-primary fa-fw"></i>Edit</a></li>
                                @if($user->roleID == 3)
                                <li><a class="dropdown-item" href="#" onclick="openChangePassword({{ $user->id }}, {{ Js::from($user->name) }}); return false;"><i class="fas fa-key me-2 text-primary fa-fw"></i>Change Password</a></li>
                                @endif
                                @if($user->status === 'locked')
                                <li><a class="dropdown-item" href="#" onclick="unlockUser({{ $user->id }}); return false;"><i class="fas fa-unlock me-2 text-success fa-fw"></i>Unlock</a></li>
                                @endif
                                @if($user->roleID == 3 && in_array($user->status, ['active', 'inactive']))
                                    @if($user->status === 'active')
                                    <li><a class="dropdown-item" href="#" onclick="toggleUserStatus({{ $user->id }}, 'active'); return false;"><i class="fas fa-user-slash me-2 text-secondary fa-fw"></i>Deactivate</a></li>
                                    @else
                                    <li><a class="dropdown-item" href="#" onclick="toggleUserStatus({{ $user->id }}, 'inactive'); return false;"><i class="fas fa-user-check me-2 text-success fa-fw"></i>Activate</a></li>
                                    @endif
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                @if($user->status === 'archived')
                                <li><a class="dropdown-item" href="#" onclick="unarchiveUser({{ $user->id }}); return false;"><i class="fas fa-box-open me-2 text-success fa-fw"></i>Unarchive</a></li>
                                @else
                                <li><a class="dropdown-item text-danger" href="#" onclick="archiveUser({{ $user->id }}); return false;"><i class="fas fa-archive me-2 fa-fw"></i>Archive</a></li>
                                @endif
                            </ul>
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

<!-- View User Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="editModalLabel">
                    <i class="fas fa-user-edit me-2 text-primary"></i>
                    Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" class="needs-validation" novalidate>
                    <input type="hidden" id="editUserId" name="userId">
                    <input type="hidden" name="name" id="editName">
                    <!-- Row 1: Name Fields -->
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="First name" id="editFirstName" required>
                            <div class="invalid-feedback">Please enter first name.</div>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Middle Name</label>
                            <input type="text" class="form-control form-control-lg" placeholder="Middle name" id="editMiddleName">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="Last name" id="editLastName" required>
                            <div class="invalid-feedback">Please enter last name.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Suffix</label>
                            <select class="form-select form-select-lg" id="editSuffix">
                                <option value="" selected>None</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Username -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" placeholder="username" name="username" id="editUsername" required>
                            </div>
                            <div class="invalid-feedback">Please enter username.</div>
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
                <button type="button" class="btn btn-primary btn-lg px-4" id="updateUserBtn" onclick="updateUser()">
                    <i class="fas fa-save me-2"></i>Update User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="changePasswordModalLabel">
                    <i class="fas fa-key me-2 text-primary"></i>
                    Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border d-flex align-items-center gap-2 mb-4">
                    <i class="fas fa-user text-primary"></i>
                    <span>Setting a new password for <strong id="changePasswordUserName">-</strong>. They will need to use it next time they log in.</span>
                </div>
                <form id="changePasswordForm" class="needs-validation" novalidate>
                    <input type="hidden" id="changePasswordUserId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <input type="password" class="form-control" name="password" id="newPasswordInput" minlength="8" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('newPasswordInput', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimum 8 characters.</div>
                        <div class="invalid-feedback">Password must be at least 8 characters.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <input type="password" class="form-control" name="password_confirmation" id="confirmPasswordInput" minlength="8" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('confirmPasswordInput', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Please confirm the new password.</div>
                        <div class="small text-danger mt-1 d-none" id="passwordMismatchError">Passwords do not match.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary btn-lg px-4" id="submitChangePasswordBtn" onclick="submitChangePassword()">
                    <i class="fas fa-key me-2"></i>Change Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notifyModal" tabindex="-1" aria-labelledby="notifyMessage" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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

<!-- Confirm Action Modal (replaces native browser confirm() dialogs) -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0">
            <div class="modal-body px-4 pt-4 pb-4 text-center">
                <div class="notify-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3" id="confirmActionBadge">
                    <i class="fas" id="confirmActionIcon"></i>
                </div>
                <h6 class="fw-bold mb-2" id="confirmActionTitle">Confirm Action</h6>
                <p class="text-muted mb-4" id="confirmActionMessage">-</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary flex-fill" id="confirmActionBtn">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .btn-check:checked + .card {
        border-color: var(--brand-primary) !important;
        background-color: var(--brand-primary-light);
        box-shadow: 0 0 0 3px var(--focus-ring);
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
    .borderdashed {
        border-style: dashed !important;
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

    // Shows a centered confirmation modal in place of the native confirm().
    // `onConfirm` runs only if the user clicks the primary button.
    function confirmAction(message, onConfirm, options) {
        options = options || {};
        var variant = options.variant === 'danger' ? 'danger' : 'success';

        var badge = document.getElementById('confirmActionBadge');
        badge.className = 'notify-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3 ' + variant;
        document.getElementById('confirmActionIcon').className = 'fas ' + (options.icon || (variant === 'danger' ? 'fa-exclamation-triangle' : 'fa-question-circle'));
        document.getElementById('confirmActionTitle').textContent = options.title || 'Confirm Action';
        document.getElementById('confirmActionMessage').textContent = message;

        var confirmBtn = document.getElementById('confirmActionBtn');
        confirmBtn.className = 'btn flex-fill ' + (variant === 'danger' ? 'btn-danger' : 'btn-primary');
        confirmBtn.textContent = options.confirmLabel || 'OK';

        var modalEl = document.getElementById('confirmActionModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

        var handleConfirm = function() {
            confirmBtn.removeEventListener('click', handleConfirm);
            modal.hide();
            onConfirm();
        };
        confirmBtn.addEventListener('click', handleConfirm);

        modalEl.addEventListener('hidden.bs.modal', function cleanup() {
            modalEl.removeEventListener('hidden.bs.modal', cleanup);
            confirmBtn.removeEventListener('click', handleConfirm);
        });

        modal.show();
    }

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

    // Reveal/hide a farmer's system-generated temporary password in the table.
    function toggleTempPassword(button) {
        var mask = button.previousElementSibling;
        var icon = button.querySelector('i');
        if (mask.textContent === mask.dataset.password) {
            mask.textContent = '••••••••••';
            icon.className = 'fas fa-eye';
        } else {
            mask.textContent = mask.dataset.password;
            icon.className = 'fas fa-eye-slash';
        }
    }

    // Toggle password visibility for a field referenced by id (Change Password modal)
    function togglePasswordField(inputId, button) {
        var input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            button.innerHTML = '<i class="fas fa-eye"></i>';
        }
    }

    // Opens the Change Password modal for a specific farmer account.
    function openChangePassword(userId, userName) {
        document.getElementById('changePasswordUserId').value = userId;
        document.getElementById('changePasswordUserName').textContent = userName;
        new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
    }

    document.getElementById('changePasswordModal').addEventListener('hidden.bs.modal', function() {
        var form = document.getElementById('changePasswordForm');
        form.reset();
        form.classList.remove('was-validated');
        document.getElementById('passwordMismatchError').classList.add('d-none');
    });

    // Submits a manager-initiated password reset for a farmer account. Only
    // Farmer accounts can be targeted — enforced again server-side.
    function submitChangePassword() {
        var form = document.getElementById('changePasswordForm');
        var newPassword = document.getElementById('newPasswordInput').value;
        var confirmPassword = document.getElementById('confirmPasswordInput').value;
        var mismatchError = document.getElementById('passwordMismatchError');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        if (newPassword !== confirmPassword) {
            mismatchError.classList.remove('d-none');
            return;
        }
        mismatchError.classList.add('d-none');

        var userId = document.getElementById('changePasswordUserId').value;
        var userName = document.getElementById('changePasswordUserName').textContent;

        if (!confirm('Change the login password for ' + userName + '? They will need to use the new password next time they log in.')) {
            return;
        }

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PATCH');
        formData.append('password', newPassword);
        formData.append('password_confirmation', confirmPassword);

        var submitBtn = document.getElementById('submitChangePasswordBtn');
        var originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Changing...';

        fetch('/manager/user-management/' + userId + '/change-password', {
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
                bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
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

    // Search and Filter Functionality — filters the full user list on the
    // server (not just the rows on the current page), same as Membership.
    var searchInput = document.getElementById('searchInput');
    var searchIcon = document.getElementById('searchIcon');
    var searchClearBtn = document.getElementById('searchClearBtn');
    var statusFilter = document.getElementById('statusFilter');

    function toggleSearchClearBtn() {
        searchClearBtn.classList.toggle('d-none', !searchInput.value);
    }

    function applyUserFilters() {
        var currentUrl = new URL(window.location.href);

        if (searchInput.value) {
            currentUrl.searchParams.set('search', searchInput.value);
        } else {
            currentUrl.searchParams.delete('search');
        }

        if (statusFilter.value) {
            currentUrl.searchParams.set('status', statusFilter.value);
        } else {
            currentUrl.searchParams.delete('status');
        }

        currentUrl.searchParams.delete('page');

        // Give feedback that the search is in flight since navigation isn't instant.
        searchIcon.className = 'fas fa-spinner fa-spin position-absolute start-3 top-50 translate-middle-y text-muted';
        searchInput.disabled = true;

        window.location.href = currentUrl.toString();
    }

    searchInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            clearTimeout(userSearchTimeout);
            applyUserFilters();
        }
    });

    var userSearchTimeout;
    searchInput.addEventListener('input', function() {
        toggleSearchClearBtn();
        clearTimeout(userSearchTimeout);
        userSearchTimeout = setTimeout(applyUserFilters, 500);
    });

    searchClearBtn.addEventListener('click', function() {
        clearTimeout(userSearchTimeout);
        searchInput.value = '';
        toggleSearchClearBtn();
        applyUserFilters();
    });

    statusFilter.addEventListener('change', applyUserFilters);

    // Restore focus (with cursor at the end) after a search reloads the page,
    // so typing feels continuous instead of resetting to the top of the page.
    searchInput.focus();
    if (searchInput.value) {
        var restoredValue = searchInput.value;
        searchInput.value = '';
        searchInput.value = restoredValue;
    }

    // Activate / Deactivate Farmer Account Function
    function toggleUserStatus(userId, currentStatus) {
        var confirmMessage = currentStatus === 'active'
            ? 'Deactivate this account? The farmer will no longer be able to log in.'
            : 'Activate this account? The farmer will be able to log in again.';

        confirmAction(confirmMessage, function() {
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            fetch('/manager/user-management/' + userId + '/toggle-status', {
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
        }, { title: currentStatus === 'active' ? 'Deactivate Account' : 'Activate Account' });
    }

    // Unlock User Function
    function unlockUser(userId) {
        confirmAction('Unlock this account? The failed login count will be reset.', function() {
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            fetch('/manager/user-management/' + userId + '/unlock', {
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
        }, { title: 'Unlock Account', icon: 'fa-unlock' });
    }

    // Unarchive User Function
    function unarchiveUser(userId) {
        confirmAction('Restore this account from the archive?', function() {
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            fetch('/manager/user-management/' + userId + '/unarchive', {
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
        }, { title: 'Restore Account', icon: 'fa-box-open' });
    }

    // Archive User Function
    function archiveUser(userId) {
        confirmAction('Are you sure you want to archive this user?', function() {
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            fetch('/manager/user-management/' + userId + '/archive', {
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
        }, { title: 'Archive User', variant: 'danger', icon: 'fa-archive' });
    }

    // View User Function
    function viewUser(userId) {
        fetch('/manager/user-management/' + userId, {
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
                document.getElementById('viewPhone').textContent = user.Phonenumber || 'N/A';
                document.getElementById('viewRole').textContent = user.roleID == 2 ? 'Manager' : 'Farmer';
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

    // Splits a stored "First [Middle.] Last [Suffix]" display name back into
    // its parts for the Edit form. Best-effort: names weren't stored as
    // separate fields to begin with, so this mirrors how the registration
    // form builds the name in the first place, rather than a strict parse.
    var NAME_SUFFIXES = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
    function splitFullName(fullName) {
        var tokens = (fullName || '').trim().split(/\s+/).filter(Boolean);
        var suffix = '';

        if (tokens.length > 1 && NAME_SUFFIXES.indexOf(tokens[tokens.length - 1]) !== -1) {
            suffix = tokens.pop();
        }

        var middle = '';
        if (tokens.length >= 3 && /^[A-Za-z]\.?$/.test(tokens[1])) {
            var first = tokens[0];
            middle = tokens[1];
            var last = tokens.slice(2).join(' ');
        } else if (tokens.length >= 2) {
            var first = tokens.slice(0, -1).join(' ');
            var last = tokens[tokens.length - 1];
        } else {
            var first = tokens[0] || '';
            var last = '';
        }

        return { first: first, middle: middle, last: last, suffix: suffix };
    }

    // Recombines the Edit form's separate name fields into the single stored
    // display name, the same way the registration form does.
    function combineEditName() {
        var first = document.getElementById('editFirstName').value.trim();
        var middle = document.getElementById('editMiddleName').value.trim();
        var last = document.getElementById('editLastName').value.trim();
        var suffix = document.getElementById('editSuffix').value;

        var fullName = [first, middle, last].filter(Boolean).join(' ');
        if (suffix) fullName += ' ' + suffix;

        document.getElementById('editName').value = fullName;
    }

    // Edit User Function
    function editUser(userId) {
        fetch('/manager/user-management/' + userId, {
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
                var nameParts = splitFullName(user.name);

                document.getElementById('editUserId').value = user.id;
                document.getElementById('editFirstName').value = nameParts.first;
                document.getElementById('editMiddleName').value = nameParts.middle;
                document.getElementById('editLastName').value = nameParts.last;
                document.getElementById('editSuffix').value = nameParts.suffix;
                document.getElementById('editUsername').value = user.username;
                document.getElementById('editPhone').value = user.Phonenumber || '';
                document.getElementById('editStatus').value = user.status;

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

        combineEditName();

        var userId = document.getElementById('editUserId').value;
        var formData = new FormData(form);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PUT');

        var submitBtn = document.getElementById('updateUserBtn');
        var originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

        fetch('/manager/user-management/' + userId, {
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