@extends('manager.layout')

@section('title', 'Farmer Profile Management')
@section('header', 'Farmer Profile Management')

@section('content')
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search farmers..." class="form-control ps-5" style="min-width: 240px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="locked">Locked</option>
                <option value="archived">Archived</option>
            </select>
        </x-slot:filters>
        <x-slot:actions>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="fas fa-plus"></i><span>Register Farmer</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Membership Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Contact</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Crop Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Land Area</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Account</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex align-items-center gap-3">
                            <x-avatar-initials name="Juan Dela Cruz" color="primary" />
                            <div>
                                <p class="mb-0 fw-medium text-dark">Juan Dela Cruz</p>
                                <p class="mb-0 text-muted small">Member ID: FM-001</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">0912-345-6789</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Rice</td>
                    <td class="px-4 px-md-6 py-4 text-muted">2.5 ha</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Active" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-lock" color="secondary" title="Lock" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex align-items-center gap-3">
                            <x-avatar-initials name="Maria Santos" color="info" />
                            <div>
                                <p class="mb-0 fw-medium text-dark">Maria Santos</p>
                                <p class="mb-0 text-muted small">Member ID: FM-002</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">0913-456-7890</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Corn</td>
                    <td class="px-4 px-md-6 py-4 text-muted">1.8 ha</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Locked" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-unlock" color="success" title="Unlock" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex align-items-center gap-3">
                            <x-avatar-initials name="Pedro Reyes" color="secondary" />
                            <div>
                                <p class="mb-0 fw-medium text-dark">Pedro Reyes</p>
                                <p class="mb-0 text-muted small">Member ID: FM-003</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Archived" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">0914-567-8901</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Vegetables</td>
                    <td class="px-4 px-md-6 py-4 text-muted">1.2 ha</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Inactive" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal" />
                            <x-icon-button icon="fa-edit" color="secondary" title="Edit" disabled />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-10 of 156 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>

<!-- View Modal -->
<x-modal id="viewModal" title="Farmer Profile Details" size="modal-lg modal-dialog-scrollable">
    <div class="mb-4">
        <h4 class="text-sm fw-semibold text-dark mb-3">Personal Information</h4>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="text-muted small d-block">Full Name</label>
                <p class="fw-medium mb-0">Juan Dela Cruz</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">Date of Birth</label>
                <p class="fw-medium mb-0">January 15, 1985</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">Contact Number</label>
                <p class="fw-medium mb-0">0912-345-6789</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">Barangay</label>
                <p class="fw-medium mb-0">Brgy. San Jose</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">Email</label>
                <p class="fw-medium mb-0">juan.delacruz@email.com</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">Member ID</label>
                <p class="fw-medium mb-0">FM-001</p>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h4 class="text-sm fw-semibold text-dark mb-3">Farming Information</h4>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="text-muted small d-block">Crop Type</label>
                <p class="fw-medium mb-0">Rice</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">Land Area</label>
                <p class="fw-medium mb-0">2.5 hectares</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">Farm Location</label>
                <p class="fw-medium mb-0">Brgy. San Jose</p>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h4 class="text-sm fw-semibold text-dark mb-3">Membership & Payment Details</h4>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="text-muted small d-block mb-1">Membership Status</label>
                <x-status-badge status="Approved" />
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">CBU Balance</label>
                <p class="fw-medium mb-0">₱12,500.00</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small d-block">Total Savings</label>
                <p class="fw-medium mb-0">₱45,000.00</p>
            </div>
        </div>
    </div>

    <div>
        <h4 class="text-sm fw-semibold text-dark mb-3">Login Credentials</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="text-muted small d-block">Username</label>
                <p class="fw-medium mb-0">juan.dc</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small d-block mb-1">Account Status</label>
                <x-status-badge status="Active" />
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </x-slot:footer>
</x-modal>
@endsection
