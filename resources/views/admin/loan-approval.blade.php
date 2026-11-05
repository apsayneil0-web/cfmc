@extends('admin.layout')

@section('title', 'Loan Approval')
@section('header', 'Farmer Loan Approval')

@php
    $daysWaiting = fn ($date) => (int) $date->copy()->startOfDay()->diffInDays(today());
    $waitingLabel = fn ($days) => $days === 0 ? 'Today' : $days.' '.\Illuminate\Support\Str::plural('day', $days).' waiting';
@endphp

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
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="la-top">
    <p class="la-sub mb-0">Requests checked by the Manager, waiting for your final decision.</p>
    <div class="la-counts" aria-label="Loan request counts">
        <span class="la-count"><i class="la-dot wait"></i><b>{{ $pendingRegularCount + $pendingBatchMemberCount }}</b> waiting</span>
        <span class="la-count"><i class="la-dot ok"></i><b>{{ $approvedThisMonth }}</b> approved this month</span>
        <span class="la-count"><i class="la-dot no"></i><b>{{ $deniedThisMonth }}</b> denied this month</span>
    </div>
</div>

<div class="la-bar">
    <div class="la-tabs" role="tablist">
        <a role="tab" href="{{ route('admin.loan-approval', array_filter(['tab' => 'regular', 'search' => request('search')])) }}" class="la-tab" aria-selected="{{ $tab === 'regular' ? 'true' : 'false' }}">
            Regular loans <span class="la-n">{{ $pendingRegularCount }}</span>
        </a>
        <a role="tab" href="{{ route('admin.loan-approval', array_filter(['tab' => 'batch', 'search' => request('search')])) }}" class="la-tab" aria-selected="{{ $tab === 'batch' ? 'true' : 'false' }}">
            Batch loans <span class="la-n">{{ $batchGroups->total() }}</span>
        </a>
    </div>
    <form method="GET" action="{{ route('admin.loan-approval') }}" class="d-flex flex-wrap align-items-center gap-3">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <x-live-search placeholder="Search farmer or batch..." width="280px" />
    </form>
</div>
@if(request()->filled('search'))
<p class="text-sm text-muted mb-3">Showing requests matching &ldquo;{{ request('search') }}&rdquo;</p>
@endif

@php
    $items = $tab === 'batch' ? $batchGroups : $requests;
@endphp

