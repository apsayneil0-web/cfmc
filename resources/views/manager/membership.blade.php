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
                <select id="filterStatus" class="form-select py-2" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus"></i>
                <span>New Request</span>
            </button>
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
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->crop->name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->land_area }} hectares</td>
                    <td class="px-4 px-md-6 py-4">
                        <span class="text-success"><i class="fas fa-check-circle"></i> Complete</span>
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        <x-status-badge :status="ucfirst($farmer->status)" />
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $farmer->id }}"><i class="fas fa-eye"></i></button>
                            @if($farmer->status == 'pending')
                            <button class="btn btn-sm btn-outline-warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $farmer->id }}"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-secondary" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $farmer->id }}"><i class="fas fa-archive"></i></button>
                            @elseif($farmer->status == 'archived')
                            <button class="btn btn-sm btn-outline-success" title="Unarchive" data-bs-toggle="modal" data-bs-target="#unarchiveModal{{ $farmer->id }}"><i class="fas fa-box-open"></i></button>
                            @endif
                        </div>
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
                                        <p class="fw-semibold mb-0">{{ $farmer->crop->name }}</p>
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
                                            @if($farmer->{$doc['path']})
                                                @php
                                                    $extension = pathinfo($farmer->{$doc['path']}, PATHINFO_EXTENSION);
                                                    $filename = basename($farmer->{$doc['path']});
                                                @endphp
                                                @if(in_array($extension, ['jpg', 'jpeg', 'png']))
                                                    <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-image me-1"></i> View Image
                                                    </a>
                                                    <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" download="{{ $filename }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-download me-1"></i> Download
                                                    </a>
                                                @elseif($extension == 'pdf')
                                                    <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-file-pdf me-1"></i> View PDF
                                                    </a>
                                                    <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" download="{{ $filename }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-download me-1"></i> Download
                                                    </a>
                                                @else
                                                    <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-file me-1"></i> View Document
                                                    </a>
                                                    <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" download="{{ $filename }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-download me-1"></i> Download
                                                    </a>
                                                @endif
                                            @else
                                                <span class="text-muted">No document uploaded</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
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
                                            <input type="text" name="first_name" class="form-control" value="{{ $farmer->first_name }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" name="last_name" class="form-control" value="{{ $farmer->last_name }}" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">M.I.</label>
                                            <input type="text" name="middle_initial" class="form-control" value="{{ $farmer->middle_initial }}" maxlength="2">
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
                                        <input type="tel" name="contact_number" class="form-control ph-contact-input" value="{{ $farmer->contact_number }}" pattern="(09\d{9}|\+639\d{9})" inputmode="numeric" maxlength="13" required>
                                        <div class="invalid-feedback">Please enter a valid Philippine mobile number (e.g. 09123456789).</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Province <span class="text-danger">*</span></label>
                                            <select name="province" class="form-select" id="editProvince{{ $farmer->id }}" required>
                                                <option value="" disabled>Select Province</option>
                                                <option value="Bukidnon" {{ $farmer->province == 'Bukidnon' ? 'selected' : '' }}>Bukidnon</option>
                                                <option value="Camiguin" {{ $farmer->province == 'Camiguin' ? 'selected' : '' }}>Camiguin</option>
                                                <option value="Lanao del Norte" {{ $farmer->province == 'Lanao del Norte' ? 'selected' : '' }}>Lanao del Norte</option>
                                                <option value="Misamis Occidental" {{ $farmer->province == 'Misamis Occidental' ? 'selected' : '' }}>Misamis Occidental</option>
                                                <option value="Misamis Oriental" {{ $farmer->province == 'Misamis Oriental' ? 'selected' : '' }}>Misamis Oriental</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Municipality <span class="text-danger">*</span></label>
                                            <input type="text" name="municipality" class="form-control" value="{{ $farmer->municipality }}" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Crop Type <span class="text-danger">*</span></label>
                                            <select name="crop_id" class="form-select" required>
                                                @foreach($crops as $crop)
                                                    <option value="{{ $crop->id }}" {{ $farmer->crop_id == $crop->id ? 'selected' : '' }}>{{ $crop->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Land Area (hectares) <span class="text-danger">*</span></label>
                                            <input type="number" name="land_area" class="form-control" value="{{ $farmer->land_area }}" step="0.1" min="0" required>
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
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $doc['label'] }}</label>
                                        <div class="border border-2 borderdashed rounded-3 p-3 text-center bg-light" id="{{ $doc['prefix'] }}DropZone{{ $farmer->id }}">
                                            <div id="{{ $doc['prefix'] }}Preview{{ $farmer->id }}">
                                                @if($farmer->{$doc['path']})
                                                    <div class="mb-2">
                                                        <span class="text-success"><i class="fas fa-check-circle"></i> Current: {{ basename($farmer->{$doc['path']}) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <i class="fas fa-cloud-upload-alt text-muted mb-2 d-block" style="font-size: 1.5rem;"></i>
                                            <p class="fw-medium text-dark mb-1 small">Click to upload or drag and drop</p>
                                            <p class="text-muted small mb-0">PDF, JPG, or PNG (Max 10MB)</p>
                                            <input type="file" name="{{ $doc['field'] }}" id="{{ $doc['prefix'] }}Input{{ $farmer->id }}" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                                            <div id="{{ $doc['prefix'] }}FileName{{ $farmer->id }}" class="mt-2 text-success fw-medium"></div>
                                        </div>
                                        @if($farmer->{$doc['path']})
                                        <div class="mt-2 form-check">
                                            <input type="checkbox" class="form-check-input" id="remove_{{ $doc['field'] }}{{ $farmer->id }}" name="remove_{{ $doc['field'] }}" value="1">
                                            <label class="form-check-label text-danger" for="remove_{{ $doc['field'] }}{{ $farmer->id }}">Remove current file</label>
                                        </div>
                                        @endif
                                    </div>
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
                    <td colspan="8" class="px-4 px-md-6 py-4 text-center text-muted">No membership requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-4 px-md-6 py-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <span class="text-muted small">Showing 1-10 of 23 entries</span>
        <nav aria-label="Table pagination">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                <li class="page-item active"><span class="page-link">1</span></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
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
                            <input type="text" name="first_name" class="form-control form-control-lg" placeholder="First name" required>
                            <div class="invalid-feedback">Please enter first name.</div>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">M.I.</label>
                            <input type="text" name="middle_initial" class="form-control form-control-lg" placeholder="M.I." maxlength="2">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control form-control-lg" placeholder="Last name" required>
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
                            <input type="tel" name="contact_number" class="form-control form-control-lg ph-contact-input" placeholder="09123456789" pattern="(09\d{9}|\+639\d{9})" inputmode="numeric" maxlength="13" required>
                            <div class="invalid-feedback">Please enter a valid Philippine mobile number (e.g. 09123456789).</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Province <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="provinceSelect" name="province" required>
                                <option value="" selected disabled>Select Province</option>
                                <optgroup label="Region X - Northern Mindanao">
                                    <option value="Bukidnon">Bukidnon</option>
                                    <option value="Camiguin">Camiguin</option>
                                    <option value="Lanao del Norte">Lanao del Norte</option>
                                    <option value="Misamis Occidental">Misamis Occidental</option>
                                    <option value="Misamis Oriental">Misamis Oriental</option>
                                </optgroup>
                                <optgroup label="Region XI - Davao Region">
                                    <option value="Davao de Oro">Davao de Oro (Compostela Valley)</option>
                                    <option value="Davao del Norte">Davao del Norte</option>
                                    <option value="Davao del Sur">Davao del Sur</option>
                                    <option value="Davao Oriental">Davao Oriental</option>
                                </optgroup>
                                <optgroup label="Region XII - SOCCSKSARGEN">
                                    <option value="Cotabato">Cotabato</option>
                                    <option value="Cotabato del Sur">Cotabato del Sur (Sultan Kudarat)</option>
                                    <option value="South Cotabato">South Cotabato</option>
                                    <option value="Sarangani">Sarangani</option>
                                </optgroup>
                                <optgroup label="Region XIII - Caraga">
                                    <option value="Agusan del Norte">Agusan del Norte</option>
                                    <option value="Agusan del Sur">Agusan del Sur</option>
                                    <option value="Surigao del Norte">Surigao del Norte</option>
                                    <option value="Surigao del Sur">Surigao del Sur</option>
                                    <option value="Dinagat Islands">Dinagat Islands</option>
                                </optgroup>
                                <optgroup label="BARMM">
                                    <option value="Basilan">Basilan</option>
                                    <option value="Lanao del Sur">Lanao del Sur</option>
                                    <option value="Maguindanao">Maguindanao</option>
                                    <option value="Sulu">Sulu</option>
                                    <option value="Tawi-Tawi">Tawi-Tawi</option>
                                </optgroup>
                            </select>
                            <div class="invalid-feedback">Please select province.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Municipality <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="municipalitySelect" name="municipality" required>
                                <option value="" selected disabled>Select Municipality</option>
                            </select>
                            <div class="invalid-feedback">Please select municipality.</div>
                        </div>
                    </div>

                    <!-- Row 3: Crop Type & Land Area -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Crop Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" name="crop_id" required>
                                <option value="" selected disabled>Select Crop</option>
                                @foreach($crops as $crop)
                                    <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select crop type.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Land Area (hectares) <span class="text-danger">*</span></label>
                            <input type="number" name="land_area" step="0.1" min="0" class="form-control form-control-lg" placeholder="0.0" required>
                            <div class="invalid-feedback">Please enter land area.</div>
                        </div>
                    </div>

                    <!-- Row 4: Upload Documents -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Upload Valid ID <span class="text-danger">*</span></label>
                            <div class="border border-2 borderdashed rounded-3 p-4 text-center bg-light hover:bg-white transition cursor-pointer" id="documentDropZone">
                                <i class="fas fa-cloud-upload-alt text-4xl text-muted mb-3 d-block"></i>
                                <p class="fw-medium text-dark mb-1">Click to upload or drag and drop</p>
                                <p class="text-muted small mb-0">PDF, JPG, or PNG (Max 10MB)</p>
                                <input type="file" name="documents" id="documentsInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
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
                                <input type="file" name="certificate_of_title" id="certificateInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
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
                                <input type="file" name="barangay_certification" id="barangayInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
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
                                <input type="file" name="rsbsa" id="rsbsaInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                                <div id="rsbsaFileName" class="mt-2 text-success fw-medium"></div>
                            </div>
                            <div class="invalid-feedback">Please upload the RSBSA number/ID.</div>
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
    // Mindanao Provinces and Municipalities Data
    const municipalitiesData = {
        'Bukidnon': [
            'Baungon', 'Cabanglasan', 'Damulog', 'Dangcagan', 'Don Carlos', 'Impasugong', 'Kadingilan', 'Kalilangan', 'Kibawe', 'Kitaotao', 'Lantapan', 'Libona', 'Malitbog', 'Manolo Fortich', 'Maramag', 'Pangantucan', 'Quezon', 'San Fernando', 'Sumilao', 'Talakag'
        ],
        'Camiguin': [
            'Catarman', 'Guinsiliban', 'Mambajao', 'Mahinog', 'Sagbayan'
        ],
        'Lanao del Norte': [
            'Bacolod', 'Baloi', 'Baroy', 'Dadaguayan', 'Dapitan City', 'Dipolog City', 'Gloria', 'Josefina', 'Kapatagan', 'Kauswagan', 'Kolambugan', 'Lala', 'Linamon', 'Maigo', 'Matungao', 'Munai', 'Nunukan', 'Ozamis City', 'Pantar', 'Plaridel', 'Salvador', 'Sapitan', 'Sultan Naga Dimaporo', 'Tagoloan', 'Tangcal', 'Tubod'
        ],
        'Misamis Occidental': [
            'Aloran', 'Baliangao', 'Bonifacio', 'Calamba', 'Clarin', 'Concepcion', 'Cotowato', 'Danjugan', 'Guingona', 'Lopez Jaena', 'Misamis Occidental', 'Nabunuran', 'Oroquieta City', 'Ozamis City', 'Panaon', 'Plaridel', 'Sagun', 'San Francisco', 'Santa Maria', 'Tangaro', 'Tudela'
        ],
        'Misamis Oriental': [
            'Alubijid', 'Balingasag', 'Balingoan', 'Binuangan', 'Cagayan de Oro City', 'Claveria', 'Gingoog City', 'Gitagum', 'Initao', 'Jasaan', 'Kinoguitan', 'Lagonglong', 'Laguindingan', 'Libona', 'Lugait', 'Magsaysay', 'Manticao', 'Medina', 'Naawan', 'Opol', 'Salay', 'Sugbongcogon', 'Tagoloan', 'Talisayan', 'Villanueva'
        ],
        'Davao de Oro': [
            'Compostela', 'Laak', 'Mabini', 'Maco', 'Maragusan', 'Mawab', 'Monkayo', 'Nabunturan', 'New Bataan', 'Pantukan'
        ],
        'Davao del Norte': [
            'Asuncion', 'Braulio E. Dujali', 'Carmen', 'Davao City', 'Igacos', 'Kapalong', 'New Corella', 'Panabo City', 'Samal City', 'Santo Tomas', 'Tagum City', 'Talaingod'
        ],
        'Davao del Sur': [
            'Bansalan', 'Davao City', 'Digos City', 'Hagonoy', 'Kiblawan', 'Magsaysay', 'Malalag', 'Matanao', 'Padada', 'Santa Cruz', 'Sulop'
        ],
        'Davao Oriental': [
            'Bagana', 'Banaybanay', 'Boston', 'Caraga', 'Cateel', 'Davao Oriental', 'Governor Generoso', 'Lupon', 'Manay', 'Mati City', 'San Isidro', 'Tagum City', 'Tarragona'
        ],
        'Cotabato': [
            'Aleosan', 'Antipas', 'Arakan', 'Banisilan', 'Carmen', 'Kabacan', 'Kidapawan City', 'Libungan', 'Magsaysay', 'Makbar', 'Malanday', 'Matalam', 'Midsayap', 'Mount Butil', 'Pikit', 'Pigcawayan', 'President Roxas', 'Tulunan'
        ],
        'Cotabato del Sur': [
            'Bagumbayan', 'Bangsamoro', 'Columbio', 'Esperanza', 'Isulan', 'Kalamarang', 'Lebak', 'Llambayan', 'Lutayan', 'Palimbang', 'President Quirino', 'Province of Sultan Kudarat', 'Rama', 'Senator Ninoy Aquino', 'Tacurong City', 'Tantangan', 'Tupol'
        ],
        'South Cotabato': [
            'Bangian', 'Bagumpasig', 'Banon', 'Buayan', 'Buli', 'Caloocan', 'Datu Saliao', 'General Santos City', 'Koronadal City', 'Lake Sebu', 'Lambayong', 'Mabini', 'Magsaysay', 'Maiha', 'Malapatan', 'Malungon', 'Norala', 'Polomolok', 'Santo Nino', 'Surallah', 'T\'Boli', 'Tampakan', 'Tantangan', 'Tupi'
        ],
        'Sarangani': [
            'Alabel', 'Camarigan', 'Don Marcelino', 'Glan', 'Guba', 'Jose Abad Santos', 'Kiamba', 'Maitum', 'Malapatan', 'Malungon', 'San Isidro', 'Santo Domingo', 'Sarangani'
        ],
        'Agusan del Norte': [
            'Buenavista', 'Butuan City', 'Cabadbaran City', 'Carmen', 'Jabonga', 'Kitcharao', 'Las Nieves', 'Magallanes', 'Nasipit', 'Remedios T. Romualdez', 'R TRomualdez', 'Santiago', 'Tubay'
        ],
        'Agusan del Sur': [
            'Bayugan City', 'Bunawan', 'Esperanza', 'La Paz', 'Loreto', 'Prosperidad', 'San Francisco', 'San Luis', 'Santa Josefa', 'Santo Tomas', 'Talacogon', 'Trento', 'Veruela'
        ],
        'Surigao del Norte': [
            'Alegria', 'Bacuag', 'Burgos', 'Cagdianao', 'Claver', 'Dapa', 'Del Carmen', 'General Luna', 'Gigaquit', 'Mainit', 'Malimono', 'Pilar', 'Placer', 'San Benito', 'San Francisco', 'San Isidro', 'Santa Monica', 'Sison', 'Socorro', 'Surigao City', 'Tagana-an'
        ],
        'Surigao del Sur': [
            'Barobo', 'Bayabas', 'Bislig City', 'Cagwait', 'Cantilan', 'Carrascal', 'Cortez', 'Datu Abdulla Sangki', 'Datu Blah T. Sinsuat', 'Datu Piang', 'Datu Salib', 'Dinagat', 'Español', 'Gandara', 'Hinatuan', 'Jabonga', 'Kitchen', 'Lanuza', 'Lianga', 'Lingig', 'Madrid', 'Magsaysay', 'Marihat', 'San Agustin', 'San Miguel', 'Tagbina', 'Tago', 'Tandag City'
        ],
        'Dinagat Islands': [
            'Basilisa', 'Cagdianao', 'Dinagat', 'Garcia Hernandez', 'Jabonga', 'Libjo', 'Loreto', 'Pilar', 'Placer', 'San Jose', 'Tubajon'
        ],
        'Basilan': [
            'Akbar', 'Al-Barka', 'Hadji Mohammad Ajul', 'Hadji Muhtamad', 'Isabela City', 'Lamitan', 'Lantaka', 'Maluso', 'Sumisip', 'Tabuan-Laki', 'Tipo-Tipo', 'Tuburan', 'Ungkaya Pukan'
        ],
        'Lanao del Sur': [
            'Amai Manabilang', 'Bacolod-Kalas', 'Balabagan', 'Balindong', 'Bayang', 'Binidayan', 'Buadiposo-Buntong', 'Bumbaran', 'Butig', 'Calanogas', 'Campestral', 'Datu Abdullah Sangki', 'Datu Andal Amas', 'Datu Blah T. Sinsuat', 'Datu Hoffer Ampatuan', 'Datu Ibnona', 'Datu Montamal', 'Datu Piang', 'Datu Salib', 'Datu UN', 'Ganassi', 'G受影响', 'Kapatagan', 'Kauswagan', 'Lumba-Bayabao', 'Lumbaca-Unayan', 'Lumbatan', 'Lunday', 'Madalum', 'Madamba', 'Maguing', 'Malabang', 'Marawi City', 'Marantao', 'Masiu', 'Mulondo', 'Pagayawan', 'Piagapo', 'Piarapon', 'Picong', 'Poona Lake', 'Pualas', 'Ramitan', 'Sagd', 'Sultan Gumander', 'Sultan Mastura', 'Tagoloan II', 'Tambo', 'Tamparan', 'Taraka', 'Tubaran', 'Tugaya', 'Wao'
        ],
        'Maguindanao': [
            'Ampatuan', 'Barira', 'Buldon', 'Buluan', 'Datu Abdullah Sangki', 'Datu Abdul Sinsuat', 'Datu Ampunan', 'Datu Angalan', 'Datu Blah T. Sinsuat', 'Datu Hoffer Ampatuan', 'Datu Ibnona', 'Datu Montamal', 'Datu Odin Sinsuat', 'Datu Paglas', 'Datu Piang', 'Datu Salib', 'Datu Saudi-Ampatuan', 'Datu Sinsuat', 'Datu Unsay', 'General Salip K.', 'Gulang-Gulang', 'Mamasapano', 'Mangudadatu', 'Matalao', 'Mayo', 'Motril', 'Mulondo', 'Pagagawan', 'Paglat', 'Pandag', 'Pata', 'Patin-ay', 'Raja Buayan', 'Rangdean', 'Sampual', 'Shariff Aguak', 'Shariff Saidona Mustapha', 'Sultan Kudarat', 'Sultan Mastura', 'Talayan', 'Talitay'
        ],
        'Sulu': [
            'Banguingui', 'Hadji Bud Sinsuat', 'Indanan', 'Jolo', 'Kalingalan Caluang', 'Lugus', 'Luuk', 'Maimbung', 'New Capitol', 'Old Panamao', 'Omar', 'Pandami', 'Pangutaran', 'Parang', 'Pata', 'Patikul', 'Siasi', 'Talibon', 'Tapul'
        ],
        'Tawi-Tawi': [
            'Bongao', 'Languyan', 'Mapun', 'Sapa-Sapa', 'Sibutu', 'Simunul', 'Sitangkai', 'South Ubian', 'Tandubas', 'Turtle Islands'
        ]
    };

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

    // Document Upload Preview
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

        // Drag and drop functionality
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

    // Province Select Change Handler
    document.getElementById('provinceSelect').addEventListener('change', function() {
        const selectedProvince = this.value;
        const municipalitySelect = document.getElementById('municipalitySelect');

        // Clear current options
        municipalitySelect.innerHTML = '<option value="" selected disabled>Select Municipality</option>';

        if (selectedProvince && municipalitiesData[selectedProvince]) {
            // Add municipalities for selected province
            municipalitiesData[selectedProvince].forEach(function(municipality) {
                const option = document.createElement('option');
                option.value = municipality;
                option.textContent = municipality;
                municipalitySelect.appendChild(option);
            });
        }
    });

    // Handle edit modal document uploads (one dropzone per document type, per farmer row)
    ['editDocument', 'editCertificate', 'editBarangay', 'editRsbsa'].forEach(function(prefix) {
        document.querySelectorAll('[id^="' + prefix + 'DropZone"]').forEach(function(dropZone) {
            const farmerId = dropZone.id.replace(prefix + 'DropZone', '');
            const input = document.getElementById(prefix + 'Input' + farmerId);
            const fileNameDisplay = document.getElementById(prefix + 'FileName' + farmerId);

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
        });
    });

    // Enable Bootstrap validation and confirmation modal
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }

                // Prevent default submission and show confirmation modal
                event.preventDefault();
                event.stopPropagation();

                // Get form values for confirmation
                const firstName = document.querySelector('input[name="first_name"]').value;
                const middleInitial = document.querySelector('input[name="middle_initial"]').value;
                const lastName = document.querySelector('input[name="last_name"]').value;
                const suffix = document.querySelector('select[name="suffix"]').value;
                const contactNumber = document.querySelector('input[name="contact_number"]').value;
                const cropSelect = document.querySelector('select[name="crop_id"]');
                const cropName = cropSelect.options[cropSelect.selectedIndex].text;
                const landArea = document.querySelector('input[name="land_area"]').value;
                const municipality = document.querySelector('select[name="municipality"]').value;
                const province = document.querySelector('select[name="province"]').value;

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
                document.getElementById('confirmLocation').textContent = municipality + ', ' + province;

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
            ['documentFileName', 'certificateFileName', 'barangayFileName', 'rsbsaFileName'].forEach(function(id) {
                document.getElementById(id).textContent = '';
            });
            document.getElementById('municipalitySelect').innerHTML = '<option value="" selected disabled>Select Municipality</option>';
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