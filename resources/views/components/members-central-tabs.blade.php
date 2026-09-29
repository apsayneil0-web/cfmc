<x-hub-tabs label="Members Central" :tabs="[
    [
        'route' => 'manager.user-management',
        'label' => 'User Management',
        'icon' => 'fas fa-user-cog',
        'count' => \App\Models\User::where('roleID', 3)->where('status', '!=', 'archived')->count(),
    ],
    [
        'route' => 'manager.membership',
        'label' => 'Membership Registration',
        'icon' => 'fas fa-user-plus',
        'count' => \App\Models\Farmer::where('status', 'pending')->count(),
    ],
    [
        'route' => 'manager.farmer-profile',
        'label' => 'Farmer Profile',
        'icon' => 'fas fa-id-badge',
        'count' => \App\Models\Farmer::where('status', 'approved')->count(),
    ],
]" />
