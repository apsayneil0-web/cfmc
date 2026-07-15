@extends('manager.layout')

@section('title', 'Farmer Profile')
@section('header', 'Farmer Profile')

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
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Approved Farmer Members</h3>
            <form method="GET" action="{{ route('manager.farmer-profile') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search farmers..." class="form-control ps-5" style="min-width: 220px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search']))
                <a href="{{ route('manager.farmer-profile') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Location</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Contact</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Crop Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Land Area</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Documents</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Account</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($farmers as $farmer)
                @php
                    $docCount = collect([$farmer->documents_path, $farmer->certificate_of_title_path, $farmer->barangay_certification_path, $farmer->rsbsa_path])->filter()->count();
                @endphp
                <tr>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex align-items-center gap-3">
                            <x-avatar-initials :name="$farmer->full_name" color="primary" />
                            <div>
                                <p class="mb-0 fw-medium text-dark">{{ $farmer->full_name }}</p>
                                <p class="mb-0 text-muted small">Member ID: FM-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->municipality }}, {{ $farmer->province }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->contact_number }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->crop->name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $farmer->land_area }} hectares</td>
                    <td class="px-4 px-md-6 py-4">
                        @if($docCount === 4)
                        <span class="text-success"><i class="fas fa-check-circle"></i> Complete</span>
                        @else
                        <span class="text-warning"><i class="fas fa-exclamation-circle"></i> {{ $docCount }}/4 uploaded</span>
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        @if($farmer->account)
                        <x-status-badge :status="ucfirst($farmer->account->status)" />
                        @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">No Account</span>
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $farmer->id }}" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $farmer->id }}" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $farmer->id }}" />
                        </div>
                    </td>
                </tr>

                <!-- View Modal -->
                <x-modal id="viewModal{{ $farmer->id }}" title="Farmer Profile Details" size="modal-lg modal-dialog-scrollable">
                    <div class="mb-4">
                        <h4 class="text-sm fw-semibold text-dark mb-3">Personal Information</h4>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="text-muted small d-block">Full Name</label><p class="fw-medium mb-0">{{ $farmer->full_name }}</p></div>
                            <div class="col-md-4"><label class="text-muted small d-block">Member ID</label><p class="fw-medium mb-0">FM-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                            <div class="col-md-4"><label class="text-muted small d-block">Contact Number</label><p class="fw-medium mb-0">{{ $farmer->contact_number }}</p></div>
                            <div class="col-md-4"><label class="text-muted small d-block">Province</label><p class="fw-medium mb-0">{{ $farmer->province }}</p></div>
                            <div class="col-md-4"><label class="text-muted small d-block">Municipality</label><p class="fw-medium mb-0">{{ $farmer->municipality }}</p></div>
                            <div class="col-md-4"><label class="text-muted small d-block">Barangay</label><p class="fw-medium mb-0">{{ $farmer->barangay ?? '—' }}</p></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-sm fw-semibold text-dark mb-3">Farming Information</h4>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="text-muted small d-block">Crop Type</label><p class="fw-medium mb-0">{{ $farmer->crop->name }}</p></div>
                            <div class="col-md-4"><label class="text-muted small d-block">Land Area</label><p class="fw-medium mb-0">{{ $farmer->land_area }} hectares</p></div>
                            <div class="col-md-4"><label class="text-muted small d-block mb-1">Membership Status</label><x-status-badge :status="ucfirst($farmer->status)" /></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-sm fw-semibold text-dark mb-3">Documents</h4>
                        <div class="row g-3">
                            @php
                                $viewDocumentFields = [
                                    ['path' => 'documents_path', 'label' => 'Valid ID'],
                                    ['path' => 'certificate_of_title_path', 'label' => 'Certificate of Title'],
                                    ['path' => 'barangay_certification_path', 'label' => 'Barangay Certification of Land Possession'],
                                    ['path' => 'rsbsa_path', 'label' => 'RSBSA Number/ID'],
                                ];
                            @endphp
                            @foreach($viewDocumentFields as $doc)
                            <div class="col-12">
                                <label class="text-muted small d-block">{{ $doc['label'] }}</label>
                                <div class="mt-1">
                                    @if($farmer->{$doc['path']})
                                        @php
                                            $extension = pathinfo($farmer->{$doc['path']}, PATHINFO_EXTENSION);
                                            $filename = basename($farmer->{$doc['path']});
                                        @endphp
                                        @if(in_array($extension, ['jpg', 'jpeg', 'png']))
                                            <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-image me-1"></i> View Image
                                            </a>
                                        @elseif($extension == 'pdf')
                                            <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-file-pdf me-1"></i> View PDF
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file me-1"></i> View Document
                                            </a>
                                        @endif
                                        <a href="{{ asset('storage/' . $farmer->{$doc['path']}) }}" download="{{ $filename }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    @else
                                        <span class="text-muted">No document uploaded</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm fw-semibold text-dark mb-3">Login Account</h4>
                        @if($farmer->account)
                        <div class="row g-3">
                            <div class="col-md-6"><label class="text-muted small d-block">Username</label><p class="fw-medium mb-0">{{ $farmer->account->username }}</p></div>
                            <div class="col-md-6"><label class="text-muted small d-block">Email</label><p class="fw-medium mb-0">{{ $farmer->account->email }}</p></div>
                            <div class="col-md-6"><label class="text-muted small d-block mb-1">Account Status</label><x-status-badge :status="ucfirst($farmer->account->status)" /></div>
                        </div>
                        @else
                        <p class="text-muted mb-0">No login account linked. Create one from User Management.</p>
                        @endif
                    </div>
                </x-modal>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $farmer->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $farmer->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark" id="editModalLabel{{ $farmer->id }}">
                                    <i class="fas fa-edit me-2"></i>Edit Farmer Profile
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
                                            <select name="province" class="form-select" required>
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
                                            <label class="form-label fw-semibold">Barangay</label>
                                            <input type="text" name="barangay" class="form-control" value="{{ $farmer->barangay }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Land Area (hectares) <span class="text-danger">*</span></label>
                                            <input type="number" name="land_area" class="form-control" value="{{ $farmer->land_area }}" step="0.1" min="0" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Crop Type <span class="text-danger">*</span></label>
                                        <select name="crop_id" class="form-select" required>
                                            @foreach($crops as $crop)
                                                <option value="{{ $crop->id }}" {{ $farmer->crop_id == $crop->id ? 'selected' : '' }}>{{ $crop->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Document Upload -->
                                    @php
                                        $editDocumentFields = [
                                            ['field' => 'documents', 'path' => 'documents_path', 'label' => 'Upload Valid ID', 'prefix' => 'profileEditDocument'],
                                            ['field' => 'certificate_of_title', 'path' => 'certificate_of_title_path', 'label' => 'Upload Certificate of Title', 'prefix' => 'profileEditCertificate'],
                                            ['field' => 'barangay_certification', 'path' => 'barangay_certification_path', 'label' => 'Upload Barangay Certification of Land Possession', 'prefix' => 'profileEditBarangay'],
                                            ['field' => 'rsbsa', 'path' => 'rsbsa_path', 'label' => 'Upload RSBSA Number/ID', 'prefix' => 'profileEditRsbsa'],
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
                                            <input type="checkbox" class="form-check-input" id="profile_remove_{{ $doc['field'] }}{{ $farmer->id }}" name="remove_{{ $doc['field'] }}" value="1">
                                            <label class="form-check-label text-danger" for="profile_remove_{{ $doc['field'] }}{{ $farmer->id }}">Remove current file</label>
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
                                <p>Are you sure you want to archive this farmer member?</p>
                                <div class="bg-light p-3 rounded">
                                    <strong>{{ $farmer->full_name }}</strong><br>
                                    <small class="text-muted">{{ $farmer->municipality }}, {{ $farmer->province }}</small>
                                </div>
                                <p class="mt-3 mb-0 text-muted"><small><i class="fas fa-info-circle me-1"></i>Archived farmers are removed from this list and will need to be re-approved via Membership Registration to become active again.</small></p>
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
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No approved farmer members found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-info-banner variant="info" title="Farmer Profile" class="mt-6">
    This roster shows only approved farmer members. New registrations and membership approvals are handled from Membership Registration; archiving a farmer here removes them from this list until re-approved.
</x-info-banner>

<script>
    // Handle edit modal document uploads (one dropzone per document type, per farmer row)
    ['profileEditDocument', 'profileEditCertificate', 'profileEditBarangay', 'profileEditRsbsa'].forEach(function(prefix) {
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
</script>
@endsection
