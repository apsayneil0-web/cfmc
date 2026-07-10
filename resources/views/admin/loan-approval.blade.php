@extends('admin.layout')

@section('title', 'Loan Approval')
@section('header', 'Farmer Loan Approval')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Pending Requests" value="3" icon="fa-hourglass-half" color="warning" />
    <x-stat-card label="Approved This Month" value="0" icon="fa-check-circle" color="success" />
    <x-stat-card label="Rejected This Month" value="0" icon="fa-times-circle" color="danger" />
</div>

<!-- Pending Loan Requests -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Pending Loan Requests</h3>
            <span class="text-sm text-muted">Validated by the Manager, awaiting final authorization</span>
        </x-slot:filters>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Requested Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Repayment Terms</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Application Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Membership Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">Juan Dela Cruz</td>
                    <td class="px-4 px-md-6 py-4">₱30,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Farm equipment purchase</td>
                    <td class="px-4 px-md-6 py-4 text-muted">6 months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 08, 2026</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Member" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-check" color="success" title="Approve" />
                            <x-icon-button icon="fa-times" color="danger" title="Reject" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4">₱50,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Seeds and fertilizer</td>
                    <td class="px-4 px-md-6 py-4 text-muted">12 months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 09, 2026</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Member" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-check" color="success" title="Approve" />
                            <x-icon-button icon="fa-times" color="danger" title="Reject" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">Roberto Tan</td>
                    <td class="px-4 px-md-6 py-4">₱20,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Irrigation repair</td>
                    <td class="px-4 px-md-6 py-4 text-muted">3 months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 10, 2026</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Non-member" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-check" color="success" title="Approve" />
                            <x-icon-button icon="fa-times" color="danger" title="Reject" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<x-info-banner variant="info" title="Loan Approval Workflow" class="mt-6">
    Loan requests are validated by the Manager before being forwarded here for the Administrator's final authorization. Approving or rejecting a request notifies the farmer and permanently logs the decision for monitoring purposes.
</x-info-banner>
@endsection
