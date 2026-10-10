// Flatpickr for every date and time field in the dashboards.
//
// Turns each <input type="date"> / <input type="time"> into a consistent
// calendar or time picker without changing what the form submits: dates are
// still sent as YYYY-MM-DD and times as HH:MM, so no controller changes.
//
// - Shows friendlier values ("Oct 10, 2026", "2:30 PM") in a visible copy of
//   the field; the original input keeps the submitted value.
// - Respects the field's min / max, required, disabled and readonly.
// - Fires the usual change/input events, so inline onchange handlers and
//   addEventListener('change') code keep working.
// - Stays in sync when page scripts set .value directly (e.g. prefilled edit
//   forms, the schedule's calculated end time).
// - Phones keep their own native date/time pickers (Flatpickr's default).
// - Date ranges: put data-range-end="<id of the 'to' input>" on the 'from'
//   input and both are edited from one range calendar.
// - Opt out per field with data-native-picker.
import flatpickr from 'flatpickr';

const DATE_DISPLAY = 'M j, Y';
const TIME_DISPLAY = 'h:i K';
const ISO_DATE = /^\d{4}-\d{2}-\d{2}$/;

// Accept both the display format and plain YYYY-MM-DD when typing.
function parseDate(str, format) {
    const value = String(str).trim();
    if (ISO_DATE.test(value)) {
        return flatpickr.parseDate(value, 'Y-m-d');
    }
    return flatpickr.parseDate(value, format) || flatpickr.parseDate(value, DATE_DISPLAY);
}

// The visible copy of the field should look exactly like the original did.
function mirrorLook(instance) {
    const original = instance.input;
    const visible = instance.altInput || instance.mobileInput;
    if (!visible || visible === original) return;
    visible.className = original.className.replace(/\bflatpickr-input\b/, '').trim();
    visible.style.cssText = original.style.cssText;
    if (original.title) visible.title = original.title;
    if (original.getAttribute('aria-label')) visible.setAttribute('aria-label', original.getAttribute('aria-label'));
    if (original.id && document.querySelector(`label[for="${original.id}"]`)) {
        // Clicking the field's label should still focus what the user sees.
        document.querySelector(`label[for="${original.id}"]`).addEventListener('click', (e) => {
            e.preventDefault();
            visible.focus();
        });
    }
    if (original.disabled) visible.disabled = true;
    if (original.readOnly) visible.readOnly = true;
}

// When page code does `input.value = '...'`, push it into the picker too.
function syncProgrammaticValue(instance, format) {
    const input = instance.input;
    const native = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
    Object.defineProperty(input, 'value', {
        configurable: true,
        get() {
            return native.get.call(this);
        },
        set(v) {
            native.set.call(this, v);
            const current = instance.selectedDates.map((d) => instance.formatDate(d, format)).join(instance.l10n.rangeSeparator);
            if ((v || '') !== current) {
                if (v) {
                    instance.setDate(v, false, format);
                } else {
                    instance.clear(false);
                }
            }
        },
    });
}

// Bootstrap modals trap focus inside the dialog, but Flatpickr's calendar is
// attached to <body>; pause the trap while a picker inside a modal is open so
// its year and time boxes can be typed into, and let Esc close only the picker.
let openInModal = null;
function pauseModalFocusTrap(instance, paused) {
    const modalEl = instance.input.closest('.modal');
    const modal = modalEl && window.bootstrap && window.bootstrap.Modal.getInstance(modalEl);
    const trap = modal && modal._focustrap;
    if (!trap) return;
    if (paused) {
        trap.deactivate();
        openInModal = instance;
    } else {
        trap.activate();
        if (openInModal === instance) openInModal = null;
    }
}
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && openInModal && openInModal.isOpen) {
        e.preventDefault();
        e.stopImmediatePropagation();
        openInModal.close();
    }
}, true);

const shared = {
    allowInput: true,
    parseDate,
    onReady: [(_, __, instance) => mirrorLook(instance)],
    onOpen: [(_, __, instance) => pauseModalFocusTrap(instance, true)],
    onClose: [(_, __, instance) => pauseModalFocusTrap(instance, false)],
};

function dateOptions(input) {
    return {
        ...shared,
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: DATE_DISPLAY,
        minDate: input.min || null,
        maxDate: input.max || null,
        disableMobile: false,
    };
}

function timeOptions(input) {
    const step = parseInt(input.getAttribute('step'), 10);
    return {
        ...shared,
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        altInput: true,
        altFormat: TIME_DISPLAY,
        time_24hr: false,
        minuteIncrement: step >= 60 ? Math.round(step / 60) : 15,
        minTime: input.min || null,
        maxTime: input.max || null,
        disableMobile: false,
    };
}

function initRange(fromInput) {
    const toInput = document.getElementById(fromInput.dataset.rangeEnd);
    if (!toInput) return false;

    const initial = [fromInput.value, toInput.value].filter(Boolean);
    toInput.dataset.pickerReady = '1';
    toInput.hidden = true;

    const instance = flatpickr(fromInput, {
        ...shared,
        mode: 'range',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: DATE_DISPLAY,
        defaultDate: initial,
        minDate: fromInput.min || null,
        maxDate: toInput.max || fromInput.max || null,
        // Write both ends once the range is complete (or cleared), then fire a
        // single change on the 'to' input so filters reload only once.
        onChange: [(dates, _, inst) => {
            if (dates.length === 1) return;
            const fmt = (d) => inst.formatDate(d, 'Y-m-d');
            const from = dates[0] ? fmt(dates[0]) : '';
            const to = dates[1] ? fmt(dates[1]) : '';
            Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set.call(inst.input, from);
            toInput.value = to;
            toInput.dispatchEvent(new Event('change', { bubbles: true }));
        }],
    });
    // The 'from' input itself would also fire change for each click; stop
    // that so code listening on both inputs only reacts to the finished range.
    fromInput.addEventListener('change', (e) => e.stopImmediatePropagation(), true);
    if (instance.altInput) {
        instance.altInput.placeholder = fromInput.placeholder || 'Select date range';
        instance.altInput.style.minWidth = '15rem';
        // Make clearing the text box clear the range.
        instance.altInput.addEventListener('change', () => {
            if (!instance.altInput.value) instance.clear();
        });
    }
    return true;
}

function initPickers(root = document) {
    root.querySelectorAll('input[type="date"], input[type="time"]').forEach((input) => {
        if (input.dataset.pickerReady || input.hasAttribute('data-native-picker') || input._flatpickr) return;
        input.dataset.pickerReady = '1';

        if (input.type === 'date' && input.dataset.rangeEnd && initRange(input)) return;

        const isTime = input.type === 'time';
        const options = isTime ? timeOptions(input) : dateOptions(input);
        const instance = flatpickr(input, options);
        if (instance && instance.altInput) {
            if (!instance.altInput.placeholder) instance.altInput.placeholder = isTime ? 'Select time' : 'Select date';
        }
        if (instance) syncProgrammaticValue(instance, options.dateFormat);
    });
}

function start() {
    initPickers();
    // Forms that are added or revealed later (modals, cloned rows).
    document.addEventListener('shown.bs.modal', (e) => initPickers(e.target));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
