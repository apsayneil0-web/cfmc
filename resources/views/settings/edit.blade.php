@extends($user->layoutView())

@section('title', 'Settings')
@section('header', 'Settings')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 p-md-6" style="max-width: 32rem;">
    <h6 class="fw-bold mb-1">Appearance</h6>
    <p class="text-muted small mb-3">Choose how CFMC looks on this device. Saved in this browser only.</p>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary flex-fill" id="settingsThemeLight">
            <i class="fas fa-sun me-2"></i> Light
        </button>
        <button type="button" class="btn btn-outline-secondary flex-fill" id="settingsThemeDark">
            <i class="fas fa-moon me-2"></i> Dark
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Reuses the topbar's own theme toggle rather than duplicating its
    // localStorage/data-bs-theme logic — this page just drives that same
    // button so the two controls can never drift out of sync.
    var topbarToggle = document.getElementById('themeToggle');
    var lightBtn = document.getElementById('settingsThemeLight');
    var darkBtn = document.getElementById('settingsThemeDark');

    function currentTheme() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    }

    function syncButtons() {
        var theme = currentTheme();
        lightBtn.classList.toggle('btn-primary', theme === 'light');
        lightBtn.classList.toggle('btn-outline-secondary', theme !== 'light');
        darkBtn.classList.toggle('btn-primary', theme === 'dark');
        darkBtn.classList.toggle('btn-outline-secondary', theme !== 'dark');
    }

    lightBtn.addEventListener('click', function () {
        if (currentTheme() !== 'light' && topbarToggle) topbarToggle.click();
        syncButtons();
    });

    darkBtn.addEventListener('click', function () {
        if (currentTheme() !== 'dark' && topbarToggle) topbarToggle.click();
        syncButtons();
    });

    syncButtons();
});
</script>
@endsection
