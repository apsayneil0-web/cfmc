{{--
    Renders one "View" button per file for a farmer document field that may
    hold several uploads (see Farmer::documentPaths()). Buttons sharing a
    viewer group step through each other via x-file-viewer's arrows, so
    multiple files read as one browsable set instead of separate popups.

    <x-document-links :paths="$farmer->documentPaths('certificate_of_title_path')" label="Certificate of Title" :group="'farmer-docs-'.$farmer->id" />
--}}
@props(['paths', 'label', 'group', 'download' => false])

@if(empty($paths))
    <span class="text-muted small">Not uploaded</span>
@else
    <div class="d-flex flex-wrap gap-2">
        @foreach($paths as $index => $path)
            @php
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $number = count($paths) > 1 ? ' '.($index + 1) : '';
            @endphp
            @if(in_array($extension, ['jpg', 'jpeg', 'png']))
                <a href="{{ asset('storage/'.$path) }}" target="_blank" data-file-viewer data-viewer-title="{{ $label }}{{ $number }}" data-viewer-group="{{ $group }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-image me-1"></i> View Image{{ $number }}
                </a>
            @elseif($extension === 'pdf')
                <a href="{{ asset('storage/'.$path) }}" target="_blank" data-file-viewer data-viewer-title="{{ $label }}{{ $number }}" data-viewer-group="{{ $group }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i> View PDF{{ $number }}
                </a>
            @else
                <a href="{{ asset('storage/'.$path) }}" target="_blank" data-file-viewer data-viewer-title="{{ $label }}{{ $number }}" data-viewer-group="{{ $group }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file me-1"></i> View Document{{ $number }}
                </a>
            @endif
            @if($download)
                <a href="{{ asset('storage/'.$path) }}" download="{{ basename($path) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-download me-1"></i> Download{{ $number }}
                </a>
            @endif
        @endforeach
    </div>
@endif
