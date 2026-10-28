@props(['status'])

@php
    $normalized = strtolower(trim($status ?? ''));
    // One meaning per color across every module:
    // wheat (warning) = waiting, green (success) = good, clay (danger) = needs action,
    // teal (info) = finished or informational, grey (secondary) = out of the way.
    $map = [
        'approved' => 'success', 'active' => 'success', 'current' => 'success',
        'published' => 'success', 'available' => 'success', 'resolved' => 'success', 'paid' => 'success',
        'ready to finalize' => 'success',
        'pending' => 'warning', 'pending disbursement' => 'warning', 'submitted' => 'warning', 'draft' => 'warning',
        'collecting members' => 'warning',
        'denied' => 'danger', 'rejected' => 'danger', 'overdue' => 'danger', 'locked' => 'danger',
        'maintenance' => 'danger', 'replaceable parts' => 'danger',
        'completed' => 'info', 'in progress' => 'info', 'in use' => 'info', 'fully paid' => 'info',
        'with administrator' => 'info', 'partial' => 'info',
        'archived' => 'secondary', 'inactive' => 'secondary', 'non-member' => 'secondary', 'cancelled' => 'secondary',
        'finalized' => 'secondary', 'not yet used' => 'secondary',
        'member' => 'primary', 'new' => 'primary', 'assigned' => 'primary',
        'cbu contribution' => 'info', 'cbu expense' => 'danger', 'billing' => 'info', 'loan payment' => 'primary', 'interest charge' => 'warning',
        'operational expense' => 'warning',
        'harvest payment (member)' => 'success', 'harvest payment (non-member)' => 'info',
        'routine inspection' => 'success', 'basic maintenance' => 'info', 'full maintenance' => 'warning',
        'comprehensive servicing' => 'danger',
    ];
    $variant = $map[$normalized] ?? 'secondary';
@endphp

<span {{ $attributes->merge(['class' => "badge bg-$variant-subtle text-$variant border border-$variant-subtle"]) }}>
    {{ $status }}
</span>
