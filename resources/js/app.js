import './bootstrap';
import './date-pickers';

// Popups are locked against outside clicks system-wide: clicking the dimmed
// background does nothing, so a stray click never closes a form or loses what
// was typed. They close with their ✕ / Close / Cancel buttons, or Esc.
// A popup can still opt back in with data-bs-backdrop="true".
(function () {
    function lockPopups() {
        var Modal = window.bootstrap && window.bootstrap.Modal;
        if (!Modal) return;
        Modal.Default.backdrop = 'static';
        // Popups some pages create before this runs keep the old default, so
        // also apply it to each popup as it opens.
        document.addEventListener('show.bs.modal', function (event) {
            var instance = Modal.getInstance(event.target);
            if (instance && instance._config && !event.target.hasAttribute('data-bs-backdrop')) {
                instance._config.backdrop = 'static';
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', lockPopups);
    } else {
        lockPopups();
    }
})();

// Row action dropdowns (the kebab-menu) render with position:absolute by
// default, which gets clipped by any ancestor with overflow set — every
// table wrapper uses overflow-x:auto for mobile scrolling — whenever the
// menu opens near that ancestor's edge. Popper's 'fixed' strategy escapes
// that clipping app-wide instead of patching each table individually.
document.addEventListener('DOMContentLoaded', function () {
    if (window.bootstrap && window.bootstrap.Dropdown) {
        window.bootstrap.Dropdown.Default.popperConfig = { strategy: 'fixed' };
    }
});

(function () {
    var STORAGE_KEY = 'cfmc-theme';

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
    }

    function currentTheme() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    }

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('themeToggle');
        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            var next = currentTheme() === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            try {
                localStorage.setItem(STORAGE_KEY, next);
            } catch (e) {}
        });
    });
})();

// Inactivity timeout: this is a convenience layer only, for an immediate
// redirect while a tab sits open and idle. It is NOT the security boundary
// — the EnsureSessionIsActive middleware enforces the same timeout
// server-side on every request regardless of whether this script runs, so
// disabling JavaScript cannot bypass the logout, only delay the user
// noticing it until their next navigation.
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var timeoutMeta = document.querySelector('meta[name="session-timeout-minutes"]');
        var logoutForm = document.querySelector('#logoutModal form');

        if (!timeoutMeta || !logoutForm) {
            return;
        }

        var TIMEOUT_MS = (parseInt(timeoutMeta.content, 10) || 15) * 60 * 1000;
        var HEARTBEAT_INTERVAL_MS = Math.min(60000, TIMEOUT_MS / 3);
        var CHECK_INTERVAL_MS = 5000;
        var ACTIVITY_EVENTS = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'wheel', 'click'];

        var lastActivityAt = Date.now();
        var lastHeartbeatAt = Date.now();
        var loggingOut = false;

        function recordActivity() {
            lastActivityAt = Date.now();
        }

        ACTIVITY_EVENTS.forEach(function (eventName) {
            document.addEventListener(eventName, recordActivity, { passive: true });
        });

        function sendHeartbeat() {
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                return;
            }

            fetch(logoutForm.action.replace(/\/logout$/, '/session/heartbeat'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfMeta.content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }).catch(function () {
                // A failed heartbeat just means the server-side clock keeps
                // ticking normally; the middleware is the real enforcement.
            });

            lastHeartbeatAt = Date.now();
        }

        function triggerTimeoutLogout() {
            if (loggingOut) {
                return;
            }
            loggingOut = true;

            var reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = 'timeout';
            logoutForm.appendChild(reasonInput);
            logoutForm.submit();
        }

        setInterval(function () {
            var idleFor = Date.now() - lastActivityAt;

            if (idleFor >= TIMEOUT_MS) {
                triggerTimeoutLogout();
                return;
            }

            if (Date.now() - lastHeartbeatAt >= HEARTBEAT_INTERVAL_MS) {
                sendHeartbeat();
            }
        }, CHECK_INTERVAL_MS);
    });
})();
