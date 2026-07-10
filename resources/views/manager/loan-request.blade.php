@extends('manager.layout')

@section('title', 'Loan Requests')
@section('header', 'Loan Request Management')

@section('content')
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search requests..." class="form-control ps-5" style="min-width: 220px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </x-slot:filters>
        <x-slot:actions>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                <i class="fas fa-plus"></i><span>New Request</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Request ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Repayment Terms</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Collateral</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-001</td>
                    <td class="px-4 px-md-6 py-4">Juan Dela Cruz</td>
                    <td class="px-4 px-md-6 py-4">₱50,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Farming Equipment</td>
                    <td class="px-4 px-md-6 py-4 text-muted">12 months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Tractor</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Pending" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-check" color="success" title="Approve" />
                            <x-icon-button icon="fa-times" color="danger" title="Reject" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-002</td>
                    <td class="px-4 px-md-6 py-4">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4">₱30,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Seeds & Fertilizer</td>
                    <td class="px-4 px-md-6 py-4 text-muted">6 months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Land Title</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-check" color="secondary" title="Approve" disabled />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-003</td>
                    <td class="px-4 px-md-6 py-4">Pedro Reyes</td>
                    <td class="px-4 px-md-6 py-4">₱75,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Harvester Purchase</td>
                    <td class="px-4 px-md-6 py-4 text-muted">24 months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Property</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Rejected" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-10 of 12 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>

<!-- Create Request Modal -->
<x-modal id="createRequestModal" title="New Loan Request">
    <form id="loanRequestForm">
        <div class="mb-3">
            <label class="form-label fw-semibold">Farmer</label>
            <select class="form-select">
                <option value="">Select Farmer</option>
                <option>Juan Dela Cruz</option>
                <option>Maria Santos</option>
                <option>Pedro Reyes</option>
            </select>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <label class="form-label fw-semibold">Loan Amount</label>
                <input type="number" class="form-control" placeholder="₱0.00">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Purpose</label>
                <select class="form-select">
                    <option value="">Select Purpose</option>
                    <option>Farming Equipment</option>
                    <option>Seeds & Fertilizer</option>
                    <option>Machinery Purchase</option>
                    <option>Working Capital</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <label class="form-label fw-semibold">Repayment Terms</label>
                <select class="form-select">
                    <option value="">Select Terms</option>
                    <option>6 months</option>
                    <option>12 months</option>
                    <option>18 months</option>
                    <option>24 months</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Collateral</label>
                <input type="text" class="form-control" placeholder="Describe collateral">
            </div>
        </div>
        <div>
            <label class="form-label fw-semibold">Upload Documents</label>
            <div class="border border-2 borderdashed rounded-3 p-4 text-center bg-light">
                <i class="fas fa-cloud-upload-alt text-muted mb-1 d-block" style="font-size: 1.5rem;"></i>
                <p class="text-muted small mb-0">Upload supporting documents</p>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="loanRequestForm" class="btn btn-primary">Submit Request</button>
    </x-slot:footer>
</x-modal>
@endsection
