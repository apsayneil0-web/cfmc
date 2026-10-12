// Unsaved-changes guard for popup forms.
//
// Every popup closes on an outside click or Esc. When the user has changed a
// field in a popup, those two accidental ways of closing ask "Discard
// changes?" first instead of throwing the typing away. The popup's own
// ✕ / Cancel buttons still close immediately (that's a deliberate choice),
// and saving works exactly as before.
//
// - Only real user edits count (typing, choosing, picking a date), so pages
//   that pre-fill or load fields into a popup never trigger the question.
// - Read-only popups and untouched forms close normally.
// - Locked popups (data-bs-backdrop="static") are left alone.
// - Opt a popup out with data-no-unsaved-guard.

const FIELDS = 'input, select, textarea';

function isGuarded(modal) {
    return modal instanceof HTMLElement
        && modal.classList.contains('modal')
        && modal.classList.contains('show')
        && modal.getAttribute('data-bs-backdrop') !== 'static'
        && !modal.hasAttribute('data-no-unsaved-guard');
}

function isDirty(modal) {
    return !!(modal._unsavedGuard && modal._unsavedGuard.edited);
}

function markEdited(target) {
    const modal = target && target.closest && target.closest('.modal');
    if (modal && modal._unsavedGuard) modal._unsavedGuard.edited = true;
}

// Track each popup from the moment it opens; forget it once closed.
document.addEventListener('shown.bs.modal', (e) => {
    e.target._unsavedGuard = { edited: false, asking: false };
});
document.addEventListener('hidden.bs.modal', (e) => {
    delete e.target._unsavedGuard;
});

// Real edits by the user — not page scripts filling fields in.
['input', 'change'].forEach((type) => {
    document.addEventListener(type, (e) => {
        if (e.isTrusted && e.target.matches && e.target.matches(FIELDS)) markEdited(e.target);
    }, true);
});
// Dates picked from the calendar (see date-pickers.js).
document.addEventListener('picker:userchange', (e) => markEdited(e.target), true);

function askToDiscard(modal) {
    const state = modal._unsavedGuard;
    if (!state || state.asking) return;
    state.asking = true;
    const ask = window.confirmDialog
        ? window.confirmDialog('You have unsaved changes in this form. If you close it now, they will be lost.', {
            title: 'Discard changes?',
            confirmLabel: 'Discard',
            cancelLabel: 'Keep editing',
            variant: 'danger',
        })
        : Promise.resolve(window.confirm('Discard your unsaved changes?'));
    ask.then((discard) => {
        state.asking = false;
        if (!discard) return;
        state.edited = false;
        const instance = window.bootstrap && window.bootstrap.Modal.getInstance(modal);
        if (instance) instance.hide();
    });
}

// Outside click: Bootstrap closes when the click lands on the popup's own
// backdrop area. Catch that click before Bootstrap sees it.
window.addEventListener('click', (e) => {
    const modal = e.target;
    if (!isGuarded(modal) || !isDirty(modal)) return;
    e.preventDefault();
    e.stopPropagation();
    askToDiscard(modal);
}, true);

// Esc: only the topmost open popup reacts, matching Bootstrap. (The date
// picker, photo viewer and confirmation dialog handle their own Esc first.)
window.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape' || e.defaultPrevented) return;
    const open = Array.from(document.querySelectorAll('.modal.show'));
    const modal = open[open.length - 1];
    if (!isGuarded(modal) || !isDirty(modal)) return;
    e.preventDefault();
    e.stopPropagation();
    askToDiscard(modal);
}, true);
