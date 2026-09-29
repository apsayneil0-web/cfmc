@php
    $onMembers = request()->routeIs('admin.members');
    $onApproval = request()->routeIs('admin.membership-approval');
@endphp

<x-hub-tabs label="Members Central" :tabs="[
    [
        'route' => 'admin.members',
        'label' => 'Member Information',
        'icon' => 'fas fa-users',
        'active' => $onMembers && request('status') !== 'approved',
        'count' => \App\Models\Farmer::whereIn('status', ['approved', 'archived'])->count(),
    ],
    [
        'route' => 'admin.members',
        'params' => ['status' => 'approved'],
        'label' => 'Approved Membership',
        'icon' => 'fas fa-user-check',
        'active' => $onMembers && request('status') === 'approved',
        'count' => \App\Models\Farmer::where('status', 'approved')->count(),
    ],
    [
        'route' => 'admin.membership-approval',
        'label' => 'Membership Approval',
        'icon' => 'fas fa-user-clock',
        'active' => $onApproval && request('status') !== 'rejected',
        'count' => \App\Models\Farmer::where('status', 'pending')->count(),
    ],
    [
        'route' => 'admin.membership-approval',
        'params' => ['status' => 'rejected'],
        'label' => 'Rejected Applications',
        'icon' => 'fas fa-user-xmark',
        'active' => $onApproval && request('status') === 'rejected',
        'count' => \App\Models\Farmer::where('status', 'rejected')->count(),
    ],
]" />
