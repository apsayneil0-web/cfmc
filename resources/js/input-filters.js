// Typing filters for form fields: tag an input with data-input="<kind>" and
// characters that don't belong are dropped as the user types or pastes.
//
// This is a convenience layer only — the controllers validate the same rules
// server-side (see App\Rules\PersonName and the contact_number regex), so a
// bypassed filter still can't save bad data.
//
// Kinds:
//   name     letters (incl. ñ/accents), spaces, period, hyphen, apostrophe
//            — "Ma. Cristina", "Dela Cruz-Peña", "D'Souza"
//   initial  letters and period — "M", "M."
//   place    letters, digits, spaces, period, comma, hyphen, apostrophe — "Purok 5, Zone 2"
//   digits   whole numbers only
//   decimal  digits and one decimal point — hectares, amounts
//   phone    digits, with an optional leading + — 09123456789 / +639123456789
//
// Works on fields added later too (modals, cloned rows), since it listens on
// the document rather than binding each input.
const FILTERS = {
    name: (v) => v.replace(/[^\p{L}\s.'\-]/gu, '').replace(/^[\s.'\-]+/, ''),
    initial: (v) => v.replace(/[^\p{L}.]/gu, ''),
    place: (v) => v.replace(/[^\p{L}\d\s.,'\-]/gu, ''),
    digits: (v) => v.replace(/\D/g, ''),
    decimal: (v) => {
        const cleaned = v.replace(/[^\d.]/g, '');
        const dot = cleaned.indexOf('.');
        return dot === -1 ? cleaned : cleaned.slice(0, dot + 1) + cleaned.slice(dot + 1).replace(/\./g, '');
    },
    phone: (v) => {
        const cleaned = v.replace(/[^\d+]/g, '');
        return cleaned.charAt(0) + cleaned.slice(1).replace(/\+/g, '');
    },
};

// Keys a number field would otherwise accept that aren't valid here
// (exponent and sign), so type="number" fields can use data-input too.
const BLOCKED_NUMBER_KEYS = ['e', 'E', '+', '-'];

document.addEventListener('input', (event) => {
    const input = event.target;
    const filter = FILTERS[input.dataset?.input];
    if (!filter || input.type === 'number') return;

    const before = input.value;
    const after = filter(before);
    if (after === before) return;

    // Keep the caret where the user was typing instead of jumping to the end.
    const caret = Math.max(0, (input.selectionStart ?? after.length) - (before.length - after.length));
    input.value = after;
    try {
        input.setSelectionRange(caret, caret);
    } catch (e) {}
});

document.addEventListener('keydown', (event) => {
    const input = event.target;
    if (input.type !== 'number' || !FILTERS[input.dataset?.input]) return;

    if (BLOCKED_NUMBER_KEYS.includes(event.key) || (input.dataset.input === 'digits' && event.key === '.')) {
        event.preventDefault();
    }
});

// A scrolled mouse wheel silently changes a focused number field's value.
document.addEventListener('wheel', (event) => {
    const input = event.target;
    if (input.type === 'number' && input.dataset?.input && document.activeElement === input) {
        input.blur();
    }
}, { passive: true });
