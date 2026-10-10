{{--
    Styled replacement for the browser's confirm() box.

    Include once per layout: <x-confirm-dialog />

    From script (returns a Promise that resolves to true / false):
        if (!(await confirmDialog('Archive this user?', { variant: 'danger', confirmLabel: 'Archive' }))) return;

    On a submit button (the clicked button's name/value is still sent):
        <button type="submit" name="action" value="submit"
                data-confirm="Submit this complaint?" data-confirm-label="Submit">Submit</button>

    Options: title, confirmLabel, cancelLabel, variant ('primary' | 'danger'), icon (Font Awesome class).
    It is a light overlay rather than a Bootstrap modal, so it can open on top of
    an already-open modal without Bootstrap's stacking and focus-trap problems.
--}}
@once
<div class="cd" id="confirmDialog" hidden role="alertdialog" aria-modal="true" aria-labelledby="cdTitle" aria-describedby="cdMessage">
    <div class="cd-backdrop" data-cd-cancel></div>
    <div class="cd-panel">
        <span class="cd-icon" id="cdIcon"><i class="fas fa-circle-question"></i></span>
        <h2 class="cd-title" id="cdTitle">Are you sure?</h2>
        <p class="cd-message" id="cdMessage"></p>
        <div class="cd-actions">
            <button type="button" class="btn btn-outline-secondary" data-cd-cancel id="cdCancel">Cancel</button>
            <button type="button" class="btn btn-primary" id="cdConfirm">Confirm</button>
        </div>
    </div>
</div>

<style>
    .cd { position: fixed; inset: 0; z-index: 2100; display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .cd[hidden] { display: none; }
    .cd-backdrop {
        position: absolute; inset: 0; background: var(--backdrop-tint, rgba(31, 42, 34, 0.55));
        -webkit-backdrop-filter: blur(var(--backdrop-blur, 6px)); backdrop-filter: blur(var(--backdrop-blur, 6px));
        animation: cd-fade 200ms ease-out;
    }
    .cd-panel {
        position: relative; width: min(24rem, 100%); padding: 1.75rem 1.5rem 1.5rem; text-align: center;
        background: var(--brand-surface, #fff); color: var(--text-primary, #1f2a22);
        border: 1px solid var(--brand-border, #e4e2da); border-radius: var(--radius-lg, 1rem);
        box-shadow: var(--shadow-modal, 0 32px 64px -24px rgba(0, 0, 0, 0.45));
        animation: cd-in 300ms cubic-bezier(0.16, 1, 0.3, 1);
    }
    .cd-icon {
        display: inline-grid; place-items: center; width: 3rem; height: 3rem; border-radius: 999px; margin-bottom: 0.9rem; font-size: 1.2rem;
        background: var(--brand-primary-light, #e6eee7); color: var(--brand-primary, #2f5d3a);
    }
    .cd.is-danger .cd-icon { background: var(--brand-danger-light, #f6e5e0); color: var(--brand-danger-text, #9c4532); }
    .cd-title { font-size: 1.1rem; font-weight: 700; margin: 0 0 0.4rem; }
    .cd-message { margin: 0 0 1.4rem; color: var(--text-secondary, #4a5249); font-size: 0.95rem; }
    .cd-actions { display: flex; gap: 0.6rem; }
    .cd-actions .btn { flex: 1; }
    @keyframes cd-fade { from { opacity: 0; } }
    @keyframes cd-in { from { opacity: 0; transform: translateY(12px) scale(0.97); } }
    @media (prefers-reduced-motion: reduce) { .cd-backdrop, .cd-panel { animation: none; } }
</style>

<script>
    (function () {
        var box = document.getElementById('confirmDialog');
        if (!box) return;
        var homeParent = box.parentNode;
        var titleEl = document.getElementById('cdTitle');
        var msgEl = document.getElementById('cdMessage');
        var iconEl = document.getElementById('cdIcon').querySelector('i');
        var okBtn = document.getElementById('cdConfirm');
        var cancelBtn = document.getElementById('cdCancel');
        var resolver = null, lastFocus = null, lockedBody = false;

        function finish(result) {
            if (box.hidden) return;
            box.hidden = true;
            if (lockedBody) { document.body.style.overflow = ''; lockedBody = false; }
            if (box.parentNode !== homeParent) homeParent.appendChild(box);
            if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
            var r = resolver; resolver = null;
            if (r) r(result);
        }

        window.confirmDialog = function (message, options) {
            options = options || {};
            if (resolver) finish(false); // only one at a time
            var danger = options.variant === 'danger';
            box.classList.toggle('is-danger', danger);
            titleEl.textContent = options.title || (danger ? 'Please confirm' : 'Are you sure?');
            msgEl.textContent = message || '';
            iconEl.className = 'fas ' + (options.icon || (danger ? 'fa-triangle-exclamation' : 'fa-circle-question'));
            okBtn.className = 'btn ' + (danger ? 'btn-danger' : 'btn-primary');
            okBtn.textContent = options.confirmLabel || 'Confirm';
            cancelBtn.textContent = options.cancelLabel || 'Cancel';

            // Live inside an open Bootstrap modal so its focus trap doesn't fight ours.
            var openModal = document.querySelector('.modal.show');
            var host = openModal || homeParent;
            if (box.parentNode !== host) host.appendChild(box);
            if (!openModal && document.body.style.overflow !== 'hidden') { document.body.style.overflow = 'hidden'; lockedBody = true; }

            lastFocus = document.activeElement;
            box.hidden = false;
            okBtn.focus();
            return new Promise(function (resolve) { resolver = resolve; });
        };

        okBtn.addEventListener('click', function () { finish(true); });
        box.addEventListener('click', function (e) { if (e.target.closest('[data-cd-cancel]')) finish(false); });

        // Capture phase: Esc cancels only this dialog, not a modal underneath.
        window.addEventListener('keydown', function (e) {
            if (box.hidden) return;
            if (e.key === 'Escape') { e.preventDefault(); e.stopImmediatePropagation(); finish(false); }
            else if (e.key === 'Tab') {
                e.preventDefault();
                (document.activeElement === okBtn ? cancelBtn : okBtn).focus();
            }
        }, true);

        // Declarative use on submit buttons: <button type="submit" data-confirm="...">
        document.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('[data-confirm]');
            if (!btn || btn.dataset.confirmed === '1') return;
            var form = btn.form || btn.closest('form');
            if (form && !form.checkValidity()) return; // let the browser show field errors first
            e.preventDefault();
            confirmDialog(btn.getAttribute('data-confirm'), {
                title: btn.getAttribute('data-confirm-title') || undefined,
                confirmLabel: btn.getAttribute('data-confirm-label') || undefined,
                variant: btn.getAttribute('data-confirm-variant') || undefined
            }).then(function (ok) {
                if (!ok) return;
                if (form && btn.type === 'submit') {
                    // requestSubmit keeps the clicked button's name/value in the request.
                    if (form.requestSubmit) { form.requestSubmit(btn); }
                    else { btn.dataset.confirmed = '1'; btn.click(); delete btn.dataset.confirmed; }
                } else {
                    btn.dataset.confirmed = '1'; btn.click(); delete btn.dataset.confirmed;
                }
            });
        }, true);
    })();
</script>
@endonce
