@extends('farmer.layout')

@section('title', 'Request Schedule')
@section('header', 'Machinery Schedule Request')

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

<!-- Machinery Availability -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Machinery Availability</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($machinery as $machine)
        <div class="p-4 rounded-lg border border-gray-200 d-flex align-items-center justify-content-between">
            <div>
                <p class="fw-semibold mb-1">{{ $machine['name'] }}</p>
                <x-status-badge :status="$machine['status']" />
            </div>
            <i class="fas fa-tractor text-muted" style="font-size: 1.5rem;"></i>
        </div>
        @endforeach
    </div>
</div>

<!-- Request Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Request a Schedule</h3>
    <form action="{{ route('farmer.schedule.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Machinery Type <span class="text-danger">*</span></label>
                <div class="d-flex flex-column gap-2">
                    @foreach($machinery as $machine)
                    <div class="form-check border rounded-lg p-2 {{ $machine['status'] == 'Unavailable' ? 'opacity-50' : '' }}">
                        <input class="form-check-input" type="radio" name="machinery" id="machinery{{ $loop->index }}"
                            value="{{ $machine['name'] }}" {{ $machine['status'] == 'Unavailable' ? 'disabled' : '' }}
                            {{ old('machinery') == $machine['name'] ? 'checked' : '' }} required>
                        <label class="form-check-label" for="machinery{{ $loop->index }}">
                            {{ $machine['name'] }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Land Size (hectares) <span class="text-danger">*</span></label>
                <input type="number" step="0.1" min="0.1" name="land_size" class="form-control" value="{{ old('land_size') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Preferred Date <span class="text-danger">*</span></label>
                <input type="date" name="scheduled_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('scheduled_date') }}" required>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane me-1"></i> Submit Request
            </button>
            <button type="reset" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Unselect
            </button>
        </div>
    </form>
</div>

<!-- My Schedule Requests -->
<div class="section-card">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">My Schedule Requests</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machinery</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Land Size</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Submitted</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-dark fw-medium">{{ $req->machinery }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->land_size }} ha</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->scheduled_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->created_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($req->status)" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 px-md-6 py-6 text-center text-muted">No schedule requests yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
