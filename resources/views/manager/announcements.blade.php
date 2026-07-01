@extends('layouts.manager')

@section('content')

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>Announcement Management</h1>
            <p>Create and manage announcements for farmers</p>
        </div>
        <button class="btn btn-primary" onclick="openModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Create Announcement
        </button>
    </div>

    <!-- Search & Filter Section -->
    <div class="search-filter-card">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search announcement title..." class="form-control">
        </div>
        <div class="filter-box">
            <select class="form-control">
                <option>All Status</option>
                <option>Published</option>
                <option>Draft</option>
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
                        <th>Title</th>
                        <th>Date</th>
                        <th>Recipients</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Meeting Schedule Update</td>
                        <td>June 20, 2026</td>
                        <td>All Farmers</td>
                        <td>
                            <span class="badge badge-approved">Published</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-info">View</button>
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

<!-- Create Announcement Modal -->
<div id="announcementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create Announcement</h2>
            <button class="close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>

        <form class="modal-form">
            <div class="form-group">
                <label>Announcement Title</label>
                <input type="text" class="form-control" placeholder="Enter announcement title">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Recipients</label>
                    <select class="form-control">
                        <option value="">Select Recipients</option>
                        <option value="all">All Farmers</option>
                        <option value="active">Active Farmers</option>
                        <option value="pending">Pending Approval</option>
                        <option value="specific">Specific Group</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control">
                        <option value="published">Publish Now</option>
                        <option value="draft">Save as Draft</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Announcement Content</label>
                <textarea class="form-control" rows="5" placeholder="Write your announcement here..."></textarea>
            </div>

            <div class="form-group">
                <label>Attach Files (Optional)</label>
                <div class="file-upload">
                    <input type="file" class="form-control" multiple>
                    <span class="file-hint">Supported: PDF, JPG, PNG (Max 10MB)</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    Publish Announcement
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Badge styles */
    .badge-published {
        background: #d4edda;
        color: #155724;
    }

    .badge-draft {
        background: #e2e3e5;
        color: #383d41;
    }

    .badge-archived {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<script>
function openModal() {
    document.getElementById('announcementModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('announcementModal').style.display = 'none';
    document.body.style.overflow = '';
}

window.onclick = function(event) {
    let modal = document.getElementById('announcementModal');
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