@extends('manager.layout')

@section('title', 'Complaints')
@section('header', 'Complaints Management')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Total Complaints" :value="$counts['total']" icon="fa-exclamation-circle" color="secondary" />
    <x-stat-card label="Submitted" :value="$counts['submitted']" icon="fa-envelope" color="primary" />
    <x-stat-card label="In Progress" :value="$counts['in_progress']" icon="fa-spinner" color="warning" />
    <x-stat-card label="Resolved" :value="$counts['resolved']" icon="fa-check-circle" color="success" />
</div>

<!-- Complaints Table -->
<div class="section-card">
    <form method="GET" action="{{ route('manager.complaints') }}">
        <x-table-toolbar>
            <x-slot:filters>
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search complaints..." class="form-control ps-5" style="min-width: 220px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="submitted" @selected(request('status') == 'submitted')>Submitted</option>
                    <option value="in_progress" @selected(request('status') == 'in_progress')>In Progress</option>
                    <option value="resolved" @selected(request('status') == 'resolved')>Resolved</option>
                </select>
            </x-slot:filters>
        </x-table-toolbar>
    </form>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Subject</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Description</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $complaint)
                <tr>
                    <td class="px-4 px-md-6 py-4">{{ $complaint->user->name ?? 'Unknown' }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $complaint->subject }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted text-truncate d-inline-block" style="max-width: 220px;">{{ $complaint->description }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $complaint->created_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucwords(str_replace('_', ' ', $complaint->status))" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="View / Respond" data-bs-toggle="modal" data-bs-target="#respondModal{{ $complaint->id }}"><i class="fas fa-eye"></i></button>
                        </div>
                    </td>
                </tr>

                <!-- Respond Modal -->
                <div class="modal fade" id="respondModal{{ $complaint->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i>Complaint Details</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.complaints.respond', $complaint->id) }}" method="POST" class="modal-form-flex">
                                @csrf
                                @method('PATCH')
                                <div class="modal-body">
                                    <label class="text-muted small">Farmer</label>
                                    <p class="fw-semibold">{{ $complaint->user->name ?? 'Unknown' }}</p>
                                    <label class="text-muted small">Subject</label>
                                    <p class="fw-semibold">{{ $complaint->subject }}</p>
                                    <label class="text-muted small">Description</label>
                                    <p>{{ $complaint->description }}</p>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="in_progress" @selected($complaint->status == 'in_progress')>In Progress</option>
                                            <option value="resolved" @selected($complaint->status == 'resolved')>Resolved</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Response to Farmer</label>
                                        <textarea name="manager_response" class="form-control" rows="4" placeholder="Explain the action taken...">{{ $complaint->manager_response }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Response</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No complaints submitted yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
