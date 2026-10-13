{{--
    Search box that filters a list as you type. Place it inside a GET <form>:

        <form method="GET" action="{{ route('admin.cbu') }}">
            <x-live-search placeholder="Search members..." />
        </form>

    Results update shortly after typing stops, Enter searches immediately,
    and the ✕ button clears it. Other fields in the same form (dropdowns,
    hidden values) are submitted with it, and pagination resets to page 1.
--}}
@props(['name' => 'search', 'placeholder' => 'Search...', 'width' => '260px'])

<div class="position-relative" data-live-search>
    <input type="text" name="{{ $name }}" value="{{ request($name) }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
           {{ $attributes->merge(['class' => 'form-control ps-5 pe-5 py-2']) }} style="min-width: {{ $width }};" autocomplete="off">
    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" data-live-search-icon style="font-size: 14px;"></i>
    <button type="button" data-live-search-clear class="btn btn-sm position-absolute end-0 top-50 translate-middle-y text-muted p-0 pe-3 border-0 bg-transparent {{ request($name) ? '' : 'd-none' }}" title="Clear search" aria-label="Clear search">
        <i class="fas fa-times-circle"></i>
    </button>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-live-search]').forEach(function (box) {
            var input = box.querySelector('input');
            var icon = box.querySelector('[data-live-search-icon]');
            var clearBtn = box.querySelector('[data-live-search-clear]');
            var form = input.form;
            if (!form) return;
            var timer;
            var submit = function () { form.requestSubmit ? form.requestSubmit() : form.submit(); };

            form.addEventListener('submit', function () {
                clearTimeout(timer);
                icon.className = 'fas fa-spinner fa-spin position-absolute start-3 top-50 translate-middle-y text-muted';
                input.readOnly = true;
            });

            input.addEventListener('input', function () {
                clearBtn.classList.toggle('d-none', !input.value);
                clearTimeout(timer);
                timer = setTimeout(submit, 500);
            });

            clearBtn.addEventListener('click', function () {
                input.value = '';
                clearBtn.classList.add('d-none');
                submit();
            });

            // After a search reloads the page, keep typing where you left off.
            if (input.value) {
                input.focus();
                input.setSelectionRange(input.value.length, input.value.length);
            }
        });
    });
</script>
@endonce
