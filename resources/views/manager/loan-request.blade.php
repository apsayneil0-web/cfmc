@extends('layouts.manager')

@section('content')

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>Loan Request Management</h1>
            <p>Manage and process farmer loan requests</p>
        </div>
        <button class="btn btn-primary" onclick="openModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Create Loan Request
        </button>
    </div>

    <!-- Search & Filter Section -->
    <div class="search-filter-card">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search farmer name..." class="form-control">
        </div>
        <div class="filter-box">
            <select class="form-control">
                <option>All Status</option>
                <option>Pending</option>
                <option>Approved</option>
                <option>Rejected</option>
                <option>Paid</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Farmer</th>
                        <th>Amount</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>LN-001</td>
                        <td>Juan Dela Cruz</td>
                        <td>₱50,000</td>
                        <td>Farm Equipment</td>
                        <td>
                            <span class="badge badge-pending">Pending</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-info">View</button>
                                <button class="btn btn-sm btn-warning">Edit</button>
                                <button class="btn btn-sm btn-danger">Reject</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Loan Request Modal -->
<div id="loanModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create Loan Request</h2>
            <button class="close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
        <form class="modal-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Select Farmer</label>
                    <select class="form-control">
                        <option value="">Select Farmer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Loan Amount (₱)</label>
                    <input type="number" class="form-control" min="0" step="0.01" placeholder="Enter loan amount">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Purpose</label>
                    <select class="form-control">
                        <option value="">Select Purpose</option>
                        <option value="equipment">Farm Equipment</option>
                        <option value="seeds">Seeds & Fertilizers</option>
                        <option value="livestock">Livestock</option>
                        <option value="irrigation">Irrigation System</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Payment Term</label>
                    <select class="form-control">
                        <option value="">Select Payment Term</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                        <option value="18">18 Months</option>
                        <option value="24">24 Months</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Expected Repayment Date</label>
                    <input type="date" class="form-control">
                </div>

                <div class="form-group">
                    <label>Upload Documents</label>
                    <div class="file-upload">
                        <input type="file" class="form-control" multiple>
                        <span class="file-hint">Supported: PDF, JPG, PNG (Max 10MB)</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Additional Notes</label>
                <textarea class="form-control" rows="3" placeholder="Enter any additional notes..."></textarea>
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

<style>
    /* Badge styles */
    .badge-pending {
        background: #fff3cd;
        color: #856404;
    }

    .badge-approved {
        background: #d4edda;
        color: #155724;
    }

    .badge-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .badge-paid {
        background: #d1ecf1;
        color: #0c5460;
    }
</style>

<script>
function openModal() {
    document.getElementById('loanModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('loanModal').style.display = 'none';
    document.body.style.overflow = '';
}

window.onclick = function(event) {
    let modal = document.getElementById('loanModal');
    if(event.target == modal){
        closeModal();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

@endsection