<div class="la-split">
    {{-- Queue --}}
    <div class="la-queue">
        <div class="la-queue-head">
            <span>Waiting &middot; newest first</span>
            <span>{{ $items->total() }} {{ \Illuminate\Support\Str::plural($tab === 'batch' ? 'batch' : 'request', $items->total()) }}</span>
        </div>

        @forelse($items as $item)
            @if($tab === 'batch')
                @php $days = $daysWaiting($item->loanRequests->min('created_at')); @endphp
                <button type="button" class="la-item" data-panel="panel-b{{ $item->id }}" aria-current="{{ $loop->first ? 'true' : 'false' }}">
                    <span class="la-avatar">B{{ $item->batch_number }}</span>
                    <span class="la-item-main">
                        <span class="la-item-name">{{ $item->label }}</span>
                        <span class="la-item-meta">{{ $item->loanRequests->count() }}/{{ $item->capacity }} members</span>
                    </span>
                    <span class="la-item-amt">
                        {{ peso($item->loanRequests->sum('requested_amount')) }}
                        <span class="la-age {{ $days >= 5 ? 'old' : '' }}">{{ $waitingLabel($days) }}</span>
                    </span>
                </button>
            @else
                @php $days = $daysWaiting($item->created_at); @endphp
                <button type="button" class="la-item" data-panel="panel-r{{ $item->id }}" aria-current="{{ $loop->first ? 'true' : 'false' }}">
                    <x-avatar-initials :name="$item->farmer->full_name" />
                    <span class="la-item-main">
                        <span class="la-item-name">{{ $item->farmer->full_name }}</span>
                        <span class="la-item-meta">{{ $item->purpose }} &middot; {{ $item->repayment_terms_months }} mo</span>
                    </span>
                    <span class="la-item-amt">
                        {{ peso($item->requested_amount) }}
                        <span class="la-age {{ $days >= 5 ? 'old' : '' }}">{{ $waitingLabel($days) }}</span>
                    </span>
                </button>
            @endif
        @empty
            <p class="la-empty">
                @if(request()->filled('search'))
                    No waiting {{ $tab === 'batch' ? 'batches' : 'requests' }} match your search.
                @elseif($tab === 'batch')
                    No batch is waiting. A batch shows up here once all its member slots are filled.
                @else
                    Nothing waiting. New requests appear here after the Manager checks them.
                @endif
            </p>
        @endforelse

        @if($items->hasPages())
        <div class="la-queue-pages">
            <x-pagination-footer :paginator="$items" />
        </div>
        @endif
    </div>

    {{-- Detail panels: one per queue item, only the selected one is shown --}}
    <div class="la-detail-wrap" id="laDetail">
        @if($items->isEmpty())
            <div class="la-detail"><div class="la-body"><p class="text-muted mb-0">Select a request on the left to review it.</p></div></div>
        @endif

        @if($tab === 'regular')
            @foreach($requests as $req)
                @php
                    $farmer = $req->farmer;
                    $isMember = $farmer->status === 'approved';
                    $cbuBalance = (float) ($farmer->cbu->balance ?? 0);
                    $inProcess = $farmer->loanInProcess($req->id);
                @endphp
                <section class="la-detail" id="panel-r{{ $req->id }}" @if(! $loop->first) hidden @endif aria-label="{{ $farmer->full_name }}'s loan request">
                    <div class="la-head">
                        <div class="la-who">
                            <x-avatar-initials :name="$farmer->full_name" :size="12" />
                            <div>
                                <h2>{{ $farmer->full_name }}</h2>
                                <p>Applied {{ $req->created_at->format('M d, Y') }} &middot; encoded by {{ $req->requestedBy->name ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="la-amount"><small>Requested</small><strong>{{ peso($req->requested_amount) }}</strong></div>
                    </div>

                    <div class="la-body">
                        <div>
                            <p class="la-sec">Loan details</p>
                            <div class="la-facts">
                                <div><small>Purpose</small><b>{{ $req->purpose }}</b></div>
                                <div><small>Repayment terms</small><b>{{ $req->repayment_terms_months }} months</b></div>
                                <div><small>Collateral</small><b>{{ $req->collateral ?: '—' }}</b></div>
                                <div><small>Monthly (approx.)</small><b>{{ $req->repayment_terms_months ? peso($req->requested_amount / $req->repayment_terms_months) : '—' }}</b></div>
                                <div><small>Supporting document</small><b>@if($req->documents_path)<a href="{{ asset('storage/'.$req->documents_path) }}" data-file-viewer data-viewer-title="Supporting document — {{ $farmer->full_name }}">View document</a>@else &mdash; @endif</b></div>
                            </div>
                        </div>

                        <div>
                            <p class="la-sec">Farmer at a glance</p>
                            <div class="la-facts">
                                <div><small>Membership</small><x-status-badge :status="$isMember ? 'Member' : 'Non-member'" /></div>
                                <div><small>Land area</small><b>{{ rtrim(rtrim(number_format((float) $farmer->land_area, 2), '0'), '.') }} ha</b></div>
                                <div><small>CBU balance</small><b>{{ peso($cbuBalance) }}</b></div>
                                <div><small>Other loan in process</small><b>{{ $inProcess ? 'Yes' : 'None' }}</b></div>
                            </div>
                        </div>

                        <div>
                            <p class="la-sec">Before you decide</p>
                            <div class="la-checks">
                                <div class="la-check ok"><i>✓</i><span>Checked by the Manager</span></div>
                                <div class="la-check {{ $isMember ? 'ok' : 'warn' }}"><i>{{ $isMember ? '✓' : '!' }}</i><span>{{ $isMember ? 'Approved coop member' : 'Not yet an approved member' }}</span></div>
                                <div class="la-check {{ $req->documents_path ? 'ok' : 'warn' }}"><i>{{ $req->documents_path ? '✓' : '!' }}</i><span>{{ $req->documents_path ? 'Supporting document attached' : 'No supporting document uploaded' }}</span></div>
                                <div class="la-check {{ $inProcess ? 'warn' : 'ok' }}"><i>{{ $inProcess ? '!' : '✓' }}</i><span>{{ $inProcess ? 'Already has a loan in process ('.$inProcess.'). Approving will be blocked.' : 'No other loan in process' }}</span></div>
                            </div>
                        </div>
                    </div>

                    <form id="approveForm{{ $req->id }}" action="{{ route('admin.loan-approval.approve', $req) }}" method="POST" class="d-none">
                        @csrf
                        @method('PATCH')
                    </form>
                    <form id="denyForm{{ $req->id }}" action="{{ route('admin.loan-approval.deny', $req) }}" method="POST" class="la-decide" data-decide
                          data-subject="{{ $farmer->full_name }}'s loan of {{ peso($req->requested_amount) }}" data-approve-form="approveForm{{ $req->id }}" data-approve-label="Approve loan">
                        @csrf
                        @method('PATCH')
                    </form>
                </section>
            @endforeach
        @else
            @foreach($batchGroups as $batch)
                @php
                    $members = $batch->loanRequests;
                    $total = $members->sum('requested_amount');
                @endphp
                <section class="la-detail" id="panel-b{{ $batch->id }}" @if(! $loop->first) hidden @endif aria-label="{{ $batch->label }}">
                    <div class="la-head">
                        <div class="la-who">
                            <span class="la-avatar la-avatar-lg">B{{ $batch->batch_number }}</span>
                            <div>
                                <h2>{{ $batch->label }}</h2>
                                <p>{{ $members->count() }} of {{ $batch->capacity }} members &middot; first applied {{ $members->min('created_at')->format('M d, Y') }}</p>
                                <div class="la-progress" aria-hidden="true"><div style="width: {{ min(100, $batch->capacity ? $members->count() / $batch->capacity * 100 : 100) }}%"></div></div>
                            </div>
                        </div>
                        <div class="la-amount"><small>Batch total</small><strong>{{ peso($total) }}</strong></div>
                    </div>

                    <div class="la-body">
                        <div>
                            <p class="la-sec">Members</p>
                            <div class="la-members">
                                <table>
                                    <thead>
                                        <tr><th>Farmer</th><th>Amount</th><th>Purpose</th><th>Terms</th><th>Membership</th><th>Collateral</th><th>Document</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($members as $member)
                                            <tr>
                                                <td class="fw-medium">{{ $member->farmer->full_name }}</td>
                                                <td>{{ peso($member->requested_amount) }}</td>
                                                <td class="text-muted">{{ $member->purpose }}</td>
                                                <td class="text-muted">{{ $member->repayment_terms_months }} mo</td>
                                                <td><x-status-badge :status="$member->farmer->status === 'approved' ? 'Member' : 'Non-member'" /></td>
                                                <td class="text-muted">{{ $member->collateral ?: '—' }}</td>
                                                <td class="text-nowrap">@if($member->documents_path)<a href="{{ asset('storage/'.$member->documents_path) }}" data-file-viewer data-viewer-title="Supporting document — {{ $member->farmer->full_name }}">View</a>@else <span class="text-muted">&mdash;</span> @endif</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr><td>Total</td><td>{{ peso($total) }}</td><td colspan="5"></td></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="la-checks">
                            <div class="la-check ok"><i>✓</i><span>All {{ $batch->capacity }} slots filled and checked by the Manager</span></div>
                        </div>
                    </div>

                    <form id="approveFormBatch{{ $batch->id }}" action="{{ route('admin.loan-approval.batch-approve', $batch) }}" method="POST" class="d-none">
                        @csrf
                        @method('PATCH')
                    </form>
                    <form id="denyFormBatch{{ $batch->id }}" action="{{ route('admin.loan-approval.batch-deny', $batch) }}" method="POST" class="la-decide" data-decide
                          data-subject="{{ $batch->label }} ({{ $members->count() }} members, {{ peso($total) }})" data-approve-form="approveFormBatch{{ $batch->id }}" data-approve-label="Approve batch" data-batch="1">
                        @csrf
                        @method('PATCH')
                    </form>
                </section>
            @endforeach
        @endif
    </div>
</div>

<x-info-banner variant="info" title="Loan Approval Workflow" class="mt-6">
    Loan requests are validated by the Manager before being forwarded here for the Administrator's final authorization. Approving or denying a request permanently logs the decision; approved requests are then finalized into an active loan by the Manager. Batch Loans only appear here once their batch has filled all 10 member slots &mdash; partially-filled batches stay on the Manager's Loan Request page until then.
</x-info-banner>

<style>
    .la-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; margin-bottom: 1.25rem; }
    .la-sub { color: var(--palette-muted); }
    .la-counts { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .la-count { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; background: var(--palette-surface); border: 1px solid var(--palette-stone); border-radius: 999px; font-size: 0.82rem; color: var(--palette-muted); }
    .la-count b { font-size: 1rem; color: var(--palette-ink); font-variant-numeric: tabular-nums; }
    .la-dot { width: 0.55rem; height: 0.55rem; border-radius: 999px; display: inline-block; }
    .la-dot.wait { background: var(--palette-wheat); }
    .la-dot.ok { background: var(--palette-field); }
    .la-dot.no { background: var(--palette-clay); }

    .la-bar { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .la-tabs { display: inline-flex; padding: 4px; gap: 2px; background: var(--palette-surface); border: 1px solid var(--palette-stone); border-radius: 0.8rem; }
    .la-tab { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.6rem; font-weight: 600; color: var(--palette-text-secondary); text-decoration: none; }
    .la-tab:hover { color: var(--palette-ink); background: var(--palette-surface-muted); }
    .la-tab .la-n { font-size: 0.75rem; padding: 0.05rem 0.45rem; border-radius: 999px; background: var(--palette-sprout); color: var(--palette-field); }
    .la-tab[aria-selected="true"] { background: var(--palette-field); color: #fff; }
    .la-tab[aria-selected="true"] .la-n { background: rgba(255, 255, 255, 0.2); color: #fff; }

    .la-split { display: grid; grid-template-columns: minmax(0, 360px) minmax(0, 1fr); gap: 1.25rem; align-items: start; }
    @media (max-width: 991.98px) { .la-split { grid-template-columns: 1fr; } }

    .la-queue, .la-detail { background: var(--palette-surface); border: 1px solid var(--palette-stone); border-radius: 1rem; overflow: hidden; }
    .la-queue-head { display: flex; justify-content: space-between; gap: 0.5rem; padding: 0.8rem 1rem; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--palette-muted); background: var(--palette-surface-muted); border-bottom: 1px solid var(--palette-stone); }
    .la-item { position: relative; display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 0.75rem; align-items: center; width: 100%; padding: 0.85rem 1rem; text-align: left; background: transparent; border: 0; border-bottom: 1px solid var(--palette-stone); color: inherit; cursor: pointer; }
    .la-item:last-of-type { border-bottom: 0; }
    .la-item:hover { background: var(--palette-surface-muted); }
    .la-item[aria-current="true"] { background: var(--palette-sprout); }
    .la-item[aria-current="true"]::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: var(--palette-field); }
    .la-item-main { min-width: 0; display: grid; }
    .la-item-name { font-weight: 600; color: var(--palette-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .la-item-meta { font-size: 0.8rem; color: var(--palette-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .la-item-amt { text-align: right; font-weight: 700; color: var(--palette-ink); font-variant-numeric: tabular-nums; white-space: nowrap; }
    .la-age { display: block; font-size: 0.72rem; font-weight: 600; color: var(--palette-muted); }
    .la-age.old { color: var(--palette-clay); }
    .la-avatar { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border-radius: 999px; background: var(--palette-sprout); color: var(--palette-field); font-weight: 700; font-size: 0.8rem; flex-shrink: 0; }
    .la-avatar-lg { width: 3rem; height: 3rem; font-size: 0.95rem; }
    .la-empty { padding: 1.5rem 1rem; margin: 0; color: var(--palette-muted); }
    .la-queue-pages { border-top: 1px solid var(--palette-stone); }

    .la-head { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--palette-stone); }
    .la-who { display: flex; gap: 0.9rem; align-items: center; min-width: 0; }
    .la-who h2 { font-family: var(--font-heading); font-weight: 600; font-size: 1.35rem; margin: 0; color: var(--palette-ink); }
    .la-who p { margin: 0; color: var(--palette-muted); font-size: 0.85rem; }
    .la-amount { text-align: right; }
    .la-amount small { display: block; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--palette-muted); }
    .la-amount strong { font-family: var(--font-heading); font-size: 1.9rem; font-weight: 600; color: var(--palette-ink); font-variant-numeric: tabular-nums; }
    .la-progress { height: 0.45rem; width: 12rem; max-width: 100%; border-radius: 999px; background: var(--palette-stone); overflow: hidden; margin-top: 0.4rem; }
    .la-progress div { height: 100%; background: var(--palette-field); }

    .la-body { display: grid; gap: 1.4rem; padding: 1.25rem 1.5rem; }
    .la-sec { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--palette-muted); margin-bottom: 0.6rem; }
    .la-facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.9rem 1.25rem; }
    .la-facts small { display: block; color: var(--palette-muted); font-size: 0.78rem; }
    .la-facts b { font-weight: 600; color: var(--palette-ink); }

    .la-checks { display: grid; gap: 0.5rem; }
    .la-check { display: flex; gap: 0.6rem; align-items: center; padding: 0.6rem 0.8rem; border-radius: 0.7rem; background: var(--palette-surface-muted); border: 1px solid var(--palette-stone); color: var(--palette-ink); }
    .la-check i { display: grid; place-items: center; width: 1.4rem; height: 1.4rem; border-radius: 999px; font-style: normal; font-size: 0.8rem; font-weight: 700; flex-shrink: 0; }
    .la-check.ok i { background: var(--palette-sprout); color: var(--palette-field); }
    .la-check.warn i { background: var(--palette-wheat-tint); color: var(--palette-wheat-deep); }

    .la-members { overflow-x: auto; border: 1px solid var(--palette-stone); border-radius: 0.8rem; }
    .la-members table { width: 100%; min-width: 640px; border-collapse: collapse; font-size: 0.88rem; }
    .la-members th { padding: 0.6rem 0.8rem; text-align: left; font-size: 0.7rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--palette-muted); background: var(--palette-surface-muted); border-bottom: 1px solid var(--palette-stone); }
    .la-members td { padding: 0.6rem 0.8rem; border-bottom: 1px solid var(--palette-stone); font-variant-numeric: tabular-nums; vertical-align: middle; }
    .la-members tbody tr:last-child td { border-bottom: 0; }
    .la-members tfoot td { font-weight: 700; background: var(--palette-surface-muted); border-top: 1px solid var(--palette-stone); border-bottom: 0; }

    .la-decide { display: grid; gap: 0.8rem; padding: 1rem 1.5rem; border-top: 1px solid var(--palette-stone); background: var(--palette-surface-muted); }
    .la-decide-row { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center; justify-content: flex-end; }
    .la-decide-row p { margin: 0 auto 0 0; color: var(--palette-muted); font-size: 0.85rem; }
    .la-decide textarea { min-height: 4.5rem; }

</style>

<script>
(function () {
    // ---- Queue selection: show the chosen request's panel ----
    var items = document.querySelectorAll('.la-item');
    items.forEach(function (item) {
        item.addEventListener('click', function () {
            items.forEach(function (other) { other.setAttribute('aria-current', String(other === item)); });
            document.querySelectorAll('#laDetail > .la-detail[id]').forEach(function (panel) {
                panel.hidden = panel.id !== item.dataset.panel;
            });
            if (window.matchMedia('(max-width: 991.98px)').matches) {
                document.getElementById(item.dataset.panel).scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ---- Approve / Deny, confirmed in place ----
    document.querySelectorAll('form[data-decide]').forEach(function (form) {
        var subject = form.dataset.subject;
        var isBatch = form.dataset.batch === '1';
        var csrf = form.innerHTML;

        function idle() {
            form.innerHTML = csrf +
                '<div class="la-decide-row"><p>Your decision is final and logged.</p>' +
                '<button type="button" class="btn btn-outline-danger" data-act="deny"><i class="fas fa-times me-1"></i> Deny</button>' +
                '<button type="button" class="btn btn-success" data-act="approve"><i class="fas fa-check me-1"></i> ' + form.dataset.approveLabel + '</button></div>';
            form.querySelector('[data-act="deny"]').addEventListener('click', denyStep);
            form.querySelector('[data-act="approve"]').addEventListener('click', approveStep);
        }

        function approveStep() {
            form.innerHTML = csrf + '<div class="la-decide-row"><p></p>' +
                '<button type="button" class="btn btn-outline-secondary" data-act="cancel">Cancel</button>' +
                '<button type="button" class="btn btn-success" data-act="go"><i class="fas fa-check me-1"></i> Yes, approve</button></div>';
            form.querySelector('.la-decide-row p').textContent = 'Approve ' + subject + '?';
            form.querySelector('[data-act="cancel"]').addEventListener('click', idle);
            form.querySelector('[data-act="go"]').addEventListener('click', function () {
                this.disabled = true;
                document.getElementById(form.dataset.approveForm).submit();
            });
        }

        function denyStep() {
            form.innerHTML = csrf +
                '<div><label class="form-label fw-semibold small" for="' + form.id + 'Reason">Reason for denial' + (isBatch ? ' (applied to all members)' : '') + '</label>' +
                '<textarea class="form-control" name="denial_reason" id="' + form.id + 'Reason" rows="2" maxlength="1000" placeholder="Explain why this request does not meet the cooperative\'s lending criteria..."></textarea>' +
                '<div class="invalid-feedback">Please provide a reason for the denial.</div></div>' +
                '<div class="la-decide-row"><p></p>' +
                '<button type="button" class="btn btn-outline-secondary" data-act="cancel">Cancel</button>' +
                '<button type="submit" class="btn btn-danger" data-act="go"><i class="fas fa-times me-1"></i> Deny ' + (isBatch ? 'batch' : 'request') + '</button></div>';
            form.querySelector('.la-decide-row p').textContent = 'Deny ' + subject + '?';
            form.querySelector('[data-act="cancel"]').addEventListener('click', idle);
            form.querySelector('textarea').focus();
        }

        form.addEventListener('submit', function (e) {
            var reason = form.elements['denial_reason'];
            if (!reason || !reason.value.trim()) {
                e.preventDefault();
                if (reason) { reason.classList.add('is-invalid'); reason.focus(); }
                return;
            }
            form.querySelector('[data-act="go"]').disabled = true;
        });

        idle();
    });
})();
</script>
@endsection
