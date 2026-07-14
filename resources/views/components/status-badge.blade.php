@props(['status'])

@php
    $normalized = strtolower(trim($status ?? ''));
    $map = [
        'approved' => 'success', 'active' => 'success', 'current' => 'success', 'completed' => 'success',
        'published' => 'success', 'available' => 'success', 'resolved' => 'success', 'paid' => 'success', 'fully paid' => 'success',
        'pending' => 'warning', 'in progress' => 'warning', 'draft' => 'warning', 'maintenance' => 'warning',
        'denied' => 'danger', 'rejected' => 'danger', 'overdue' => 'danger', 'locked' => 'danger', 'replaceable parts' => 'danger',
        'archived' => 'secondary', 'inactive' => 'secondary', 'non-member' => 'secondary', 'cancelled' => 'secondary',
        'member' => 'primary', 'in use' => 'primary', 'new' => 'primary', 'assigned' => 'primary',
        'cbu contribution' => 'info', 'billing' => 'info', 'loan payment' => 'primary', 'interest charge' => 'warning',
    ];
    $variant = $map[$normalized] ?? 'secondary';
@endphp

<span {{ $attributes->merge(['class' => "badge bg-$variant-subtle text-$variant border border-$variant-subtle"]) }}>
    {{ $status }}
</span>
