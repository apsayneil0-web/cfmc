@extends('manager.layout')

@section('title', 'Announcements')
@section('header', 'Announcement Management')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <form method="GET" action="{{ route('manager.announcement') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search announcements..." class="form-control ps-5" style="min-width: 220px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('manager.announcement') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
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
                @forelse($announcements as $announcement)
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <p class="mb-0 fw-medium text-dark">{{ $announcement->title }}</p>
                        <p class="mb-0 text-muted small">{{ \Illuminate\Support\Str::limit($announcement->description, 60) }}</p>
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $announcement->purpose }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $announcement->announcement_date?->format('M d, Y') ?? '-' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $announcement->audience_label }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($announcement->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $announcement->id }}" />
                            @if($announcement->status !== 'archived')
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $announcement->id }}" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $announcement->id }}" />
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- View Modal -->
                <x-modal id="viewModal{{ $announcement->id }}" title="Announcement Details">
                    <div class="row g-3">
                        <div class="col-12"><label class="text-muted small d-block">Title</label><p class="fw-medium mb-0">{{ $announcement->title }}</p></div>
                        <div class="col-12"><label class="text-muted small d-block">Description</label><p class="fw-medium mb-0">{{ $announcement->description ?? '—' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Purpose</label><p class="fw-medium mb-0">{{ $announcement->purpose }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Date</label><p class="fw-medium mb-0">{{ $announcement->announcement_date?->format('M d, Y') ?? '—' }}</p></div>
                        @if($announcement->time)
                        <div class="col-6"><label class="text-muted small d-block">Time</label><p class="fw-medium mb-0">🕘 {{ \Carbon\Carbon::parse($announcement->time)->format('g:i A') }}</p></div>
                        @endif
                        @if($announcement->location)
                        <div class="col-6"><label class="text-muted small d-block">Location</label><p class="fw-medium mb-0">📍 {{ $announcement->location }}</p></div>
                        @endif
                        <div class="col-6"><label class="text-muted small d-block">Audience</label><p class="fw-medium mb-0">{{ $announcement->audience_label }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="ucfirst($announcement->status)" /></div>
                        @if($announcement->resolution)
                        <div class="col-12"><label class="text-muted small d-block">Resolution</label><p class="fw-medium mb-0">{{ $announcement->resolution }}</p></div>
                        @endif
                        <div class="col-6"><label class="text-muted small d-block">Created By</label><p class="fw-medium mb-0">{{ $announcement->creator->name ?? '—' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Created</label><p class="fw-medium mb-0">{{ $announcement->created_at->format('M d, Y') }}</p></div>
                    </div>
                </x-modal>

                @if($announcement->status !== 'archived')
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $announcement->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit me-2"></i>Edit Announcement</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.announcement.update', $announcement) }}" method="POST" class="modal-form-flex">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    @include('manager.partials.announcement-form-fields', ['prefix' => 'edit'.$announcement->id, 'announcement' => $announcement, 'farmers' => $farmers, 'purposes' => $purposes])
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Archive Modal -->
                <div class="modal fade" id="archiveModal{{ $announcement->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-archive me-2"></i>Archive Announcement</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Archive "{{ $announcement->title }}"? It will be removed from the active list and notification bell, but kept for records.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.announcement.archive', $announcement) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary">Archive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No announcements found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Announcement Modal -->
<div class="modal fade" id="createAnnouncementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-bullhorn me-2"></i>Create Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.announcement.store') }}" method="POST" class="modal-form-flex">
                @csrf
                <div class="modal-body">
                    @include('manager.partials.announcement-form-fields', ['prefix' => 'create', 'announcement' => null, 'farmers' => $farmers, 'purposes' => $purposes])
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.announcement-form').forEach(function (form) {
        var audienceSelect = form.querySelector('.audience-select');
        var farmerPicker = form.querySelector('.farmer-picker');

        audienceSelect.addEventListener('change', function () {
            farmerPicker.style.display = audienceSelect.value === 'selected' ? '' : 'none';
        });

        var purposeSelect = form.querySelector('.purpose-select');
        var meetingFields = form.querySelector('.meeting-fields');

        purposeSelect.addEventListener('change', function () {
            meetingFields.style.display = purposeSelect.value === 'Meeting' ? '' : 'none';
        });
    });
</script>
@endsection
