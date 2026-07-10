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
                    <input type="text" id="searchInput" placeholder="Search users..." class="form-control ps-5 py-2" style="min-width: 200px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select id="roleFilter" class="form-select py-2" style="width: auto; min-width: 120px;">
                    <option value="">All Roles</option>
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
                        @if($user->roleID == 2)
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
                        <span>This form creates a <strong>Farmer</strong> account.</span>
                    </div>

                    <!-- Row 1: Full Name -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="Enter full name" name="name" required>
                            <div class="invalid-feedback">Please enter full name.</div>
                        </div>
                    </div>

                    <!-- Row 2: Username & Email -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" placeholder="username" name="username" required>
                            </div>
                            <div class="invalid-feedback">Please enter username.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-muted">(optional)</span></label>
                            <input type="email" class="form-control form-control-lg" placeholder="email@example.com" name="email" id="emailInput">
                            <div class="invalid-feedback">Please enter email.</div>
                        </div>
                    </div>

                    <!-- Row 3: Phone & Status -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control form-control-lg" placeholder="0912-345-6789" name="Phonenumber">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select status.</div>
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
                <button type="button" class="btn btn-primary btn-lg px-4" id="submitUserBtn" onclick="submitUserForm()">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
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
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg" placeholder="email@example.com" name="email" id="editEmail" required>
                            <div class="invalid-feedback">Please enter email.</div>
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

    // Form submission function
    function submitUserForm() {
        console.log('submitUserForm called');
        var form = document.getElementById('userForm');
        console.log('Form found:', !!form);

        if (!form.checkValidity()) {
            console.log('Form validation failed');
            form.classList.add('was-validated');
            return;
        }

        console.log('Form validation passed');

        var formData = new FormData(form);
        var submitBtn = document.getElementById('submitUserBtn');
        var originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';

        var url = '{{ route("user.store") }}';
        console.log('Submitting to:', url);
        console.log('CSRF Token:', '{{ csrf_token() }}');
        console.log('Form data:', Object.fromEntries(formData));

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function(response) {
            console.log('Response status:', response.status);
            return response.json().catch(function(err) {
                console.log('JSON parse error:', err);
                return { success: false, message: 'Unknown error', errors: {} };
            });
        })
        .then(function(data) {
            console.log('Full Response data:', JSON.stringify(data, null, 2));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            if (data.success) {
                alert(data.message);
                var modal = bootstrap.Modal.getInstance(document.getElementById('createModal'));
                modal.hide();
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                var errorMsg = data.message || 'Validation failed';
                if (data.errors) {
                    var errorsArray = [];
                    for (var key in data.errors) {
                        errorsArray.push(key + ': ' + data.errors[key].join(', '));
                    }
                    errorMsg = errorsArray.join('\n');
                }
                alert('Error: ' + errorMsg);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            alert('Error: ' + error.message);
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
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            });
        }
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
                document.getElementById('viewEmail').textContent = user.email || 'N/A';
                document.getElementById('viewPhone').textContent = user.Phonenumber || 'N/A';
                document.getElementById('viewRole').textContent = user.roleID == 2 ? 'Manager' : 'Farmer';
                document.getElementById('viewStatus').textContent = user.status.charAt(0).toUpperCase() + user.status.slice(1);
                document.getElementById('viewUserId').textContent = 'USR-' + String(user.id).padStart(3, '0');
                document.getElementById('viewCreatedAt').textContent = new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

                var viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
                viewModal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
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
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editName').value = user.name;
                document.getElementById('editUsername').value = user.username;
                document.getElementById('editEmail').value = user.email || '';
                document.getElementById('editPhone').value = user.Phonenumber || '';
                document.getElementById('editStatus').value = user.status;

                var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('Error: ' + error.message);
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
                alert(data.message);
                var editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                editModal.hide();
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            alert('Error: ' + error.message);
        });
    }
</script>
@endsection