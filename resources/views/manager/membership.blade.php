@extends('manager.layout')

@section('title', 'Membership Registration')
@section('header', 'Membership Registration')

@section('content')
<!-- Success Message -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Error Message -->
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Validation Errors -->
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

<!-- Membership Table Card -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header Actions -->
    <div class="p-4 p-md-6 border-b border-gray-200">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3 flex-grow-1">
                <div class="position-relative">
                    <input type="text" id="searchFarmers" placeholder="Search farmers..." class="form-control ps-5 py-2" style="min-width: 250px;" value="{{ request('search') }}">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select id="filterStatus" class="form-select py-2 {{ $showArchived ? 'd-none' : '' }}" style="width: auto;">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ !request()->has('status') || request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <x-archive-toggle route="manager.membership" :showing="$showArchived" :count="$archivedCount" />
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fas fa-plus"></i>
                    <span>New Request</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Barangay</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Contact</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Crop Type</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Land Area</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Documents</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-left text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-light">
                @forelse($farmers as $farmer)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-dark fw-medium">
                        {{ $farmer->first_name }} {{ $farmer->middle_initial }} {{ $farmer->last_name }} {{ $farmer->suffix }}
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->municipality }}, {{ $farmer->province }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->contact_number }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->crop_names }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->land_area }} hectares</td>
                    <td class="px-4 px-md-6 py-4">
                        <span class="text-success"><i class="fas fa-check-circle"></i> Complete</span>
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        <x-status-badge :status="ucfirst($farmer->status)" />
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        <x-action-dropdown>
                            <x-slot:primary><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $farmer->id }}">View</button></x-slot:primary>
                            @if($farmer->status == 'pending')
                            <x-dropdown-item icon="fa-pen" data-bs-toggle="modal" data-bs-target="#editModal{{ $farmer->id }}">Edit</x-dropdown-item>
                            <li><hr class="dropdown-divider"></li>
                            <x-dropdown-item icon="fa-archive" :danger="true" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $farmer->id }}">Archive</x-dropdown-item>
                            @elseif($farmer->status == 'rejected')
                            <x-dropdown-item icon="fa-redo" data-bs-toggle="modal" data-bs-target="#resubmitModal{{ $farmer->id }}">Resubmit</x-dropdown-item>
                            <li><hr class="dropdown-divider"></li>
                            <x-dropdown-item icon="fa-archive" :danger="true" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $farmer->id }}">Archive</x-dropdown-item>
                            @elseif($farmer->status == 'archived')
                            <x-dropdown-item icon="fa-box-open" data-bs-toggle="modal" data-bs-target="#unarchiveModal{{ $farmer->id }}">Unarchive</x-dropdown-item>
                            @endif
                        </x-action-dropdown>
                    </td>
                </tr>

                <!-- View Modal -->
                <div class="modal fade" id="viewModal{{ $farmer->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $farmer->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold" id="viewModalLabel{{ $farmer->id }}">
                                    <i class="fas fa-user me-2"></i>Farmer Details
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Full Name</label>
                                        <p class="fw-semibold mb-0">{{ $farmer->first_name }} {{ $farmer->middle_initial }} {{ $farmer->last_name }} {{ $farmer->suffix }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Contact Number</label>
                                        <p class="fw-semibold mb-0">{{ $farmer->contact_number }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Province</label>
                                        <p class="fw-semibold mb-0">{{ $farmer->province }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Municipality</label>
                                        <p class="fw-semibold mb-0">{{ $farmer->municipality }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Crop Type</label>
                                        <p class="fw-semibold mb-0">{{ $farmer->crop_names }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Land Area</label>
                                        <p class="fw-semibold mb-0">{{ $farmer->land_area }} hectares</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Status</label>
                                        <p class="fw-semibold mb-0">
                                            <x-status-badge :status="ucfirst($farmer->status)" />
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Date Registered</label>
                                        <p class="fw-semibold mb-0">{{ $farmer->created_at->format('M d, Y') }}</p>
                                    </div>
                                    @if($farmer->status === 'rejected' && $farmer->rejection_reason)
                                    <div class="col-12 mb-3">
                                        <label class="text-muted small">Rejection Reason</label>
                                        <p class="fw-semibold mb-0 text-danger">{{ $farmer->rejection_reason }}</p>
                                    </div>
                                    @endif
                                    @php
                                        $viewDocumentFields = [
                                            ['path' => 'documents_path', 'label' => 'Valid ID'],
                                            ['path' => 'certificate_of_title_path', 'label' => 'Certificate of Title'],
                                            ['path' => 'barangay_certification_path', 'label' => 'Barangay Certification of Land Possession'],
                                            ['path' => 'rsbsa_path', 'label' => 'RSBSA Number/ID'],
                                        ];
                                    @endphp
                                    @foreach($viewDocumentFields as $doc)
                                    <div class="col-12 mb-3">
                                        <label class="text-muted small">{{ $doc['label'] }}</label>
                                        <div class="mt-2">
                                            <x-document-links :paths="$farmer->documentPaths($doc['path'])" :label="$doc['label']" :group="'farmer-docs-'.$farmer->id" download />
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if($farmer->activityLogs->isNotEmpty())
                                <h4 class="text-sm fw-semibold text-dark mb-2 mt-1">History</h4>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($farmer->activityLogs as $log)
                                    <li class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <p class="mb-0">{{ $log->description }}</p>
                                        <p class="text-muted mb-0" style="font-size: 0.75rem;">{{ $log->created_at->format('M d, Y g:i A') }}</p>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $farmer->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $farmer->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark" id="editModalLabel{{ $farmer->id }}">
                                    <i class="fas fa-edit me-2"></i>Edit Farmer
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.membership.update', $farmer->id) }}" method="POST" enctype="multipart/form-data" class="modal-form-flex">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                            <input data-input="name" type="text" name="first_name" class="form-control" value="{{ $farmer->first_name }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                            <input data-input="name" type="text" name="last_name" class="form-control" value="{{ $farmer->last_name }}" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">M.I.</label>
                                            <input data-input="initial" type="text" name="middle_initial" class="form-control" value="{{ $farmer->middle_initial }}" maxlength="2">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Suffix</label>
                                            <select name="suffix" class="form-select">
                                                <option value="" {{ !$farmer->suffix ? 'selected' : '' }}>None</option>
                                                <option value="Jr." {{ $farmer->suffix == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                                <option value="Sr." {{ $farmer->suffix == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                                <option value="III" {{ $farmer->suffix == 'III' ? 'selected' : '' }}>III</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                                        <input data-input="phone" type="tel" name="contact_number" class="form-control ph-contact-input" value="{{ $farmer->contact_number }}" pattern="(09\d{9}|\+639\d{9})" inputmode="numeric" maxlength="13" required>
                                        <div class="invalid-feedback">Please enter a valid Philippine mobile number (e.g. 09123456789).</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Province <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" value="{{ \App\Models\Farmer::PROVINCE }}" readonly>
                                            <input type="hidden" name="province" value="{{ \App\Models\Farmer::PROVINCE }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Municipality <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" value="{{ \App\Models\Farmer::MUNICIPALITY }}" readonly>
                                            <input type="hidden" name="municipality" value="{{ \App\Models\Farmer::MUNICIPALITY }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Barangay</label>
                                            <select name="barangay" class="form-select">
                                                <option value="">Select Barangay</option>
                                                @if($farmer->barangay && !in_array($farmer->barangay, \App\Models\Farmer::BARANGAYS, true))
                                                <option value="{{ $farmer->barangay }}" selected>{{ $farmer->barangay }}</option>
                                                @endif
                                                @foreach(\App\Models\Farmer::BARANGAYS as $barangay)
                                                <option value="{{ $barangay }}" {{ $farmer->barangay === $barangay ? 'selected' : '' }}>{{ $barangay }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Crop Type <span class="text-danger">*</span></label>
                                            <div class="border rounded-3 p-2">
                                                @foreach($crops as $crop)
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="crop_ids[]" value="{{ $crop->id }}" id="editCrop{{ $farmer->id }}_{{ $crop->id }}" {{ $farmer->crops->contains($crop->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="editCrop{{ $farmer->id }}_{{ $crop->id }}">{{ $crop->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Land Area (hectares) <span class="text-danger">*</span></label>
                                            <input data-input="decimal" type="number" name="land_area" class="form-control" value="{{ $farmer->land_area }}" step="0.1" min="0" required>
                                        </div>
                                    </div>
                                    <!-- Document Upload -->
                                    @php
                                        $editDocumentFields = [
                                            ['field' => 'documents', 'path' => 'documents_path', 'label' => 'Upload Valid ID', 'prefix' => 'editDocument'],
                                            ['field' => 'certificate_of_title', 'path' => 'certificate_of_title_path', 'label' => 'Upload Certificate of Title', 'prefix' => 'editCertificate'],
                                            ['field' => 'barangay_certification', 'path' => 'barangay_certification_path', 'label' => 'Upload Barangay Certification of Land Possession', 'prefix' => 'editBarangay'],
                                            ['field' => 'rsbsa', 'path' => 'rsbsa_path', 'label' => 'Upload RSBSA Number/ID', 'prefix' => 'editRsbsa'],
                                        ];
                                    @endphp
                                    @foreach($editDocumentFields as $doc)
                                    <x-multi-file-upload :field="$doc['field']" :label="$doc['label']" :base-id="$doc['prefix'].$farmer->id" :existing-paths="$farmer->documentPaths($doc['path'])" />
                                    @endforeach
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @if($farmer->status === 'rejected')
                <!-- Resubmit Modal -->
                <div class="modal fade" id="resubmitModal{{ $farmer->id }}" tabindex="-1" aria-labelledby="resubmitModalLabel{{ $farmer->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title fw-bold" id="resubmitModalLabel{{ $farmer->id }}">
                                    <i class="fas fa-redo me-2"></i>Resubmit Application
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.membership.resubmit', $farmer->id) }}" method="POST" enctype="multipart/form-data" class="modal-form-flex">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    @if($farmer->rejection_reason)
                                    <div class="alert alert-danger py-2 small mb-3"><strong>Rejected:</strong> {{ $farmer->rejection_reason }}</div>
                                    @endif
                                    <p class="text-muted small mb-3">Correct whatever caused the rejection below, then resubmit — this sends it straight back to the Administrator as a new pending application. No need to start over.</p>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                            <input data-input="name" type="text" name="first_name" class="form-control" value="{{ $farmer->first_name }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                            <input data-input="name" type="text" name="last_name" class="form-control" value="{{ $farmer->last_name }}" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">M.I.</label>
                                            <input data-input="initial" type="text" name="middle_initial" class="form-control" value="{{ $farmer->middle_initial }}" maxlength="2">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Suffix</label>
                                            <select name="suffix" class="form-select">
                                                <option value="" {{ !$farmer->suffix ? 'selected' : '' }}>None</option>
                                                <option value="Jr." {{ $farmer->suffix == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                                <option value="Sr." {{ $farmer->suffix == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                                <option value="III" {{ $farmer->suffix == 'III' ? 'selected' : '' }}>III</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                                        <input data-input="phone" type="tel" name="contact_number" class="form-control ph-contact-input" value="{{ $farmer->contact_number }}" pattern="(09\d{9}|\+639\d{9})" inputmode="numeric" maxlength="13" required>
                                        <div class="invalid-feedback">Please enter a valid Philippine mobile number (e.g. 09123456789).</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Province <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" value="{{ \App\Models\Farmer::PROVINCE }}" readonly>
                                            <input type="hidden" name="province" value="{{ \App\Models\Farmer::PROVINCE }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Municipality <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" value="{{ \App\Models\Farmer::MUNICIPALITY }}" readonly>
                                            <input type="hidden" name="municipality" value="{{ \App\Models\Farmer::MUNICIPALITY }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Barangay</label>
                                            <select name="barangay" class="form-select">
                                                <option value="">Select Barangay</option>
                                                @if($farmer->barangay && !in_array($farmer->barangay, \App\Models\Farmer::BARANGAYS, true))
                                                <option value="{{ $farmer->barangay }}" selected>{{ $farmer->barangay }}</option>
                                                @endif
                                                @foreach(\App\Models\Farmer::BARANGAYS as $barangay)
                                                <option value="{{ $barangay }}" {{ $farmer->barangay === $barangay ? 'selected' : '' }}>{{ $barangay }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Crop Type <span class="text-danger">*</span></label>
                                            <div class="border rounded-3 p-2">
                                                @foreach($crops as $crop)
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="crop_ids[]" value="{{ $crop->id }}" id="resubmitCrop{{ $farmer->id }}_{{ $crop->id }}" {{ $farmer->crops->contains($crop->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="resubmitCrop{{ $farmer->id }}_{{ $crop->id }}">{{ $crop->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Land Area (hectares) <span class="text-danger">*</span></label>
                                            <input data-input="decimal" type="number" name="land_area" class="form-control" value="{{ $farmer->land_area }}" step="0.1" min="0" required>
                                        </div>
                                    </div>
                                    <!-- Document Upload -->
                                    @php
                                        $resubmitDocumentFields = [
                                            ['field' => 'documents', 'path' => 'documents_path', 'label' => 'Upload Valid ID', 'prefix' => 'resubmitDocument'],
                                            ['field' => 'certificate_of_title', 'path' => 'certificate_of_title_path', 'label' => 'Upload Certificate of Title', 'prefix' => 'resubmitCertificate'],
                                            ['field' => 'barangay_certification', 'path' => 'barangay_certification_path', 'label' => 'Upload Barangay Certification of Land Possession', 'prefix' => 'resubmitBarangay'],
                                            ['field' => 'rsbsa', 'path' => 'rsbsa_path', 'label' => 'Upload RSBSA Number/ID', 'prefix' => 'resubmitRsbsa'],
                                        ];
                                    @endphp
                                    @foreach($resubmitDocumentFields as $doc)
                                    <x-multi-file-upload :field="$doc['field']" :label="$doc['label']" :base-id="$doc['prefix'].$farmer->id" :existing-paths="$farmer->documentPaths($doc['path'])" />
                                    @endforeach
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-info text-white">
                                        <i class="fas fa-redo me-1"></i> Resubmit to Admin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Archive Modal -->
                <div class="modal fade" id="archiveModal{{ $farmer->id }}" tabindex="-1" aria-labelledby="archiveModalLabel{{ $farmer->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title fw-bold" id="archiveModalLabel{{ $farmer->id }}">
                                    <i class="fas fa-archive me-2"></i>Archive Farmer
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to archive this farmer?</p>
                                <div class="bg-light p-3 rounded">
                                    <strong>{{ $farmer->first_name }} {{ $farmer->last_name }}</strong><br>
                                    <small class="text-muted">{{ $farmer->municipality }}, {{ $farmer->province }}</small>
                                </div>
                                <p class="mt-3 mb-0 text-muted"><small><i class="fas fa-info-circle me-1"></i>This action can be undone later.</small></p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.membership.archive', $farmer->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary">Archive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unarchive Modal -->
                <div class="modal fade" id="unarchiveModal{{ $farmer->id }}" tabindex="-1" aria-labelledby="unarchiveModalLabel{{ $farmer->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold" id="unarchiveModalLabel{{ $farmer->id }}">
                                    <i class="fas fa-box-open me-2"></i>Unarchive Farmer
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to unarchive this farmer?</p>
                                <div class="bg-light p-3 rounded">
                                    <strong>{{ $farmer->first_name }} {{ $farmer->last_name }}</strong><br>
                                    <small class="text-muted">{{ $farmer->municipality }}, {{ $farmer->province }}</small>
                                </div>
                                <p class="mt-3 mb-0 text-muted"><small><i class="fas fa-info-circle me-1"></i>After unarchiving, the farmer will appear in the pending list.</small></p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.membership.unarchive', $farmer->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">Unarchive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-4 text-center text-muted">{{ $showArchived ? 'No archived members.' : 'No membership requests found.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination-footer :paginator="$farmers" />
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0 pt-3 px-3">
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4 pt-0">
                <div class="text-center mb-4">
                    <div class="confirm-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="confirmModalLabel">Confirm Your Submission</h5>
                    <p class="text-muted small mb-0">Please review the details before submitting</p>
                </div>

                <div class="confirm-summary-list border rounded-3 px-3">
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-user text-success me-2 fa-fw"></i>Farmer Name</span>
                        <span class="fw-semibold text-end" id="confirmFarmerName">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-seedling text-success me-2 fa-fw"></i>Crop Type</span>
                        <span class="fw-semibold text-end" id="confirmCropType">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-ruler-combined text-success me-2 fa-fw"></i>Land Area</span>
                        <span class="fw-semibold text-end" id="confirmLandArea">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <span class="text-muted small"><i class="fas fa-phone text-success me-2 fa-fw"></i>Contact</span>
                        <span class="fw-semibold text-end" id="confirmContact">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2">
                        <span class="text-muted small"><i class="fas fa-map-marker-alt text-success me-2 fa-fw"></i>Location</span>
                        <span class="fw-semibold text-end" id="confirmLocation">-</span>
                    </div>
                </div>

                <div class="info-banner variant-warning mt-3 mb-4">
                    <i class="fas fa-exclamation-triangle mt-1"></i>
                    <p class="small mb-0">
                        <strong>Note:</strong> Once submitted, you cannot edit this request. The status will be set to "Pending" for admin review.
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill py-2" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-2"></i>Go Back
                    </button>
                    <button type="button" class="btn btn-success flex-fill py-2" id="confirmSubmitBtn">
                        <i class="fas fa-check-circle me-2"></i>Confirm &amp; Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .confirm-icon-badge {
        width: 64px;
        height: 64px;
        font-size: 1.75rem;
        background-color: var(--brand-success-light);
        color: var(--brand-success);
    }
    .confirm-summary-list > div:last-child {
        border-bottom: none !important;
    }
</style>

<!-- Bootstrap 5 Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="createModalLabel">
                    <i class="fas fa-user-plus me-2 text-primary"></i>
                    New Membership Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form action="{{ route('manager.membership.store') }}" method="POST" id="membershipForm" class="needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="is_confirmed" id="isConfirmed" value="0">
                    <!-- Row 1: Name Fields -->
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input data-input="name" type="text" name="first_name" class="form-control form-control-lg" placeholder="First name" required>
                            <div class="invalid-feedback">Please enter first name.</div>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">M.I.</label>
                            <input data-input="initial" type="text" name="middle_initial" class="form-control form-control-lg" placeholder="M.I." maxlength="2">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input data-input="name" type="text" name="last_name" class="form-control form-control-lg" placeholder="Last name" required>
                            <div class="invalid-feedback">Please enter last name.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Suffix</label>
                            <select name="suffix" class="form-select form-select-lg">
                                <option value="" selected>None</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Contact & Location -->
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                            <input data-input="phone" type="tel" name="contact_number" class="form-control form-control-lg ph-contact-input" placeholder="09123456789" pattern="(09\d{9}|\+639\d{9})" inputmode="numeric" maxlength="13" required>
                            <div class="invalid-feedback">Please enter a valid Philippine mobile number (e.g. 09123456789).</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Province <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" value="{{ \App\Models\Farmer::PROVINCE }}" readonly>
                            <input type="hidden" name="province" value="{{ \App\Models\Farmer::PROVINCE }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Municipality <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" value="{{ \App\Models\Farmer::MUNICIPALITY }}" readonly>
                            <input type="hidden" name="municipality" value="{{ \App\Models\Farmer::MUNICIPALITY }}">
                        </div>
                    </div>

                    <!-- Row 2b: Barangay -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Barangay</label>
                            <select class="form-select form-select-lg" name="barangay">
                                <option value="">Select Barangay</option>
                                @foreach(\App\Models\Farmer::BARANGAYS as $barangay)
                                <option value="{{ $barangay }}" {{ old('barangay') === $barangay ? 'selected' : '' }}>{{ $barangay }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Crop Type & Land Area -->
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
                            <input data-input="decimal" type="number" name="land_area" step="0.1" min="0" class="form-control form-control-lg" placeholder="0.0" required>
                            <div class="invalid-feedback">Please enter land area.</div>
                        </div>
                    </div>

                    <!-- Row 4: Upload Documents -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <x-multi-file-upload field="documents" label="Upload Valid ID" base-id="newDocument" required />
                        </div>
                        <div class="col-md-6">
                            <x-multi-file-upload field="certificate_of_title" label="Upload Certificate of Title" base-id="newCertificate" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <x-multi-file-upload field="barangay_certification" label="Upload Barangay Certification of Land Possession" base-id="newBarangay" required />
                        </div>
                        <div class="col-md-6">
                            <x-multi-file-upload field="rsbsa" label="Upload RSBSA Number/ID" base-id="newRsbsa" required />
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" form="membershipForm" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .borderdashed {
        border-style: dashed !important;
    }
    .form-control-lg, .form-select-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    /* Confirmation Modal Styles */
    #confirmModal {
        z-index: 1060 !important;
    }
    /* The confirm box opens on top of the New Membership window; lift its
       backdrop between the two so the form behind is dimmed and blurred. */
    .modal-backdrop.confirm-backdrop {
        z-index: 1058;
    }
    #confirmModal .modal-dialog {
        max-width: 500px;
    }
    #confirmModal .card {
        border-radius: 0.5rem;
    }
    @media (max-width: 767.98px) {
        .modal-dialog {
            margin: 1rem;
        }
        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    }
</style>

<script>
    // Bootstrap gives a stacked modal's backdrop the same layer as the first
    // backdrop, under the New Membership window. Tag it so CSS lifts it above.
    document.getElementById('confirmModal')?.addEventListener('show.bs.modal', function () {
        setTimeout(function () {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                backdrops[backdrops.length - 1].classList.add('confirm-backdrop');
            }
        }, 0);
    });

    // Search and Filter Functionality
    const searchInput = document.getElementById('searchFarmers');
    const filterStatus = document.getElementById('filterStatus');

    function applyFilters() {
        const searchValue = searchInput.value;
        const statusValue = filterStatus.value;
        const currentUrl = new URL(window.location.href);

        if (searchValue) {
            currentUrl.searchParams.set('search', searchValue);
        } else {
            currentUrl.searchParams.delete('search');
        }

        if (statusValue) {
            currentUrl.searchParams.set('status', statusValue);
        } else {
            currentUrl.searchParams.delete('status');
        }

        window.location.href = currentUrl.toString();
    }

    // Add event listeners
    searchInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyFilters();
        }
    });

    // Also trigger search on input change with debounce
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });

    filterStatus.addEventListener('change', function() {
        applyFilters();
    });

    // Enable Bootstrap validation and confirmation modal
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                const checkedCrops = form.querySelectorAll('input[name="crop_ids[]"]:checked');
                const cropError = document.getElementById('cropCheckboxError');

                if (!form.checkValidity() || checkedCrops.length === 0) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    if (cropError) {
                        cropError.style.display = checkedCrops.length === 0 ? 'block' : 'none';
                    }
                    return;
                }

                if (cropError) {
                    cropError.style.display = 'none';
                }

                // Prevent default submission and show confirmation modal
                event.preventDefault();
                event.stopPropagation();

                // Get form values for confirmation (scoped to this form — the page
                // also contains per-farmer edit modals with the same field names)
                const firstName = form.querySelector('input[name="first_name"]').value;
                const middleInitial = form.querySelector('input[name="middle_initial"]').value;
                const lastName = form.querySelector('input[name="last_name"]').value;
                const suffix = form.querySelector('select[name="suffix"]').value;
                const contactNumber = form.querySelector('input[name="contact_number"]').value;
                const cropName = Array.prototype.map.call(checkedCrops, function(checkbox) {
                    return checkbox.nextElementSibling.textContent;
                }).join(', ');
                const landArea = form.querySelector('input[name="land_area"]').value;
                const barangay = form.querySelector('select[name="barangay"]').value;
                const municipality = form.querySelector('input[name="municipality"]').value;
                const province = form.querySelector('input[name="province"]').value;

                // Build full name
                let fullName = firstName;
                if (middleInitial) fullName += ' ' + middleInitial;
                fullName += ' ' + lastName;
                if (suffix) fullName += ' ' + suffix;

                // Set confirmation values
                document.getElementById('confirmFarmerName').textContent = fullName;
                document.getElementById('confirmCropType').textContent = cropName;
                document.getElementById('confirmLandArea').textContent = landArea + ' hectares';
                document.getElementById('confirmContact').textContent = contactNumber;
                document.getElementById('confirmLocation').textContent = (barangay ? barangay + ', ' : '') + municipality + ', ' + province;

                // Show confirmation modal
                let confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
                if (!confirmModal) {
                    confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
                }
                confirmModal.show();
            }, false);
        });

        // Handle confirm button click
        document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
            const form = document.getElementById('membershipForm');

            // Set confirmed flag
            document.getElementById('isConfirmed').value = '1';

            // Hide confirmation modal
            const confirmModalEl = document.getElementById('confirmModal');
            let confirmModal = bootstrap.Modal.getInstance(confirmModalEl);
            if (!confirmModal) {
                confirmModal = new bootstrap.Modal(confirmModalEl);
            }
            confirmModal.hide();

            // Small delay then submit to allow modals to close
            setTimeout(function() {
                form.submit();
            }, 300);
        });

        // Reset form when create modal is closed
        document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('membershipForm');
            form.reset();
            form.classList.remove('was-validated');
            document.getElementById('cropCheckboxError').style.display = 'none';
            form.querySelectorAll('[data-multi-upload]').forEach(function(container) {
                const slotsContainer = container.querySelector('[data-multi-upload-slots]');
                slotsContainer.innerHTML = '';
                container.querySelector('[data-multi-upload-add]').click();
            });
        });

        // Auto-open modal if there are old input values (validation error)
        @if($errors->any() || session('error'))
        document.addEventListener('DOMContentLoaded', function() {
            const createModal = new bootstrap.Modal(document.getElementById('createModal'));
            createModal.show();
        });
        @endif
    })();

    // Restrict Philippine contact number inputs to digits (with optional leading +)
    document.querySelectorAll('.ph-contact-input').forEach(function(input) {
        input.addEventListener('input', function() {
            let value = input.value.replace(/[^\d+]/g, '');
            if (value.indexOf('+') > 0) {
                value = value.replace(/\+/g, '');
            }
            input.value = value;
        });
    });
</script>
@endsection