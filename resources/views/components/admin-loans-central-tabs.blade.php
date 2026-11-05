@php
    $onLoanApproval = request()->routeIs('admin.loan-approval');
    $onDeniedTab = $onLoanApproval && request('tab') === 'denied';
@endphp
<x-hub-tabs label="Loans Central" :tabs="[
    ['route' => 'admin.loan-approval', 'label' => 'Loan Approval', 'icon' => 'fas fa-hand-holding-usd', 'active' => $onLoanApproval && ! $onDeniedTab],
    ['route' => 'admin.approved-loans', 'label' => 'Approved Loans', 'icon' => 'fas fa-check-double'],
    ['route' => 'admin.loan-approval', 'params' => ['tab' => 'denied'], 'label' => 'Denied Loans', 'icon' => 'fas fa-ban', 'active' => $onDeniedTab],
    ['route' => 'admin.cbu', 'label' => 'Capital Build Up', 'icon' => 'fas fa-piggy-bank'],
]" />
