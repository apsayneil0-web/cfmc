@extends('layouts.manager')

@section('content')

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>Farmer Profile Management</h1>
            <p>Manage farmer profiles and their information</p>
        </div>
        <button class="btn btn-primary" onclick="openModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Add Farmer
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
                <option>Active</option>
                <option>Inactive</option>
                <option>Archived</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Farmer ID</th>
                        <th>Name</th>
                        <th>Barangay</th>
                        <th>Contact</th>
                        <th>Crop Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>F001</td>
                        <td>Juan Dela Cruz</td>
                        <td>San Jose</td>
                        <td>09123456789</td>
                        <td>Rice</td>
                        <td>
                            <span class="badge badge-approved">Active</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-warning">Edit</button>
                                <button class="btn btn-sm btn-danger">Archive</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Farmer Modal -->
<div id="farmerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Farmer</h2>
            <button class="close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
        <form class="modal-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Farmer Full Name</label>
                    <input type="text" class="form-control" placeholder="Enter farmer's full name">
                </div>

                <div class="form-group">
                    <label>Barangay</label>
                    <input type="text" class="form-control" placeholder="Enter barangay">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="tel" class="form-control" placeholder="Enter contact number">
                </div>

                <div class="form-group">
                    <label>Crop Type</label>
                    <select class="form-control">
                        <option value="">Select Crop Type</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Land Area (Hectares)</label>
                    <input type="number" class="form-control" min="0" step="0.01" placeholder="Enter land area">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    Save Farmer
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    Cancel
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('farmerModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('farmerModal').style.display = 'none';
    document.body.style.overflow = '';
}

window.onclick = function(event) {
    let modal = document.getElementById('farmerModal');
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