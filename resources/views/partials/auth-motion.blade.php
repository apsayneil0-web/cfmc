{{--
    Entrance motion for the standalone auth pages (login, forgot password).
    Include inside <head>. Calm and smooth, nothing bouncy. Every hidden
    state is scoped to html.auth-motion, which is only added when the
    visitor hasn't asked for reduced motion, so the form is never hidden
    without JS.
--}}
<script>
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('auth-motion');
    }
</script>
<style>
    :root { --am-out: cubic-bezier(0.16, 1, 0.3, 1); --am-soft: cubic-bezier(0.33, 1, 0.68, 1); }

    /* The pages' own quick entrance animations are replaced by this sequence */
    html.auth-motion .card,
    html.auth-motion .alert { animation: none; }

    /* Card rises in */
    html.auth-motion .card { opacity: 0; transform: translateY(26px) scale(0.985); transition: opacity 900ms var(--am-soft), transform 1100ms var(--am-out); }
    html.auth-motion.am-go .card { opacity: 1; transform: none; }

    /* Logo tile settles in, then its icon draws itself */
    html.auth-motion .brand-mark { opacity: 0; transform: scale(0.6) rotate(-8deg); transition: opacity 700ms var(--am-soft) 280ms, transform 1100ms var(--am-out) 280ms; }
    html.auth-motion.am-go .brand-mark { opacity: 1; transform: none; }
    html.auth-motion .brand-mark svg [pathLength] { stroke-dasharray: 1; stroke-dashoffset: 1; transition: stroke-dashoffset 1400ms var(--am-out) 520ms; }
    html.auth-motion.am-go .brand-mark svg [pathLength] { stroke-dashoffset: 0; }

    /* Title rises word by word out of a mask */
    .am-w { display: inline-block; overflow: hidden; vertical-align: top; padding: 0 0.05em 0.12em 0; margin: 0 -0.05em -0.12em 0; }
    .am-wi { display: inline-block; }
    html.auth-motion .am-wi { transform: translateY(110%); transition: transform 1000ms var(--am-out); transition-delay: calc(420ms + var(--i, 0) * 70ms); }
    html.auth-motion.am-go .am-wi { transform: none; }

    /* Everything else fades up in order (delays set by the script) */
    html.auth-motion .am-item { opacity: 0; transform: translateY(16px); transition: opacity 800ms var(--am-soft), transform 1000ms var(--am-out); transition-delay: var(--d, 0ms); }
    html.auth-motion.am-go .am-item { opacity: 1; transform: none; }

    /* Background blobs drift slowly */
    html.auth-motion .bg-blob { animation: am-drift 22s ease-in-out infinite alternate; }
    html.auth-motion .bg-blob.b2 { animation-duration: 26s; animation-delay: -6s; }
    html.auth-motion .bg-blob.b3 { animation-duration: 30s; animation-delay: -12s; }
    @keyframes am-drift {
        0% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(2.5rem, -1.5rem) scale(1.06); }
        100% { transform: translate(-1.5rem, 2rem) scale(0.97); }
    }

    /* Small interactions */
    .form-group label { transition: color 200ms var(--am-soft); }
    .form-group:focus-within > label { color: var(--brand-success); }
    .btn-primary svg { transition: transform 500ms var(--am-out); }
    .btn-primary:hover svg { transform: translateX(3px); }

    /* Submit: keep the label, add a quiet spinner so it's clear something is happening */
    .btn-primary.is-sending { position: relative; pointer-events: none; opacity: 0.85; }
    .btn-primary.is-sending > svg { opacity: 0; }
    .btn-primary.is-sending::after {
        content: ""; position: absolute; right: 1.1rem; top: 50%; width: 1rem; height: 1rem; margin-top: -0.5rem;
        border-radius: 50%; border: 2px solid #fff; border-right-color: transparent;
        animation: am-spin 700ms linear infinite;
    }
    @keyframes am-spin { to { transform: rotate(360deg); } }

    @media (prefers-reduced-motion: reduce) {
        .btn-primary.is-sending::after { animation-duration: 2s; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var root = document.documentElement;

        // Spinner on submit (also useful with reduced motion). The form submits normally.
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('.btn-primary');
                if (btn && form.checkValidity()) btn.classList.add('is-sending');
            });
        });
        // If the browser restores the page from cache (Back button), clear the spinner.
        window.addEventListener('pageshow', function () {
            document.querySelectorAll('.btn-primary.is-sending').forEach(function (b) { b.classList.remove('is-sending'); });
        });

        if (!root.classList.contains('auth-motion')) return;

        // Title: split into masked words, keeping inline tags.
        var title = document.querySelector('.card .title');
        if (title) {
            var i = 0;
            (function walk(node) {
                Array.prototype.slice.call(node.childNodes).forEach(function (ch) {
                    if (ch.nodeType === 3) {
                        var frag = document.createDocumentFragment();
                        ch.textContent.split(/(\s+)/).forEach(function (part) {
                            if (!part) return;
                            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
                            var w = document.createElement('span'); w.className = 'am-w';
                            var wi = document.createElement('span'); wi.className = 'am-wi'; wi.textContent = part;
                            wi.style.setProperty('--i', i++);
                            w.appendChild(wi); frag.appendChild(w);
                        });
                        ch.replaceWith(frag);
                    } else if (ch.nodeType === 1) { walk(ch); }
                });
            })(title);
            title.setAttribute('aria-label', title.textContent.replace(/\s+/g, ' ').trim());
        }

        // Logo icon: normalise path lengths so it can draw itself.
        document.querySelectorAll('.brand-mark svg path, .brand-mark svg rect').forEach(function (p) {
            p.setAttribute('pathLength', '1');
        });

        // Stagger the rest of the card in reading order.
        var items = [];
        var back = document.querySelector('.card .back-link');
        if (back) { back.classList.add('am-item'); back.style.setProperty('--d', '220ms'); }
        var sub = document.querySelector('.card .subtitle');
        if (sub) items.push(sub);
        document.querySelectorAll('.card .alert, .card form > .form-group, .card form > .btn, .card form > .forgot-password, .card > .forgot-password, .card > .hint')
            .forEach(function (el) { items.push(el); });
        items.forEach(function (el, n) {
            el.classList.add('am-item');
            el.style.setProperty('--d', (560 + n * 90) + 'ms');
        });

        requestAnimationFrame(function () { setTimeout(function () { root.classList.add('am-go'); }, 60); });

        // Once everything is in, hand control back to the pages' own hover styles.
        setTimeout(function () {
            document.querySelectorAll('.am-item').forEach(function (el) { el.classList.remove('am-item'); });
        }, 2600);
    });
</script>
