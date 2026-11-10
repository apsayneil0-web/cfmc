<x-hub-tabs label="Members Central" :tabs="[
    [
        'route' => 'manager.user-management',
        'label' => 'User Management',
        'icon' => 'fas fa-user-cog',
    ],
    [
        'route' => 'manager.membership',
        'label' => 'Farmer Registration',
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
