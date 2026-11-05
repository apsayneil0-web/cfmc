{{--
    Document cards for one farmer on the Loan Approval review panel. Clicking
    a card opens the page's document viewer at that file.

    @include('admin.partials.loan-approval-documents', ['docs' => $docs, 'personKey' => 'r12', 'personName' => 'Juan Dela Cruz'])
--}}
<div>
    <p class="la-sec" data-docs-heading="{{ $personKey }}">Documents</p>
    <div class="la-docs">
        @foreach($docs as $doc)
            @if($doc['kind'] === 'missing')
                <div class="la-doc is-missing">
                    <div class="la-thumb"><i class="fas fa-file-circle-xmark" aria-hidden="true"></i></div>
                    <span class="la-doc-name">{{ $doc['label'] }}</span>
                    <span class="la-doc-state">Not uploaded</span>
                </div>
            @else
                <button type="button" class="la-doc" data-person="{{ $personKey }}" data-url="{{ $doc['url'] }}" aria-label="View {{ $doc['label'] }}">
                    <span class="la-tick" aria-hidden="true">✓</span>
                    <div class="la-thumb">
                        @if($doc['kind'] === 'image')
                            <img src="{{ $doc['url'] }}" alt="" loading="lazy">
                        @elseif($doc['kind'] === 'pdf')
                            <i class="fas fa-file-pdf" aria-hidden="true"></i>
                        @else
                            <i class="fas fa-file" aria-hidden="true"></i>
                        @endif
                    </div>
                    <span class="la-doc-name">{{ $doc['label'] }}</span>
                    <span class="la-doc-state">Not checked yet</span>
                </button>
            @endif
        @endforeach
    </div>
    <script type="application/json" data-docs-for="{{ $personKey }}" data-name="{{ $personName }}">@json($docs)</script>
</div>
