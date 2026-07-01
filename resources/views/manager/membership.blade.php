@extends('layouts.manager')

@section('content')

<div class="container">
    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>Membership Registration</h1>
            <p>Manage farmer membership requests and approvals</p>
        </div>
        <button class="btn btn-primary" onclick="openModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Create Membership Request
        </button>
    </div>

    <!-- Search & Filter Section -->
    <div class="search-filter-card">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search farmer name..." class="form-control">
        </div>
        <div class="filter-box">
            <select id="statusFilter" class="form-control">
                <option value="">Active (Non-Archived)</option>
                <option value="Pending Approval">Pending Approval</option>
                <option value="Approved">Approved</option>
                <option value="Archived">Archived</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-wrapper">
            <table class="table" id="farmersTable">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Farmer Name</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th>Crop Type</th>
                        <th>Land Area</th>
                        <th>Status</th>
                        <th>Documents</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="farmersTableBody">
                    @forelse($farmers as $index => $farmer)
                    <tr class="farmer-row" data-name="{{ strtolower($farmer->first_name . ' ' . $farmer->last_name) }}" data-status="{{ $farmer->status }}">
                        <td>REQ-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $farmer->first_name }} {{ $farmer->middle_initial ?? '' }}. {{ $farmer->last_name }} {{ $farmer->suffix ?? '' }}</td>
                        <td>{{ $farmer->barangay }}, {{ $farmer->municipality }}, {{ $farmer->province }}</td>
                        <td>{{ $farmer->contact_number }}</td>
                        <td>{{ $farmer->crop->name ?? 'N/A' }}</td>
                        <td>{{ $farmer->land_area }} Hectares</td>
                        <td>
                            <span class="badge badge-pending-approval">
                                {{ $farmer->status }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewFarmer({{ $farmer->id }})">
                                View
                            </button>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-warning" onclick="editFarmer({{ $farmer->id }})">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="archiveFarmer({{ $farmer->id }}, '{{ $farmer->first_name }} {{ $farmer->last_name }}')">
                                    Archive
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No membership requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Membership Modal -->
<div id="membershipModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create Membership Request</h2>
            <button class="close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
        <form class="modal-form" id="membershipForm" method="POST" action="{{ route('membership.store') }}" enctype="multipart/form-data">
            @csrf
            <!-- Farmer Name Section -->
            <h4 class="form-section-title">Farmer Information</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                </div>

                <div class="form-group">
                    <label>Middle Initial (MI)</label>
                    <input type="text" name="middle_initial" class="form-control" placeholder="e.g., M" maxlength="5">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                </div>

                <div class="form-group">
                    <label>Suffix (Optional)</label>
                    <input type="text" name="suffix" class="form-control" placeholder="e.g., Jr., Sr., III">
                </div>
            </div>

            <!-- Address Section -->
            <h4 class="form-section-title">Address</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Barangay</label>
                    <input type="text" name="barangay" class="form-control" placeholder="Enter barangay" required>
                </div>

                <div class="form-group">
                    <label>Municipality</label>
                    <input type="text" name="municipality" class="form-control" placeholder="Enter municipality" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Province</label>
                    <input type="text" name="province" class="form-control" placeholder="Enter province" required>
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="tel" name="contact_number" class="form-control" placeholder="Enter contact number" required>
                </div>
            </div>

            <!-- Farm Details Section -->
            <h4 class="form-section-title">Farm Details</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Crop Type</label>
                    <select name="crop_type" class="form-control" required>
                        <option value="">Select Crop Type</option>
                        @foreach($crops as $crop)
                            <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Land Area (Hectares)</label>
                    <input type="number" name="land_area" class="form-control" min="0" step="0.01" placeholder="Enter land area" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Upload Documents</label>
                    <div class="file-upload">
                        <input type="file" name="documents[]" class="form-control" multiple>
                        <span class="file-hint">Supported: PDF, JPG, PNG (Max 10MB)</span>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    Submit Request
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    Cancel
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- View Farmer Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content modal-view">
        <div class="modal-header">
            <h2>Farmer Details</h2>
            <button class="close" onclick="closeViewModal()" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="viewModalBody">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Edit Farmer Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Membership Request</h2>
            <button class="close" onclick="closeEditModal()" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="editForm" method="POST" action="{{ route('membership.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="farmer_id" id="edit_farmer_id">
                <!-- Farmer Name Section -->
                <h4 class="form-section-title">Farmer Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Initial (MI)</label>
                        <input type="text" name="middle_initial" id="edit_middle_initial" class="form-control" maxlength="5">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Suffix (Optional)</label>
                        <input type="text" name="suffix" id="edit_suffix" class="form-control">
                    </div>
                </div>

                <!-- Address Section -->
                <h4 class="form-section-title">Address</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Barangay</label>
                        <input type="text" name="barangay" id="edit_barangay" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Municipality</label>
                        <input type="text" name="municipality" id="edit_municipality" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Province</label>
                        <input type="text" name="province" id="edit_province" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="tel" name="contact_number" id="edit_contact_number" class="form-control" required>
                    </div>
                </div>

                <!-- Farm Details Section -->
                <h4 class="form-section-title">Farm Details</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Crop Type</label>
                        <select name="crop_type" id="edit_crop_type" class="form-control" required>
                            <option value="">Select Crop Type</option>
                            @foreach($crops as $crop)
                                <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Land Area (Hectares)</label>
                        <input type="number" name="land_area" id="edit_land_area" class="form-control" min="0" step="0.01" required>
                    </div>
                </div>

                <!-- Current Documents Section -->
                <div class="form-row" id="currentDocumentsSection">
                    <div class="form-group">
                        <label>Current Documents</label>
                        <div class="current-documents" id="currentDocuments">
                            <!-- Populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Upload New Documents Section -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Upload New Documents</label>
                        <div class="file-upload">
                            <input type="file" name="documents[]" class="form-control" multiple>
                            <span class="file-hint">Supported: PDF, JPG, PNG (Max 10MB)</span>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="removed_documents" id="removed_documents" value="">

                <div class="form-actions">
                    <button type="submit" class="btn btn-success" onclick="confirmEdit(event)">
                        Update Request
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modern Confirmation Dialog -->
<div id="confirmDialog" class="confirm-dialog-overlay" style="display: none;">
    <div class="confirm-dialog">
        <div class="confirm-dialog-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <h3 class="confirm-dialog-title" id="confirmTitle">Confirm Action</h3>
        <p class="confirm-dialog-message" id="confirmMessage">Are you sure you want to proceed?</p>
        <div class="confirm-dialog-actions">
            <button type="button" class="btn btn-secondary" id="confirmCancel" onclick="closeConfirmDialog()">
                Cancel
            </button>
            <button type="button" class="btn btn-danger" id="confirmOk" onclick="confirmOk()">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- Additional Responsive Styles -->
<style>
    /* Modern Confirmation Dialog - Minimalist */
    .confirm-dialog-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.2s ease;
    }

    .confirm-dialog {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        border: 1px solid #e2e8f0;
        animation: slideUp 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .confirm-dialog-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        background: #fef3c7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .confirm-dialog-icon svg {
        width: 24px;
        height: 24px;
        color: #d97706;
    }

    .confirm-dialog-title {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 8px 0;
    }

    .confirm-dialog-message {
        font-size: 14px;
        color: #64748b;
        margin: 0 0 20px 0;
        line-height: 1.5;
    }

    .confirm-dialog-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .confirm-dialog-actions .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        min-width: 90px;
    }

    .confirm-dialog-actions .btn-danger {
        background: #dc2626;
        color: white;
        border: none;
    }

    .confirm-dialog-actions .btn-danger:hover {
        background: #b91c1c;
    }

    /* View Modal Styles */
    .modal-view .modal-body {
        padding: 0;
    }

    .detail-section {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .detail-section:last-child {
        border-bottom: none;
    }

    .detail-section-title {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 12px 0;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 14px;
        color: #1e293b;
        font-weight: 500;
    }

    /* Form Section Title */
    .form-section-title {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        margin: 16px 0 12px 0;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-section-title:first-of-type {
        margin-top: 0;
    }

    /* Alert Message */
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .alert-success {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    /* Badge Styles - Minimalist */
    .badge-approved {
        background-color: #dcfce7;
        color: #166534;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-archived {
        background-color: #f1f5f9;
        color: #64748b;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-pending-approval {
        background-color: #fef3c7;
        color: #b45309;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Page Header - Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header .btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* File Upload Hint */
    .file-hint {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
    }

    /* Documents Grid */
    .documents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 12px;
    }

    .document-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 12px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .document-link {
        display: block;
        width: 100%;
        text-decoration: none;
    }

    .document-preview {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    .document-file-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: #f1f5f9;
        border-radius: 6px;
    }

    .document-info {
        margin-top: 8px;
        text-align: center;
        width: 100%;
    }

    .document-name {
        display: block;
        font-size: 11px;
        color: #64748b;
        word-break: break-all;
        margin-bottom: 4px;
    }

    .btn-download {
        display: inline-block;
        padding: 4px 10px;
        font-size: 11px;
        color: #ffffff;
        background: #1e293b;
        border-radius: 4px;
        text-decoration: none;
    }

    .btn-download:hover {
        background: #334155;
    }

    /* Current Documents */
    .current-documents {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 12px;
    }

    .current-doc {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 8px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        position: relative;
    }

    .current-doc img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }

    .current-doc .doc-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #f1f5f9;
        border-radius: 4px;
    }

    .current-doc .doc-name {
        font-size: 10px;
        color: #64748b;
        margin-top: 4px;
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .current-doc .btn-remove {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 18px;
        height: 18px;
        padding: 0;
        font-size: 10px;
        line-height: 1;
        border-radius: 50%;
        background: #dc2626;
        color: white;
        border: none;
        cursor: pointer;
    }

    .current-doc .btn-remove:hover {
        background: #b91c1c;
    }

    /* Action Buttons Container */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    /* Button Icons */
    .btn svg {
        flex-shrink: 0;
    }

    /* Minimalist Button Variants */
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .btn-info {
        background: #f1f5f9;
        color: #1e293b;
    }

    .btn-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-success {
        background: #1e293b;
        color: #ffffff;
    }
</style>

<script>
// Global variables
let confirmCallback = null;
let farmersData = @json($farmers);

// Create Modal Functions
function openModal() {
    document.getElementById('membershipModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('membershipModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('membershipForm').reset();
}

// View Modal Functions
function viewFarmer(id) {
    const farmer = farmersData.find(f => f.id === id);
    if (!farmer) return;

    const cropName = farmer.crop ? farmer.crop.name : 'N/A';
    const fullName = `${farmer.first_name} ${farmer.middle_initial || ''} ${farmer.last_name} ${farmer.suffix || ''}`.replace(/\s+/g, ' ').trim();
    const fullAddress = `${farmer.barangay}, ${farmer.municipality}, ${farmer.province}`;

    const statusClass = farmer.status === 'Approved' ? 'badge-approved' : (farmer.status === 'Archived' ? 'badge-archived' : 'badge-pending-approval');

    const html = `
        <div class="detail-section">
            <h4 class="detail-section-title">Personal Information</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Full Name</span>
                    <span class="detail-value">${fullName}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Contact Number</span>
                    <span class="detail-value">${farmer.contact_number}</span>
                </div>
            </div>
        </div>
        <div class="detail-section">
            <h4 class="detail-section-title">Address</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Barangay</span>
                    <span class="detail-value">${farmer.barangay}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Municipality</span>
                    <span class="detail-value">${farmer.municipality}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Province</span>
                    <span class="detail-value">${farmer.province}</span>
                </div>
            </div>
        </div>
        <div class="detail-section">
            <h4 class="detail-section-title">Farm Details</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Crop Type</span>
                    <span class="detail-value">${cropName}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Land Area</span>
                    <span class="detail-value">${farmer.land_area} Hectares</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="badge ${statusClass}">${farmer.status}</span>
                </div>
            </div>
        </div>
        <div class="detail-section">
            <h4 class="detail-section-title">Documents</h4>
            ${farmer.documents ? (() => {
                try {
                    const docs = typeof farmer.documents === 'string' ? JSON.parse(farmer.documents) : farmer.documents;
                    if (docs && docs.length > 0) {
                        return '<div class="documents-grid">' + docs.map(doc => {
                            const ext = doc.split('.').pop().toLowerCase();
                            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
                            const fileUrl = `/photo/documents/${doc}`;
                            if (isImage) {
                                return `<div class="document-item">
                                    <a href="${fileUrl}" target="_blank" class="document-link">
                                        <img src="${fileUrl}" alt="${doc}" class="document-preview">
                                    </a>
                                    <div class="document-info">
                                        <span class="document-name">${doc}</span>
                                        <a href="${fileUrl}" download="${doc}" class="btn-download">Download</a>
                                    </div>
                                </div>`;
                            } else {
                                return `<div class="document-item">
                                    <div class="document-file-icon">📄</div>
                                    <div class="document-info">
                                        <span class="document-name">${doc}</span>
                                        <a href="${fileUrl}" download="${doc}" class="btn-download">Download</a>
                                    </div>
                                </div>`;
                            }
                        }).join('') + '</div>';
                    }
                    return '<span class="detail-value">No documents uploaded</span>';
                } catch(e) {
                    return '<span class="detail-value">No documents uploaded</span>';
                }
            })() : '<span class="detail-value">No documents uploaded</span>'}
        </div>
    `;

    document.getElementById('viewModalBody').innerHTML = html;
    document.getElementById('viewModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Edit Modal Functions
function editFarmer(id) {
    const farmer = farmersData.find(f => f.id === id);
    if (!farmer) return;

    document.getElementById('edit_farmer_id').value = farmer.id;
    document.getElementById('edit_first_name').value = farmer.first_name;
    document.getElementById('edit_middle_initial').value = farmer.middle_initial || '';
    document.getElementById('edit_last_name').value = farmer.last_name;
    document.getElementById('edit_suffix').value = farmer.suffix || '';
    document.getElementById('edit_barangay').value = farmer.barangay;
    document.getElementById('edit_municipality').value = farmer.municipality;
    document.getElementById('edit_province').value = farmer.province;
    document.getElementById('edit_contact_number').value = farmer.contact_number;
    document.getElementById('edit_crop_type').value = farmer.crop_type;
    document.getElementById('edit_land_area').value = farmer.land_area;

    // Populate current documents
    document.getElementById('removed_documents').value = '';
    const currentDocsContainer = document.getElementById('currentDocuments');
    const currentDocsSection = document.getElementById('currentDocumentsSection');

    if (farmer.documents && farmer.documents.length > 0) {
        currentDocsSection.style.display = 'block';
        currentDocsContainer.innerHTML = farmer.documents.map(doc => {
            const ext = doc.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
            const fileUrl = `/photo/documents/${doc}`;

            if (isImage) {
                return `<div class="current-doc" data-doc="${doc}">
                    <button type="button" class="btn-remove" onclick="removeDocument('${doc}')">&times;</button>
                    <a href="${fileUrl}" target="_blank">
                        <img src="${fileUrl}" alt="${doc}">
                    </a>
                    <span class="doc-name">${doc}</span>
                </div>`;
            } else {
                return `<div class="current-doc" data-doc="${doc}">
                    <button type="button" class="btn-remove" onclick="removeDocument('${doc}')">&times;</button>
                    <div class="doc-icon">📄</div>
                    <span class="doc-name">${doc}</span>
                </div>`;
            }
        }).join('');
    } else {
        currentDocsSection.style.display = 'none';
        currentDocsContainer.innerHTML = '';
    }

    document.getElementById('editModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function removeDocument(docName) {
    const removedInput = document.getElementById('removed_documents');
    const currentRemoved = removedInput.value ? removedInput.value.split(',') : [];

    if (!currentRemoved.includes(docName)) {
        currentRemoved.push(docName);
        removedInput.value = currentRemoved.join(',');
    }

    // Hide the document
    const docElement = document.querySelector(`.current-doc[data-doc="${docName}"]`);
    if (docElement) {
        docElement.style.display = 'none';
    }
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('editForm').reset();
}

function confirmEdit(event) {
    event.preventDefault();

    const form = document.getElementById('editForm');
    const firstName = form.querySelector('input[name="first_name"]').value;
    const lastName = form.querySelector('input[name="last_name"]').value;

    showConfirmDialog(
        'Update Membership Request',
        `Are you sure you want to update the membership request for ${firstName} ${lastName}?`,
        function() {
            form.submit();
        }
    );
}

// Archive Functions
function archiveFarmer(id, name) {
    showConfirmDialog(
        'Archive Membership Request',
        `Are you sure you want to archive the membership request for ${name}? This action can be undone later.`,
        function() {
            // Submit archive request via AJAX
            fetch(`/membership/${id}/archive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error archiving membership request');
                }
            })
            .catch(error => {
                alert('Error archiving membership request');
            });
        }
    );
}

// Modern Confirmation Dialog
function showConfirmDialog(title, message, callback) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    confirmCallback = callback;
    document.getElementById('confirmDialog').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeConfirmDialog() {
    document.getElementById('confirmDialog').style.display = 'none';
    confirmCallback = null;
    document.body.style.overflow = '';
}

function confirmOk() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmDialog();
}

// Original confirmSubmission for create form
function confirmSubmission(event) {
    event.preventDefault();

    const form = document.getElementById('membershipForm');
    const firstName = form.querySelector('input[name="first_name"]').value;
    const lastName = form.querySelector('input[name="last_name"]').value;
    const cropType = form.querySelector('select[name="crop_type"] option:checked').text;

    showConfirmDialog(
        'Submit Membership Request',
        `Are you sure you want to submit this membership request for ${firstName} ${lastName}?\n\nCrop Type: ${cropType}`,
        function() {
            form.submit();
        }
    );
}

// Attach confirmation to form submit
document.getElementById('membershipForm').addEventListener('submit', confirmSubmission);

// Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('farmersTableBody');
    const rows = tableBody.querySelectorAll('.farmer-row');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusValue = statusFilter.value;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const status = row.getAttribute('data-status');

            const matchesSearch = name.includes(searchTerm);

            // If no filter selected, hide archived by default
            let matchesStatus = false;
            if (statusValue === '') {
                // Show all except archived
                matchesStatus = status !== 'Archived';
            } else {
                // Show selected status
                matchesStatus = status === statusValue;
            }

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide empty message
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        const emptyMessage = tableBody.querySelector('.empty-message');

        if (visibleRows.length === 0) {
            if (!emptyMessage) {
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-message';
                emptyRow.innerHTML = '<td colspan="9" class="text-center">No matching records found.</td>';
                tableBody.appendChild(emptyRow);
            }
        } else if (emptyMessage) {
            emptyMessage.remove();
        }
    }

    // Add event listeners first
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);

    // Initial filter to hide archived
    filterTable();
});

// Window click handlers
window.onclick = function(event) {
    let modals = ['membershipModal', 'viewModal', 'editModal'];
    modals.forEach(modalId => {
        let modal = document.getElementById(modalId);
        if (event.target == modal) {
            if (modalId === 'membershipModal') closeModal();
            else if (modalId === 'viewModal') closeViewModal();
            else if (modalId === 'editModal') closeEditModal();
        }
    });

    let confirmDialog = document.getElementById('confirmDialog');
    if (event.target == confirmDialog) {
        closeConfirmDialog();
    }
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeViewModal();
        closeEditModal();
        closeConfirmDialog();
    }
});
</script>

@endsection