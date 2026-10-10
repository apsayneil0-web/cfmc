@props(['paginator'])

@if($paginator->hasPages())
<div class="px-4 px-md-6 py-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
    <span class="text-muted small">Showing {{ $paginator->count() }} of {{ $paginator->total() }} entries</span>
    <nav aria-label="Table pagination">
        <ul class="pagination pagination-sm mb-0">
            @if($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">Previous</span></li>
            @else
            <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}">Previous</a></li>
            @endif

            @for($i = 1; $i <= $paginator->lastPage(); $i++)
            <li class="page-item {{ $i == $paginator->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
            </li>
            @endfor

            @if($paginator->hasMorePages())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}">Next</a></li>
            @else
            <li class="page-item disabled"><span class="page-link">Next</span></li>
            @endif
        </ul>
    </nav>
</div>
@else
<div class="px-4 px-md-6 py-4 border-top">
    <span class="text-muted small">Showing {{ $paginator->count() }} of {{ $paginator->total() }} entries</span>
</div>
@endif
