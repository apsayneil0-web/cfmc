import './bootstrap';

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
