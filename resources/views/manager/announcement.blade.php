@extends('manager.layout')

@section('title', 'Announcements')
@section('header', 'Announcement Management')

@section('content')
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search announcements..." class="form-control ps-5" style="min-width: 220px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Status</option>
                <option>Published</option>
                <option>Draft</option>
                <option>Archived</option>
            </select>
        </x-slot:filters>
        <x-slot:actions>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#announcementModal">
                <i class="fas fa-plus"></i><span>Create Announcement</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Title</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Selected Farmers</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <p class="mb-0 fw-medium text-dark">Schedule Maintenance Notice</p>
                        <p class="mb-0 text-muted small">Harvester unit under maintenance</p>
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">Information</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">All Members</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Published" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#announcementModal" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <p class="mb-0 fw-medium text-dark">Membership Renewal</p>
                        <p class="mb-0 text-muted small">Annual membership renewal reminder</p>
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">Reminder</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 05, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">All Members</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Published" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#announcementModal" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <p class="mb-0 fw-medium text-dark">New Equipment Available</p>
                        <p class="mb-0 text-muted small">Latest tractor models now available</p>
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">Announcement</td>
                    <td class="px-4 px-md-6 py-4 text-muted">-</td>
                    <td class="px-4 px-md-6 py-4 text-muted">-</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Draft" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#announcementModal" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <p class="mb-0 fw-medium text-dark">Upcoming Training</p>
                        <p class="mb-0 text-muted small">Farmer training program</p>
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">Event</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jun 15, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Selected (25)</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Archived" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-4 of 4 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>

<!-- Create/Edit Announcement Modal -->
<x-modal id="announcementModal" title="Create Announcement" size="modal-dialog-scrollable">
    <form id="announcementForm">
        <div class="mb-3">
            <label class="form-label fw-semibold">Title</label>
            <input type="text" class="form-control" placeholder="Enter title">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea rows="3" class="form-control" placeholder="Enter description"></textarea>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <label class="form-label fw-semibold">Purpose</label>
                <select class="form-select">
                    <option>Information</option>
                    <option>Reminder</option>
                    <option>Event</option>
                    <option>Announcement</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date</label>
                <input type="date" class="form-control">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Resolution (Optional)</label>
            <textarea rows="2" class="form-control" placeholder="Enter resolution details"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Select Farmers</label>
            <select class="form-select">
                <option>All Members</option>
                <option>Selected Farmers</option>
            </select>
        </div>
        <div>
            <label class="form-label fw-semibold">Status</label>
            <select class="form-select">
                <option>Draft</option>
                <option>Published</option>
            </select>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="announcementForm" class="btn btn-primary">Save</button>
    </x-slot:footer>
</x-modal>
@endsection
