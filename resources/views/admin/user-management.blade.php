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
                    <input type="text" id="searchInput" placeholder="Search users..." class="form-control ps-5 pe-5 py-2" style="min-width: 220px;" value="{{ request('search') }}" autocomplete="off">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" id="searchIcon" style="font-size: 14px;"></i>
                    <button type="button" id="searchClearBtn" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y text-muted p-0 pe-3 border-0 bg-transparent {{ request('search') ? '' : 'd-none' }}" title="Clear search" aria-label="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <select id="roleFilter" class="form-select py-2" style="width: auto; min-width: 120px;">
                    <option value="">All Roles</option>
                    <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ request('role') == '2' ? 'selected' : '' }}>Manager</option>
                    <option value="3" {{ request('role') == '3' ? 'selected' : '' }}>Farmer</option>
                </select>
                <select id="statusFilter" class="form-select py-2" style="width: auto; min-width: 120px;">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
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
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="View" onclick="viewUser({{ $user->id }})"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-outline-warning" title="Edit" onclick="editUser({{ $user->id }})"><i class="fas fa-edit"></i></button>
                            @if($user->roleID != 1)
                            <button class="btn btn-sm btn-outline-primary" title="Change Password" onclick="openChangePassword({{ $user->id }}, {{ Js::from($user->name) }})"><i class="fas fa-key"></i></button>
                            @endif
                            @if($user->roleID != 1 && in_array($user->status, ['active', 'inactive']))
                                @if($user->status === 'active')
                                <button class="btn btn-sm btn-outline-secondary" title="Deactivate" onclick="toggleUserStatus({{ $user->id }}, 'active')"><i class="fas fa-user-slash"></i></button>
                                @else
                                <button class="btn btn-sm btn-outline-success" title="Activate" onclick="toggleUserStatus({{ $user->id }}, 'inactive')"><i class="fas fa-user-check"></i></button>
                                @endif
                            @endif
                            @if($user->status === 'archived')
                            <button class="btn btn-sm btn-outline-success" title="Unarchive" onclick="unarchiveUser({{ $user->id }})"><i class="fas fa-box-open"></i></button>
                            @else
                            <button class="btn btn-sm btn-outline-warning" title="Archive" onclick="archiveUser({{ $user->id }})"><i class="fas fa-archive"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 px-md-6 py-4 text-center text-muted">No users found</td>
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

                    <!-- Row 0: Role -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
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

                    <!-- Row 1: Farmer Registration (Farmer only) -->
                    <div id="farmerRegistrationGroup">
                        <div class="alert alert-light border d-flex align-items-center gap-2 mb-3">
                            <i class="fas fa-info-circle text-primary"></i>
                            <span>This creates the membership record <strong>and</strong> its login account immediately — no separate review step. Use this for a farmer you're registering in person.</span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control form-control-lg" placeholder="First name" id="farmerFirstNameInput" required>
                                <div class="invalid-feedback">Please enter first name.</div>
                            </div>
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">M.I.</label>
                                <input type="text" name="middle_initial" class="form-control form-control-lg" placeholder="M.I." maxlength="2" id="farmerMiddleInitialInput">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control form-control-lg" placeholder="Last name" id="farmerLastNameInput" required>
                                <div class="invalid-feedback">Please enter last name.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Suffix</label>
                                <select name="suffix" class="form-select form-select-lg" id="farmerSuffixInput">
                                    <option value="" selected>None</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                    <option value="V">V</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                                <input type="tel" name="contact_number" class="form-control form-control-lg ph-contact-input" placeholder="09123456789" pattern="(09\d{9}|\+639\d{9})" inputmode="numeric" maxlength="13" id="farmerContactInput" required>
                                <div class="invalid-feedback">Please enter a valid Philippine mobile number (e.g. 09123456789).</div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Province <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" value="South Cotabato" readonly>
                                <input type="hidden" name="province" value="South Cotabato">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Municipality <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" id="municipalitySelect" name="municipality" required>
                                    <option value="" selected disabled>Select Municipality</option>
                                </select>
                                <div class="invalid-feedback">Please select municipality.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Barangay</label>
                                <select class="form-select form-select-lg d-none" id="barangayNameSelect" name="barangay" disabled>
                                    <option value="" selected>Select Barangay</option>
                                </select>
                                <input type="text" class="form-control form-control-lg" id="barangayNameInput" name="barangay" placeholder="Enter barangay">
                                <div class="form-text">A barangay list is only available for Surallah and Banga; other municipalities can be typed in.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Crop Type <span class="text-danger">*</span></label>
                                <div class="border rounded-3 p-2" id="cropCheckboxGroup">
                                    @foreach($crops as $crop)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="crop_ids[]" value="{{ $crop->id }}" id="newCrop{{ $crop->id }}">
                                            <label class="form-check-label" for="newCrop{{ $crop->id }}">{{ $crop->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-danger small mt-1" id="cropCheckboxError" style="display:none;">Please select at least one crop type.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Land Area (hectares) <span class="text-danger">*</span></label>
                                <input type="number" name="land_area" step="0.1" min="0" class="form-control form-control-lg" placeholder="0.0" id="farmerLandAreaInput" required>
                                <div class="invalid-feedback">Please enter land area.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Upload Valid ID <span class="text-danger">*</span></label>
                                <div class="border border-2 borderdashed rounded-3 p-4 text-center bg-light hover:bg-white transition cursor-pointer" id="documentDropZone">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-muted mb-3 d-block"></i>
                                    <p class="fw-medium text-dark mb-1">Click to upload or drag and drop</p>
                                    <p class="text-muted small mb-0">PDF, JPG, or PNG (Max 10MB)</p>
                                    <input type="file" name="documents" id="documentsInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div id="documentFileName" class="mt-2 text-success fw-medium"></div>
                                </div>
                                <div class="invalid-feedback">Please upload required documents.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Upload Certificate of Title <span class="text-danger">*</span></label>
                                <div class="border border-2 borderdashed rounded-3 p-4 text-center bg-light hover:bg-white transition cursor-pointer" id="certificateDropZone">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-muted mb-3 d-block"></i>
                                    <p class="fw-medium text-dark mb-1">Click to upload or drag and drop</p>
                                    <p class="text-muted small mb-0">PDF, JPG, or PNG (Max 10MB)</p>
                                    <input type="file" name="certificate_of_title" id="certificateInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div id="certificateFileName" class="mt-2 text-success fw-medium"></div>
                                </div>
                                <div class="invalid-feedback">Please upload the certificate of title.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Upload Barangay Certification of Land Possession <span class="text-danger">*</span></label>
                                <div class="border border-2 borderdashed rounded-3 p-4 text-center bg-light hover:bg-white transition cursor-pointer" id="barangayDropZone">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-muted mb-3 d-block"></i>
                                    <p class="fw-medium text-dark mb-1">Click to upload or drag and drop</p>
                                    <p class="text-muted small mb-0">PDF, JPG, or PNG (Max 10MB)</p>
                                    <input type="file" name="barangay_certification" id="barangayInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div id="barangayFileName" class="mt-2 text-success fw-medium"></div>
                                </div>
                                <div class="invalid-feedback">Please upload the barangay certification of land possession.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Upload RSBSA Number/ID <span class="text-danger">*</span></label>
                                <div class="border border-2 borderdashed rounded-3 p-4 text-center bg-light hover:bg-white transition cursor-pointer" id="rsbsaDropZone">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-muted mb-3 d-block"></i>
                                    <p class="fw-medium text-dark mb-1">Click to upload or drag and drop</p>
                                    <p class="text-muted small mb-0">PDF, JPG, or PNG (Max 10MB)</p>
                                    <input type="file" name="rsbsa" id="rsbsaInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div id="rsbsaFileName" class="mt-2 text-success fw-medium"></div>
                                </div>
                                <div class="invalid-feedback">Please upload the RSBSA number/ID.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1b: Staff profile (Admin/Manager only) -->
                    <div class="row mb-3 d-none" id="staffFieldsGroup">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="First name" name="first_name" id="firstNameInput" autocomplete="off" required>
                            <div class="invalid-feedback">Please enter first name.</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Middle Name</label>
                            <input type="text" class="form-control form-control-lg" placeholder="Middle name" name="middle_name" id="middleNameInput" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="Last name" name="last_name" id="lastNameInput" autocomplete="off" required>
                            <div class="invalid-feedback">Please enter last name.</div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" class="form-control form-control-lg" name="date_of_birth" id="dobInput">
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label fw-semibold">Gender</label>
                            <select class="form-select form-select-lg" name="gender" id="genderInput">
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label fw-semibold">Profile Picture</label>
                            <input type="file" class="form-control form-control-lg" name="profile_picture" id="profilePictureInput" accept="image/*">
                        </div>
                    </div>

                    <!-- Row 2-4: Username, Email, Phone, Password (Admin/Manager only — Farmer credentials are auto-generated) -->
                    <div id="staffCredentialsGroup" class="d-none">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">@</span>
                                    <input type="text" class="form-control" placeholder="username" name="username" id="usernameInput" required>
                                    <div class="invalid-feedback">Please enter username.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-lg" placeholder="email@example.com" name="email" id="emailInput" required>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control form-control-lg" placeholder="0912-345-6789" name="Phonenumber" id="phoneInput">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <input type="password" class="form-control" placeholder="Enter password" name="password" id="staffPasswordInput" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter password.</div>
                            </div>
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
                        <span class="text-muted small"><i class="fas fa-id-badge text-primary me-2 fa-fw"></i>Role</span>
                        <span class="fw-semibold text-end" id="confirmAccRole">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-user text-primary me-2 fa-fw"></i>Full Name</span>
                        <span class="fw-semibold text-end" id="confirmAccName">-</span>
                    </div>

                    <!-- Admin/Manager rows -->
                    <div id="confirmStaffRows">
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

                    <!-- Farmer rows -->
                    <div id="confirmFarmerRows" class="d-none">
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <span class="text-muted small"><i class="fas fa-seedling text-primary me-2 fa-fw"></i>Crop Type</span>
                            <span class="fw-semibold text-end" id="confirmAccCropType">-</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <span class="text-muted small"><i class="fas fa-ruler-combined text-primary me-2 fa-fw"></i>Land Area</span>
                            <span class="fw-semibold text-end" id="confirmAccLandArea">-</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <span class="text-muted small"><i class="fas fa-phone text-primary me-2 fa-fw"></i>Contact</span>
                            <span class="fw-semibold text-end" id="confirmAccContact">-</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between py-2">
                            <span class="text-muted small"><i class="fas fa-map-marker-alt text-primary me-2 fa-fw"></i>Location</span>
                            <span class="fw-semibold text-end" id="confirmAccLocation">-</span>
                        </div>
                    </div>
                </div>

                <div class="info-banner variant-info mt-3 mb-4" id="confirmAutoAccountNote" style="display: none;">
                    <i class="fas fa-bolt mt-1"></i>
                    <p class="small mb-0">This account is created and approved immediately — a username and temporary password will be generated automatically for you to share with the farmer.</p>
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

<!-- Account Created Result Modal (Farmer accounts only) -->
<div class="modal fade" id="accountCreatedModal" tabindex="-1" aria-labelledby="accountCreatedModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-body px-4 pb-4 pt-4 text-center">
                <div class="notify-icon-badge success d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="fw-bold mb-1" id="accountCreatedModalLabel">Farmer Account Created</h5>
                <p class="text-muted small mb-4">Share these login details with <strong id="createdAccountName">-</strong> directly.</p>

                <div class="confirm-account-summary border rounded-3 px-3 text-start mb-4">
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-at text-primary me-2 fa-fw"></i>Username</span>
                        <span class="fw-semibold text-end" id="createdAccountUsername">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2">
                        <span class="text-muted small"><i class="fas fa-key text-primary me-2 fa-fw"></i>Temporary Password</span>
                        <span class="fw-semibold text-end" id="createdAccountPassword">-</span>
                    </div>
                </div>

                <button type="button" class="btn btn-primary w-100 py-2" id="accountCreatedDoneBtn">
                    <i class="fas fa-check me-2"></i>Done
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
                    <div class="col-md-6 d-none" id="viewDobGroup">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Date of Birth / Age</small>
                            <strong id="viewDob">-</strong>
                        </div>
                    </div>
                    <div class="col-md-6 d-none" id="viewGenderGroup">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Gender</small>
                            <strong id="viewGender">-</strong>
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

                    <!-- Row 0: Role -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
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

                    <!-- Row 1: Full Name (Farmer only) -->
                    <div class="row mb-3" id="editFarmerNameGroup">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="Enter full name" name="name" id="editName">
                            <div class="invalid-feedback">Please enter full name.</div>
                        </div>
                    </div>

                    <!-- Row 1b: Staff profile (Admin/Manager only) -->
                    <div class="row mb-3 d-none" id="editStaffFieldsGroup">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="First name" name="first_name" id="editFirstName">
                            <div class="invalid-feedback">Please enter first name.</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Middle Name</label>
                            <input type="text" class="form-control form-control-lg" placeholder="Middle name" name="middle_name" id="editMiddleName">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" placeholder="Last name" name="last_name" id="editLastName">
                            <div class="invalid-feedback">Please enter last name.</div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" class="form-control form-control-lg" name="date_of_birth" id="editDob">
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label fw-semibold">Gender</label>
                            <select class="form-select form-select-lg" name="gender" id="editGender">
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label fw-semibold">Profile Picture</label>
                            <input type="file" class="form-control form-control-lg" name="profile_picture" id="editProfilePicture" accept="image/*">
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

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
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

    var phoneInput = document.getElementById('phoneInput');
    var usernameInput = document.getElementById('usernameInput');
    var emailInput = document.getElementById('emailInput');
    var accountTypeLabel = document.getElementById('accountTypeLabel');
    var farmerRegistrationGroup = document.getElementById('farmerRegistrationGroup');
    var staffFieldsGroup = document.getElementById('staffFieldsGroup');
    var staffCredentialsGroup = document.getElementById('staffCredentialsGroup');
    var firstNameInput = document.getElementById('firstNameInput');
    var lastNameInput = document.getElementById('lastNameInput');

    function getSelectedRole() {
        var checked = document.querySelector('#userForm input[name="roleID"]:checked');
        return checked ? checked.value : '3';
    }

    // Disabled fields are skipped by both form submission (FormData) and
    // constraint validation (checkValidity), so hiding a group's fields and
    // disabling them together is enough to fully exclude it — including
    // avoiding name collisions with the other group's identically-named
    // fields (both use first_name/last_name, for instance).
    function setGroupEnabled(groupEl, enabled) {
        groupEl.classList.toggle('d-none', !enabled);
        groupEl.querySelectorAll('input, select, textarea').forEach(function(field) {
            field.disabled = !enabled;
        });
    }

    // Farmer accounts fill in the full registration form (like Membership
    // Registration) and get their login auto-provisioned; Admin/Manager fill
    // in a Staff profile plus manually-chosen username/email/password instead.
    function applyRoleRules() {
        var role = getSelectedRole();
        var isFarmer = role === '3';

        accountTypeLabel.textContent = ROLE_LABELS[role];

        setGroupEnabled(farmerRegistrationGroup, isFarmer);
        setGroupEnabled(staffFieldsGroup, !isFarmer);
        setGroupEnabled(staffCredentialsGroup, !isFarmer);
    }

    document.querySelectorAll('#userForm input[name="roleID"]').forEach(function(input) {
        input.addEventListener('change', applyRoleRules);
    });
    applyRoleRules();

    document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
        resetCreateForm();
    });

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

    // Opens the Change Password modal for a Manager or Farmer account.
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

    // Submits an admin-initiated password reset for a Manager or Farmer
    // account. Admin accounts are never targetable here — enforced server-side too.
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

        fetch('/admin/user-management/' + userId + '/change-password', {
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

    // Mindanao Provinces and Municipalities Data (mirrors Membership Registration)
    const municipalitiesData = {
        'South Cotabato': [
            'Banga', 'Bangian', 'Bagumpasig', 'Banon', 'Buayan', 'Buli', 'Caloocan', 'Datu Saliao', 'General Santos City', 'Koronadal City', 'Lake Sebu', 'Lambayong', 'Mabini', 'Magsaysay', 'Maiha', 'Malapatan', 'Malungon', 'Norala', 'Polomolok', 'Santo Nino', 'Surallah', 'T\'Boli', 'Tampakan', 'Tantangan', 'Tupi'
        ]
    };

    // Barangay lists — only populated for Surallah and Banga (South Cotabato) for now.
    const barangaysData = {
        'Surallah': [
            'Buenavista', 'Canahay', 'Centrala', 'Colongulo', 'Dajay', 'Duengas', 'Lambontong', 'Lamian', 'Lamsugod', 'Libertad (Poblacion)', 'Little Baguio', 'Moloy', 'Naci', 'Talahik', 'Tubiala', 'Upper Sepaka', 'Veterans'
        ],
        'Banga': [
            'Benitez (Poblacion)', 'Cabudian', 'Cabuling', 'Cinco', 'Derilon', 'El Nonok', 'Improgo Village (Poblacion)', 'Kusan', 'Lam-apos', 'Lamba', 'Lambingi', 'Lampari', 'Liwanay', 'Malaya', 'Punong Grande', 'Rang-ay', 'Reyes (Poblacion)', 'Rizal', 'Rizal Poblacion', 'San Jose', 'San Vicente', 'Yangco Poblacion'
        ]
    };

    (function() {
        const municipalitySelect = document.getElementById('municipalitySelect');
        municipalitiesData['South Cotabato'].forEach(function(municipality) {
            const option = document.createElement('option');
            option.value = municipality;
            option.textContent = municipality;
            municipalitySelect.appendChild(option);
        });
    })();

    document.getElementById('municipalitySelect').addEventListener('change', function() {
        const selectedMunicipality = this.value;
        const barangaySelect = document.getElementById('barangayNameSelect');
        const barangayInput = document.getElementById('barangayNameInput');

        barangaySelect.innerHTML = '<option value="" selected>Select Barangay</option>';
        barangayInput.value = '';

        if (selectedMunicipality && barangaysData[selectedMunicipality]) {
            barangaysData[selectedMunicipality].forEach(function(barangay) {
                const option = document.createElement('option');
                option.value = barangay;
                option.textContent = barangay;
                barangaySelect.appendChild(option);
            });

            barangaySelect.classList.remove('d-none');
            barangaySelect.disabled = false;
            barangayInput.classList.add('d-none');
            barangayInput.disabled = true;
        } else {
            barangaySelect.classList.add('d-none');
            barangaySelect.disabled = true;
            barangayInput.classList.remove('d-none');
            barangayInput.disabled = false;
        }
    });

    function wireDocumentDropZone(dropZoneId, inputId, fileNameId) {
        const dropZone = document.getElementById(dropZoneId);
        const input = document.getElementById(inputId);
        const fileNameDisplay = document.getElementById(fileNameId);

        dropZone.addEventListener('click', function() {
            input.click();
        });

        input.addEventListener('change', function(event) {
            const file = event.target.files[0];
            fileNameDisplay.innerHTML = file ? '<i class="fas fa-file"></i> ' + file.name : '';
        });

        dropZone.addEventListener('dragover', function(event) {
            event.preventDefault();
            dropZone.classList.add('bg-white', 'border-success');
        });

        dropZone.addEventListener('dragleave', function(event) {
            event.preventDefault();
            dropZone.classList.remove('bg-white', 'border-success');
        });

        dropZone.addEventListener('drop', function(event) {
            event.preventDefault();
            dropZone.classList.remove('bg-white', 'border-success');
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                fileNameDisplay.innerHTML = '<i class="fas fa-file"></i> ' + files[0].name;
            }
        });
    }

    wireDocumentDropZone('documentDropZone', 'documentsInput', 'documentFileName');
    wireDocumentDropZone('certificateDropZone', 'certificateInput', 'certificateFileName');
    wireDocumentDropZone('barangayDropZone', 'barangayInput', 'barangayFileName');
    wireDocumentDropZone('rsbsaDropZone', 'rsbsaInput', 'rsbsaFileName');

    document.querySelectorAll('.ph-contact-input').forEach(function(input) {
        input.addEventListener('input', function() {
            let value = input.value.replace(/[^\d+]/g, '');
            if (value.indexOf('+') > 0) {
                value = value.replace(/\+/g, '');
            }
            input.value = value;
        });
    });

    function resetCreateForm() {
        var form = document.getElementById('userForm');
        form.reset();
        form.classList.remove('was-validated');
        document.getElementById('cropCheckboxError').style.display = 'none';
        ['documentFileName', 'certificateFileName', 'barangayFileName', 'rsbsaFileName'].forEach(function(id) {
            document.getElementById(id).textContent = '';
        });

        const municipalitySelect = document.getElementById('municipalitySelect');
        municipalitySelect.innerHTML = '<option value="" selected disabled>Select Municipality</option>';
        municipalitiesData['South Cotabato'].forEach(function(municipality) {
            const option = document.createElement('option');
            option.value = municipality;
            option.textContent = municipality;
            municipalitySelect.appendChild(option);
        });

        const barangaySelect = document.getElementById('barangayNameSelect');
        const barangayInput = document.getElementById('barangayNameInput');
        barangaySelect.innerHTML = '<option value="" selected>Select Barangay</option>';
        barangaySelect.classList.add('d-none');
        barangaySelect.disabled = true;
        barangayInput.classList.remove('d-none');
        barangayInput.disabled = false;

        applyRoleRules();
    }

    // Validates the create-account form and, if valid, shows a review step
    // before the account is actually created. Farmer accounts get a review
    // of the registration details; Admin/Manager get the existing summary.
    function openCreateAccountConfirm() {
        var form = document.getElementById('userForm');
        var role = getSelectedRole();
        var isFarmer = role === '3';

        document.getElementById('confirmAccRole').textContent = ROLE_LABELS[role];

        if (isFarmer) {
            var checkedCrops = form.querySelectorAll('input[name="crop_ids[]"]:checked');
            var cropError = document.getElementById('cropCheckboxError');

            if (!form.checkValidity() || checkedCrops.length === 0) {
                form.classList.add('was-validated');
                cropError.style.display = checkedCrops.length === 0 ? 'block' : 'none';
                return;
            }
            cropError.style.display = 'none';

            var firstName = document.getElementById('farmerFirstNameInput').value.trim();
            var middleInitial = document.getElementById('farmerMiddleInitialInput').value.trim();
            var lastName = document.getElementById('farmerLastNameInput').value.trim();
            var suffix = document.getElementById('farmerSuffixInput').value;
            var contactNumber = document.getElementById('farmerContactInput').value.trim();
            var landArea = document.getElementById('farmerLandAreaInput').value;
            var municipality = document.getElementById('municipalitySelect').value;
            var cropNames = Array.prototype.map.call(checkedCrops, function(checkbox) {
                return checkbox.nextElementSibling.textContent;
            }).join(', ');

            var fullName = firstName;
            if (middleInitial) fullName += ' ' + middleInitial;
            fullName += ' ' + lastName;
            if (suffix) fullName += ' ' + suffix;

            document.getElementById('confirmAccName').textContent = fullName;
            document.getElementById('confirmAccCropType').textContent = cropNames;
            document.getElementById('confirmAccLandArea').textContent = landArea + ' hectares';
            document.getElementById('confirmAccContact').textContent = contactNumber;
            document.getElementById('confirmAccLocation').textContent = municipality + ', South Cotabato';

            document.getElementById('confirmStaffRows').classList.add('d-none');
            document.getElementById('confirmFarmerRows').classList.remove('d-none');
            document.getElementById('confirmAutoAccountNote').style.display = 'flex';
        } else {
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            var displayName = [firstNameInput.value, document.getElementById('middleNameInput').value, lastNameInput.value].filter(Boolean).join(' ').trim();

            document.getElementById('confirmAccName').textContent = displayName;
            document.getElementById('confirmAccUsername').textContent = '@' + usernameInput.value.trim();
            document.getElementById('confirmAccEmail').textContent = emailInput.value.trim();
            document.getElementById('confirmAccPhone').textContent = phoneInput.value.trim() || 'Not provided';

            document.getElementById('confirmStaffRows').classList.remove('d-none');
            document.getElementById('confirmFarmerRows').classList.add('d-none');
            document.getElementById('confirmAutoAccountNote').style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('confirmCreateModal')).show();
    }

    document.getElementById('confirmCreateAccountBtn').addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('confirmCreateModal')).hide();
        submitUserForm();
    });

    // Form submission function — for Farmer this also provisions the login
    // account automatically, so the response carries a generated username/password.
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

                if (data.username && data.password) {
                    document.getElementById('createdAccountName').textContent = data.name;
                    document.getElementById('createdAccountUsername').textContent = data.username;
                    document.getElementById('createdAccountPassword').textContent = data.password;
                    new bootstrap.Modal(document.getElementById('accountCreatedModal')).show();
                } else {
                    showAlert(data.message, 'success', function() {
                        window.location.reload();
                    });
                }
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

    document.getElementById('accountCreatedDoneBtn').addEventListener('click', function() {
        window.location.reload();
    });

    // Search and Filter Functionality — filters the full user list on the
    // server (not just the rows on the current page).
    var searchInput = document.getElementById('searchInput');
    var searchIcon = document.getElementById('searchIcon');
    var searchClearBtn = document.getElementById('searchClearBtn');
    var roleFilter = document.getElementById('roleFilter');
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

        if (roleFilter.value) {
            currentUrl.searchParams.set('role', roleFilter.value);
        } else {
            currentUrl.searchParams.delete('role');
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

    roleFilter.addEventListener('change', applyUserFilters);
    statusFilter.addEventListener('change', applyUserFilters);

    // Restore focus (with cursor at the end) after a search reloads the page,
    // so typing feels continuous instead of resetting to the top of the page.
    searchInput.focus();
    if (searchInput.value) {
        var restoredValue = searchInput.value;
        searchInput.value = '';
        searchInput.value = restoredValue;
    }

    // Activate / Deactivate Account Function (Manager and Farmer accounts only)
    function toggleUserStatus(userId, currentStatus) {
        var confirmMessage = currentStatus === 'active'
            ? 'Deactivate this account? The user will no longer be able to log in.'
            : 'Activate this account? The user will be able to log in again.';

        if (confirm(confirmMessage)) {
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            fetch('/admin/user-management/' + userId + '/toggle-status', {
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

    // Unarchive User Function
    function unarchiveUser(userId) {
        if (confirm('Restore this account from the archive?')) {
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');

            fetch('/admin/user-management/' + userId + '/unarchive', {
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
                var staff = user.staff || null;

                document.getElementById('viewAvatar').textContent = user.name.substring(0, 2).toUpperCase();
                document.getElementById('viewName').textContent = user.name;
                document.getElementById('viewUsername').textContent = '@' + user.username;
                document.getElementById('viewEmail').textContent = user.email || 'N/A';
                document.getElementById('viewPhone').textContent = user.Phonenumber || 'N/A';
                document.getElementById('viewRole').textContent = ROLE_LABELS[String(user.roleID)] || 'Farmer';
                document.getElementById('viewStatus').textContent = user.status.charAt(0).toUpperCase() + user.status.slice(1);
                document.getElementById('viewUserId').textContent = 'USR-' + String(user.id).padStart(3, '0');
                document.getElementById('viewCreatedAt').textContent = new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

                var dobGroup = document.getElementById('viewDobGroup');
                var genderGroup = document.getElementById('viewGenderGroup');

                if (staff && staff.date_of_birth) {
                    var dobLabel = new Date(staff.date_of_birth).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                    document.getElementById('viewDob').textContent = dobLabel + (staff.age !== null && staff.age !== undefined ? ' (' + staff.age + ' yrs)' : '');
                    dobGroup.classList.remove('d-none');
                } else {
                    dobGroup.classList.add('d-none');
                }

                if (staff && staff.gender) {
                    document.getElementById('viewGender').textContent = staff.gender;
                    genderGroup.classList.remove('d-none');
                } else {
                    genderGroup.classList.add('d-none');
                }

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

    // Applies the email-required rule and Staff/Farmer field toggle to the Edit form.
    function applyEditRoleRules() {
        var checked = document.querySelector('#editUserForm input[name="roleID"]:checked');
        var role = checked ? checked.value : '3';
        var isFarmer = role === '3';
        var editEmail = document.getElementById('editEmail');
        var mark = document.getElementById('editEmailRequiredMark');

        editEmail.required = !isFarmer;
        mark.textContent = isFarmer ? '(optional)' : '*';
        mark.className = isFarmer ? 'text-muted' : 'text-danger';

        document.getElementById('editFarmerNameGroup').classList.toggle('d-none', !isFarmer);
        document.getElementById('editStaffFieldsGroup').classList.toggle('d-none', isFarmer);
        document.getElementById('editName').required = isFarmer;
        document.getElementById('editFirstName').required = !isFarmer;
        document.getElementById('editLastName').required = !isFarmer;
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

                var staff = user.staff || {};
                document.getElementById('editFirstName').value = staff.first_name || '';
                document.getElementById('editMiddleName').value = staff.middle_name || '';
                document.getElementById('editLastName').value = staff.last_name || '';
                document.getElementById('editDob').value = staff.date_of_birth ? staff.date_of_birth.substring(0, 10) : '';
                document.getElementById('editGender').value = staff.gender || '';
                document.getElementById('editProfilePicture').value = '';

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
