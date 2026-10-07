<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Centrala Farmers Marketing Cooperative - CFMC</title>
    <meta name="description" content="The cooperative management system of Centrala Farmers Marketing Cooperative: membership, farmer loans, CBU and machine rental in one place.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&family=fraunces:500,500i,600" rel="stylesheet" />
    @vite('resources/css/palette.css')
    <script>
        document.documentElement.classList.add('js');
        // Same saved theme as the dashboards; applied before paint so there's no flash.
        (function () {
            var saved = null;
            try { saved = localStorage.getItem('cfmc-theme'); } catch (e) {}
            var theme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        :root {
            --serif: 'Fraunces', Georgia, 'Times New Roman', serif;
            --sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            --sprout-soft: color-mix(in srgb, var(--palette-sprout) 60%, var(--palette-paper));
            --shadow-color: var(--palette-ink);
            --shadow-card: 0 1px 2px color-mix(in srgb, var(--shadow-color) 4%, transparent);
            --shadow-photo: 0 24px 48px -24px color-mix(in srgb, var(--shadow-color) 35%, transparent);
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Dark mode: the dark palette itself lives in palette.css (shared with
           the dashboards via the saved "cfmc-theme"); only page tweaks here. */
        :root[data-bs-theme="dark"] {
            color-scheme: dark;
            --shadow-color: #000;
        }
        :root[data-bs-theme="dark"] header { background: var(--palette-paper); }
        :root[data-bs-theme="dark"] .btn-solid { color: var(--palette-paper); }
        :root[data-bs-theme="dark"] .btn-outline { border-color: color-mix(in srgb, var(--palette-ink) 70%, transparent); color: var(--palette-ink); }
        :root[data-bs-theme="dark"] .btn-outline:hover { background: color-mix(in srgb, var(--palette-ink) 8%, transparent); }
        :root[data-bs-theme="dark"] .btn-nav { border-color: var(--palette-field); color: var(--palette-field-light); }
        :root[data-bs-theme="dark"] .service-card,
        :root[data-bs-theme="dark"] .checklist { background: linear-gradient(160deg, #1d271e 0%, var(--palette-surface) 100%); }
        :root[data-bs-theme="dark"] .facts { background: var(--palette-surface); border-color: var(--palette-stone); }
        :root[data-bs-theme="dark"] .fact + .fact { border-color: var(--palette-stone); }
        :root[data-bs-theme="dark"] .fact svg { color: var(--palette-field-light); }
        :root[data-bs-theme="dark"] .trust-card { background: color-mix(in srgb, var(--palette-surface) 92%, transparent); backdrop-filter: blur(8px); }
        :root[data-bs-theme="dark"] .hero-photo img,
        :root[data-bs-theme="dark"] .story-media img { filter: brightness(0.92) saturate(0.95); }
        :root[data-bs-theme="dark"] .cta { background: linear-gradient(120deg, #22301f 0%, #1c2a1c 100%); border: 1px solid var(--palette-sprout-border); }
        :root[data-bs-theme="dark"] .btn-light { background: var(--palette-field); color: var(--palette-paper); }
        :root[data-bs-theme="dark"] .btn-light:hover { background: var(--palette-field-hover); }

        /* Theme toggle (sun / moon) */
        .theme-toggle {
            display: grid;
            place-items: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 999px;
            border: 1px solid var(--palette-stone);
            background: transparent;
            color: var(--palette-text-secondary);
            cursor: pointer;
            transition: background-color 160ms var(--ease), color 160ms var(--ease);
        }
        .theme-toggle:hover { background: var(--palette-sprout); color: var(--palette-field); }
        .theme-toggle svg { width: 1.1rem; height: 1.1rem; }
        .theme-toggle .icon-sun { display: none; }
        :root[data-bs-theme="dark"] .theme-toggle .icon-sun { display: block; }
        :root[data-bs-theme="dark"] .theme-toggle .icon-moon { display: none; }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--sans);
            background-color: var(--palette-paper);
            color: var(--palette-ink);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        section[id], footer[id] { scroll-margin-top: 5.5rem; }
        img { max-width: 100%; display: block; }
        :focus-visible { outline: 2px solid var(--palette-field); outline-offset: 3px; border-radius: 6px; }

        .container { max-width: 88rem; margin-inline: auto; padding-inline: clamp(1rem, 4vw, 3.5rem); }

        /* ---------- Header ---------- */
        header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--palette-surface);
            border-bottom: 1px solid var(--palette-stone);
            transition: box-shadow 220ms var(--ease);
        }
        header.is-scrolled { box-shadow: 0 6px 20px -14px color-mix(in srgb, var(--shadow-color) 35%, transparent); }
        .navbar { display: flex; align-items: center; gap: 2rem; min-height: 5rem; }
        .brand { display: flex; align-items: center; gap: 0.65rem; }
        .brand svg { width: 2.25rem; height: 2.25rem; flex-shrink: 0; }
        .brand-name { font-family: var(--serif); font-weight: 600; font-size: 1.85rem; line-height: 1; color: var(--palette-field); letter-spacing: -0.01em; }
        .brand-sub { display: block; margin-top: 0.3rem; font-size: 0.62rem; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: var(--palette-muted); }
        .nav-links { display: none; gap: 2.25rem; margin-left: auto; font-size: 0.92rem; color: var(--palette-text-secondary); }
        .nav-links a { padding-block: 0.35rem; border-bottom: 2px solid transparent; transition: color 160ms var(--ease), border-color 160ms var(--ease); }
        .nav-links a:hover { color: var(--palette-ink); }
        .nav-links a.is-active { color: var(--palette-ink); font-weight: 600; border-color: var(--palette-field); }
        .nav-actions { margin-left: auto; display: flex; align-items: center; gap: 0.75rem; }
        @media (min-width: 860px) { .nav-links { display: flex; } .nav-actions { margin-left: 1.5rem; } }
        @media (max-width: 480px) { .brand-sub { display: none; } .brand-name { font-size: 1.55rem; } }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            border-radius: 999px;
            padding: 0.8rem 1.6rem;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            border: 1.5px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            transition: background-color 160ms var(--ease), color 160ms var(--ease), border-color 160ms var(--ease);
        }
        .btn svg { width: 1rem; height: 1rem; transition: transform 160ms var(--ease); }
        .btn:hover svg.arrow { transform: translateX(3px); }
        .btn-solid { background: var(--palette-field); color: #fff; }
        .btn-solid:hover { background: var(--palette-field-hover); }
        .btn-outline { border-color: var(--palette-field); color: var(--palette-field); background: transparent; }
        .btn-outline:hover { background: var(--palette-sprout); }
        .btn-nav { border-radius: 0.65rem; padding: 0.55rem 1.2rem; font-size: 0.9rem; }

        /* ---------- Hero ---------- */
        .hero { position: relative; padding-block: 4rem 3rem; overflow: hidden; }
        .hero-grid { display: grid; gap: 3rem; align-items: center; }
        @media (min-width: 1200px) { .hero-grid { gap: 5rem; } }
        @media (min-width: 960px) { .hero-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr); } }
        .eyebrow { display: flex; flex-wrap: wrap; gap: 0.6rem; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--palette-text-secondary); }
        .eyebrow i { font-style: normal; color: var(--palette-wheat); }
        .hero h1 {
            font-family: var(--serif);
            font-weight: 600;
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            line-height: 1.04;
            letter-spacing: -0.025em;
            margin: 1.1rem 0 1.25rem;
            text-wrap: balance;
        }
        .hero h1 em { font-style: italic; font-weight: 500; color: var(--palette-field-light); }
        .hero .lead { color: var(--palette-text-secondary); font-size: 1.05rem; max-width: 46ch; margin-bottom: 2rem; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 0.85rem; }
        .hero-actions form { display: contents; }
        .hero-leaf { position: absolute; left: -0.5rem; bottom: 1rem; width: 5rem; opacity: 0.5; pointer-events: none; }

        .hero-photo { position: relative; }
        .hero-photo img {
            width: 100%;
            aspect-ratio: 735 / 479;
            object-fit: cover;
            border-radius: 1.25rem;
            box-shadow: var(--shadow-photo);
        }
        .trust-card {
            position: absolute;
            right: 1rem;
            bottom: -1.5rem;
            display: grid;
            gap: 0.6rem;
            min-width: 13.5rem;
            padding: 0.9rem 1rem;
            background: var(--palette-surface);
            border: 1px solid var(--palette-stone);
            border-radius: 0.9rem;
            box-shadow: 0 16px 32px -18px color-mix(in srgb, var(--shadow-color) 40%, transparent);
        }
        .trust-title { display: flex; align-items: center; gap: 0.65rem; font-weight: 600; font-size: 0.92rem; line-height: 1.25; }
        .trust-title span { display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: 999px; background: var(--palette-sprout); color: var(--palette-field); flex-shrink: 0; }
        .trust-title svg { width: 1rem; height: 1rem; }
        .trust-place { display: flex; align-items: center; gap: 0.45rem; font-size: 0.78rem; color: var(--palette-muted); }
        .trust-place svg { width: 0.9rem; height: 0.9rem; color: var(--palette-field); flex-shrink: 0; }
        @media (max-width: 520px) { .trust-card { right: 0.75rem; left: 0.75rem; min-width: 0; } }

        /* ---------- Sections ---------- */
        .section { padding-block: 4.5rem; }
        .section-head { max-width: 46rem; margin-bottom: 2.5rem; }
        .section-head .eyebrow { margin-bottom: 0.75rem; }
        .section-title { font-family: var(--serif); font-weight: 600; font-size: clamp(1.9rem, 3.5vw, 2.5rem); line-height: 1.12; letter-spacing: -0.02em; text-wrap: balance; }
        .section-sub { margin-top: 0.75rem; color: var(--palette-text-secondary); }

        /* ---------- Services ---------- */
        .services-grid { display: grid; gap: 1.25rem; }
        @media (min-width: 860px) { .services-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .service-card {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 1.75rem;
            background: var(--palette-surface);
            border: 1px solid var(--palette-stone);
            border-radius: 1rem;
            box-shadow: var(--shadow-card);
            transition: border-color 200ms var(--ease);
        }
        .service-card:hover { border-color: var(--palette-sprout-border); }
        .service-card::after {
            content: "";
            position: absolute;
            right: -1.5rem;
            bottom: -1.5rem;
            width: 6.5rem;
            height: 6.5rem;
            border-radius: 999px;
            background: var(--sprout-soft);
            pointer-events: none;
        }
        .service-icon { display: grid; place-items: center; width: 3.25rem; height: 3.25rem; border-radius: 999px; background: var(--palette-sprout); color: var(--palette-field); }
        .service-icon svg { width: 1.5rem; height: 1.5rem; }
        .service-card h3 { font-family: var(--serif); font-weight: 600; font-size: 1.45rem; letter-spacing: -0.01em; margin-top: 0.35rem; }
        .service-card p { color: var(--palette-text-secondary); font-size: 0.95rem; }
        .service-link { position: relative; z-index: 1; margin-top: auto; padding-top: 0.5rem; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600; font-size: 0.92rem; color: var(--palette-field); }
        .service-link svg { width: 1rem; height: 1rem; transition: transform 160ms var(--ease); }
        .service-link:hover svg { transform: translateX(3px); }

        /* ---------- Facts strip ---------- */
        .facts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            background: var(--sprout-soft);
            border: 1px solid var(--palette-sprout-border);
            border-radius: 1rem;
        }
        @media (min-width: 860px) { .facts { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .fact { display: grid; justify-items: center; gap: 0.35rem; padding: 1.6rem 1rem; text-align: center; }
        .fact + .fact { border-left: 1px solid var(--palette-sprout-border); }
        @media (max-width: 859px) {
            .fact:nth-child(3) { border-left: 0; }
            .fact:nth-child(n+3) { border-top: 1px solid var(--palette-sprout-border); }
        }
        .fact svg { width: 1.6rem; height: 1.6rem; color: var(--palette-field); }
        .fact b { font-size: 1.05rem; font-weight: 600; }
        .fact span { font-size: 0.85rem; color: var(--palette-text-secondary); }
        .facts-numbers .fact { padding-block: 1.75rem; }
        .facts-numbers .fact b { font-size: clamp(1.6rem, 3vw, 2rem); font-weight: 700; line-height: 1.15; letter-spacing: -0.01em; font-variant-numeric: tabular-nums; }

        /* ---------- Story rows (photo + text, alternating sides) ---------- */
        .story-row {
            display: grid;
            gap: 2rem;
            align-items: center;
            margin-bottom: 5rem;
        }
        .story-row:last-child { margin-bottom: 0; }
        @media (min-width: 860px) {
            .story-row { grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: clamp(2.5rem, 5vw, 4.5rem); }
            .story-row.is-flipped { grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr); }
            .story-row.is-flipped .story-media { order: 2; }
        }
        .story-media { position: relative; }
        .story-media img {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            border-radius: 1.1rem;
            box-shadow: var(--shadow-photo);
        }
        .story-tag {
            position: absolute;
            left: 1rem;
            bottom: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--palette-surface) 92%, transparent);
            font-size: 0.78rem;
            font-weight: 600;
        }
        .story-tag::before { content: ""; width: 0.45rem; height: 0.45rem; border-radius: 999px; background: var(--palette-field); }
        .story-step { font-family: var(--serif); font-style: italic; color: var(--palette-wheat-deep); font-size: 0.95rem; }
        .story-copy h3 { font-family: var(--serif); font-weight: 600; font-size: clamp(1.6rem, 2.8vw, 2rem); line-height: 1.15; letter-spacing: -0.015em; margin: 0.4rem 0 0.8rem; text-wrap: balance; }
        .story-copy p { color: var(--palette-text-secondary); max-width: 52ch; }
        .story-points { list-style: none; display: grid; gap: 0.6rem; margin-top: 1.25rem; }
        .story-points li { display: flex; gap: 0.65rem; color: var(--palette-text-secondary); font-size: 0.95rem; }
        .story-points svg { width: 1.15rem; height: 1.15rem; flex-shrink: 0; margin-top: 0.2rem; padding: 0.2rem; border-radius: 999px; background: var(--palette-sprout); color: var(--palette-field); }

        /* Photos slide in from their own side; text rises in just after.
           Gated on .js so the page is fully visible if scripts don't run. */
        .js .story-row .story-media { opacity: 0; transform: translateX(-2.5rem); transition: opacity 800ms var(--ease), transform 800ms cubic-bezier(0.2, 0.7, 0.2, 1); }
        .js .story-row.is-flipped .story-media { transform: translateX(2.5rem); }
        .js .story-row .story-copy { opacity: 0; transform: translateY(1.5rem); transition: opacity 800ms var(--ease) 150ms, transform 800ms cubic-bezier(0.2, 0.7, 0.2, 1) 150ms; }
        .js .story-row.in-view .story-media,
        .js .story-row.in-view .story-copy { opacity: 1; transform: none; }
        @media (max-width: 859px) {
            .js .story-row .story-media,
            .js .story-row.is-flipped .story-media { transform: translateY(1.5rem); }
        }

        /* ---------- Photo banner (parallax) ---------- */
        .photo-banner {
            position: relative;
            overflow: hidden;
            height: clamp(20rem, 55vh, 32rem);
            border-radius: 1.4rem;
        }
        .photo-banner img {
            position: absolute;
            inset: -12% 0;
            width: 100%;
            height: 124%;
            object-fit: cover;
            will-change: transform;
        }
        .photo-banner::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg,
                color-mix(in srgb, var(--palette-field-deep) 85%, transparent) 0%,
                color-mix(in srgb, var(--palette-field-deep) 40%, transparent) 55%,
                transparent 100%);
        }
        .photo-banner-copy {
            position: relative;
            z-index: 1;
            height: 100%;
            max-width: 36rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-inline: clamp(1.5rem, 5vw, 4rem);
            color: #fff;
        }
        .photo-banner blockquote { font-family: var(--serif); font-weight: 500; font-size: clamp(1.6rem, 3.2vw, 2.4rem); line-height: 1.2; letter-spacing: -0.01em; }
        .photo-banner blockquote em { color: var(--palette-sprout); }
        .photo-banner cite { margin-top: 1rem; font-style: normal; font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.85; }

        /* ---------- About ---------- */
        .about-grid { display: grid; gap: 2.5rem; align-items: start; }
        @media (min-width: 960px) { .about-grid { grid-template-columns: 1.1fr 1fr; gap: 4rem; } }
        .about-copy p { color: var(--palette-text-secondary); margin-top: 1rem; max-width: 60ch; }
        .checklist { display: grid; gap: 0.25rem; padding: 0.5rem 1.5rem; background: var(--palette-surface); border: 1px solid var(--palette-stone); border-radius: 1rem; }
        .check-item { display: flex; gap: 0.9rem; padding-block: 1rem; }
        .check-item + .check-item { border-top: 1px solid var(--palette-stone); }
        .check-badge { display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border-radius: 999px; background: var(--palette-sprout); color: var(--palette-field); flex-shrink: 0; }
        .check-badge svg { width: 0.9rem; height: 0.9rem; }
        .check-item h3 { font-size: 1rem; font-weight: 600; }
        .check-item p { font-size: 0.9rem; color: var(--palette-text-secondary); }

        /* ---------- CTA ---------- */
        .cta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 2.25rem 2.5rem;
            background: var(--palette-field);
            border-radius: 1.25rem;
            color: #fff;
        }
        .cta h2 { font-family: var(--serif); font-weight: 600; font-size: clamp(1.6rem, 3vw, 2.1rem); line-height: 1.15; letter-spacing: -0.015em; }
        .cta p { color: color-mix(in srgb, #fff 80%, transparent); margin-top: 0.4rem; }
        .cta form { display: contents; }
        .btn-light { background: var(--palette-surface); color: var(--palette-field); }
        .btn-light:hover { background: var(--palette-sprout); }
        @media (max-width: 640px) { .cta { padding: 1.75rem 1.5rem; } }

        /* ---------- Footer ---------- */
        footer { padding-block: 2.25rem 2.5rem; border-top: 1px solid var(--palette-stone); }
        .footer-band { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem 1.25rem; }
        .footer-band svg { width: 2.5rem; height: 2.5rem; flex-shrink: 0; }
        .footer-mid b { display: block; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.2em; color: var(--palette-text-secondary); }
        .footer-mid i { font-size: 0.82rem; color: var(--palette-muted); }
        .footer-meta { margin-left: auto; text-align: right; font-size: 0.82rem; color: var(--palette-muted); }
        @media (max-width: 640px) { .footer-meta { margin-left: 0; text-align: left; width: 100%; } }

        /* ---------- Reveal-on-scroll ---------- */
        .reveal { opacity: 0; transform: translateY(16px); transition: opacity 600ms var(--ease), transform 600ms var(--ease); }
        .reveal.in-view { opacity: 1; transform: translateY(0); }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
            .btn svg, .service-link svg { transition: none !important; }
            .js .story-row .story-media, .js .story-row .story-copy { opacity: 1 !important; transform: none !important; transition: none !important; }
            .photo-banner img { transform: none !important; }
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <header id="siteHeader">
        <div class="container navbar">
            <a href="#top" class="brand" aria-label="Centrala Farmers Marketing Cooperative home">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--palette-field)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22V9"/><path d="M12 14c-4.2 0-7.2-3-7.2-7.2 4.2 0 7.2 3 7.2 7.2Z" fill="var(--palette-sprout)"/><path d="M12 14c4.2 0 7.2-3 7.2-7.2-4.2 0-7.2 3-7.2 7.2Z" fill="var(--palette-sprout)"/><path d="M12 9c0-3 1.4-5.2 3-6.6.9 2.6-.1 5.2-3 6.6Z"/></svg>
                <span>
                    <span class="brand-name">Centrala</span>
                    <span class="brand-sub">Farmers Marketing Cooperative</span>
                </span>
            </a>

            <nav class="nav-links" aria-label="Main">
                <a href="#top" class="is-active">Home</a>
                <a href="#about">About</a>
                <a href="#services">Services</a>
                <a href="#contact">Contact</a>
            </nav>

            <div class="nav-actions">
                <button type="button" class="theme-toggle" id="themeToggle" aria-label="Switch to dark mode" title="Toggle dark mode">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>
                @if (Route::has('login'))
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-nav">Login</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline btn-nav">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>
                            Login
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero" id="top">
            <svg class="hero-leaf" viewBox="0 0 70 120" aria-hidden="true"><path d="M10 110C10 60 30 30 60 10 55 50 40 85 10 110Z" fill="var(--palette-sprout-border)"/><path d="M5 115C20 85 25 70 22 40" stroke="var(--palette-sprout-border)" stroke-width="2" fill="none"/></svg>

            <div class="container hero-grid">
                <div>
                    <div class="eyebrow">Together <i>&bull;</i> Farmers <i>&bull;</i> Stronger <i>&bull;</i> Tomorrow</div>
                    <h1>Growing <em>together,</em><br>managing better.</h1>
                    <p class="lead">
                        The unified cooperative management system for Centrala farmers. Apply for loans,
                        track your CBU, book machinery and keep your membership up to date, all in one place.
                    </p>

                    <div class="hero-actions">
                        @if (Route::has('login'))
                            @auth
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-solid">Get Started</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-solid">
                                    Get Started
                                    <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </a>
                            @endauth
                        @endif
                        <a href="#services" class="btn btn-outline">Learn More</a>
                    </div>
                </div>

                <div class="hero-photo">
                    <img src="{{ asset('images/hero-harvest.jpg') }}" width="735" height="479"
                         alt="Cooperative farmers loading sacks of harvested rice onto a combine harvester in a Centrala rice field">
                    <div class="trust-card">
                        <div class="trust-title">
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 20A7 7 0 0 1 4 13c0-6 7-10 16-10 0 9-4 16-9 17Z"/><path d="M4 21c4-6 8-9 12-11"/></svg></span>
                            Trusted by<br>local farmers
                        </div>
                        <div class="trust-place">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            Centrala, Surallah, South Cotabato
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Services</div>
                    <h2 class="section-title">Everything a member needs, in one system</h2>
                </div>

                <div class="services-grid">
                    <article class="service-card reveal">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3"/><circle cx="5" cy="10" r="2.2"/><circle cx="19" cy="10" r="2.2"/><path d="M7 19c0-3 2.2-5 5-5s5 2 5 5"/><path d="M1.5 18c0-2 1.5-3.5 3.5-3.5"/><path d="M22.5 18c0-2-1.5-3.5-3.5-3.5"/></svg>
                        </div>
                        <h3>Member Registration</h3>
                        <p>Register as a cooperative member and keep your farmer profile and records organized and secure.</p>
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="service-link">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                        @endif
                    </article>

                    <article class="service-card reveal">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M12 5v4"/><path d="M2 17h4l4 2h5a2 2 0 0 0 0-4h-3"/><path d="M6 14l3-1.5h3"/><path d="M15 15l5-2a1.6 1.6 0 0 1 1.5 2.6L16 20"/></svg>
                        </div>
                        <h3>Farmer Loans</h3>
                        <p>Apply for loans, follow your repayment schedule, view your CBU and pay on time, all from one place.</p>
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="service-link">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                        @endif
                    </article>

                    <article class="service-card reveal">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7" cy="17" r="3"/><circle cx="18" cy="18" r="2"/><path d="M4 14V8h7l2 5h5a2 2 0 0 1 2 2v3"/><path d="M10 17h6"/><path d="M7 8V5h3"/></svg>
                        </div>
                        <h3>Machine Rental</h3>
                        <p>Check tractor and harvester availability, request a schedule, and see your bookings on the calendar.</p>
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="service-link">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                        @endif
                    </article>
                </div>
            </div>
        </section>

        <!-- Facts Strip -->
        <section aria-label="Cooperative at a glance">
            <div class="container">
                @if (!empty($stats))
                    @php
                        // Compact peso amount: ₱4.8M, ₱350K, ₱9,500.
                        $loaned = $stats['loaned'];
                        $loanedLabel = $loaned >= 1000000
                            ? '₱' . rtrim(rtrim(number_format($loaned / 1000000, 1), '0'), '.') . 'M'
                            : ($loaned >= 1000
                                ? '₱' . rtrim(rtrim(number_format($loaned / 1000, 1), '0'), '.') . 'K'
                                : '₱' . number_format($loaned));
                    @endphp
                    <div class="facts facts-numbers reveal">
                        <div class="fact">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 20A7 7 0 0 1 4 13c0-6 7-10 16-10 0 9-4 16-9 17Z"/><path d="M4 21c4-6 8-9 12-11"/></svg>
                            <b>{{ number_format($stats['farmers']) }}</b>
                            <span>Registered Farmers</span>
                        </div>
                        <div class="fact">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3"/><circle cx="5" cy="10" r="2.2"/><circle cx="19" cy="10" r="2.2"/><path d="M7 19c0-3 2.2-5 5-5s5 2 5 5"/><path d="M1.5 18c0-2 1.5-3.5 3.5-3.5"/><path d="M22.5 18c0-2-1.5-3.5-3.5-3.5"/></svg>
                            <b>{{ number_format($stats['members']) }}</b>
                            <span>Active Members</span>
                        </div>
                        <div class="fact">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7" cy="17" r="3"/><circle cx="18" cy="18" r="2"/><path d="M4 14V8h7l2 5h5a2 2 0 0 1 2 2v3"/><path d="M10 17h6"/><path d="M7 8V5h3"/></svg>
                            <b>{{ number_format($stats['machines']) }}</b>
                            <span>Available Machines</span>
                        </div>
                        <div class="fact">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5"/><path d="M9 12h7"/><path d="M9 16h5"/></svg>
                            <b>{{ $loanedLabel }}</b>
                            <span>Total Loaned Amount</span>
                        </div>
                    </div>
                @else
                <div class="facts reveal">
                    <div class="fact">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 20A7 7 0 0 1 4 13c0-6 7-10 16-10 0 9-4 16-9 17Z"/><path d="M4 21c4-6 8-9 12-11"/></svg>
                        <b>Member-owned</b>
                        <span>Run by and for local farmers</span>
                    </div>
                    <div class="fact">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/><path d="M7 15h3"/></svg>
                        <b>Loans &amp; CBU</b>
                        <span>Track balances and payments</span>
                    </div>
                    <div class="fact">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7" cy="17" r="3"/><circle cx="18" cy="18" r="2"/><path d="M4 14V8h7l2 5h5a2 2 0 0 1 2 2v3"/><path d="M10 17h6"/></svg>
                        <b>Shared machinery</b>
                        <span>Book tractors and harvesters</span>
                    </div>
                    <div class="fact">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                        <b>Open anytime</b>
                        <span>On your phone or computer</span>
                    </div>
                </div>
                @endif
            </div>
        </section>

        <!-- Story Section: the cooperative in action -->
        <section id="stories" class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Our cooperative in action</div>
                    <h2 class="section-title" style="margin-top: 0.75rem;">From the field <em style="font-style: italic; font-weight: 500; color: var(--palette-field-light);">to the market,</em> together.</h2>
                    <p class="section-sub">A look at what Centrala members do every season, and how the cooperative helps along the way.</p>
                </div>

                <article class="story-row">
                    <div class="story-media">
                        <img src="{{ asset('images/harvest-hauling.jpg') }}" width="1200" height="900" loading="lazy" decoding="async"
                             alt="Farmers loading sacks of harvested palay onto the cooperative truck while a carabao cart brings more from the field">
                        <span class="story-tag">Harvest season</span>
                    </div>
                    <div class="story-copy">
                        <span class="story-step">01 &middot; Marketing</span>
                        <h3>From field to market</h3>
                        <p>When the harvest comes in, members bring their palay straight from the field to the cooperative truck, by hand and by carabao cart, to be sold together at a fair price.</p>
                        <ul class="story-points">
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>Harvest payments recorded for every member</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>Fair pricing through collective marketing</li>
                        </ul>
                    </div>
                </article>

                <article class="story-row is-flipped">
                    <div class="story-media">
                        <img src="{{ asset('images/member-seminar.jpg') }}" width="960" height="720" loading="lazy" decoding="async"
                             alt="Cooperative members attending a seminar on cooperativism beside a Kubota combine harvester">
                        <span class="story-tag">Member seminar</span>
                    </div>
                    <div class="story-copy">
                        <span class="story-step">02 &middot; Membership</span>
                        <h3>Learning and growing together</h3>
                        <p>Members meet regularly for seminars on cooperativism, farm practices and how to use the cooperative's services, including this system.</p>
                        <ul class="story-points">
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>Membership registration and approval online</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>Announcements sent to every member</li>
                        </ul>
                    </div>
                </article>

                <article class="story-row">
                    <div class="story-media">
                        <img src="{{ asset('images/rcef-harvester.jpg') }}" width="1200" height="675" loading="lazy" decoding="async"
                             alt="A new Yanmar AW70V combine harvester on a trailer pulled by a tractor, from the RCEF Mechanization Program">
                        <span class="story-tag">RCEF Mechanization Program</span>
                    </div>
                    <div class="story-copy">
                        <span class="story-step">03 &middot; Machinery</span>
                        <h3>Modern machines for every member</h3>
                        <p>Through the RCEF Mechanization Program, the cooperative now has combine harvesters and tractors that members can book for their own fields.</p>
                        <ul class="story-points">
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>See which machines are available</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>Request a schedule and track it on the calendar</li>
                        </ul>
                    </div>
                </article>
            </div>
        </section>

        <!-- Photo Banner -->
        <section aria-label="Cooperative motto">
            <div class="container">
                <div class="photo-banner reveal">
                    <img id="bannerPhoto" src="{{ asset('images/harvest-hauling.jpg') }}" alt="" loading="lazy" decoding="async">
                    <div class="photo-banner-copy">
                        <blockquote>&ldquo;Stronger <em>together</em> than any one of us alone.&rdquo;</blockquote>
                        <cite>Centrala Farmers Marketing Cooperative</cite>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="section">
            <div class="container about-grid">
                <div class="about-copy reveal">
                    <div class="eyebrow">About CFMC</div>
                    <h2 class="section-title" style="margin-top: 0.75rem;">A cooperative that grows with its farmers</h2>
                    <p>Centrala Farmers Marketing Cooperative brings farmers in Centrala, Surallah together to share resources, access fair financing and market their harvest.</p>
                    <p>This system puts the cooperative's everyday work online: membership records, loan applications and payments, capital build-up, machinery schedules and announcements, so members and staff spend less time on paperwork.</p>
                </div>
                <div class="checklist reveal">
                    <div class="check-item">
                        <span class="check-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg></span>
                        <div>
                            <h3>Easy to use</h3>
                            <p>Simple screens designed for every member</p>
                        </div>
                    </div>
                    <div class="check-item">
                        <span class="check-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg></span>
                        <div>
                            <h3>Transparent</h3>
                            <p>See your loans, payments and CBU anytime</p>
                        </div>
                    </div>
                    <div class="check-item">
                        <span class="check-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg></span>
                        <div>
                            <h3>Community focused</h3>
                            <p>Built around cooperative values</p>
                        </div>
                    </div>
                    <div class="check-item">
                        <span class="check-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg></span>
                        <div>
                            <h3>Secure</h3>
                            <p>Your records are protected and private</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="section" style="padding-top: 0;">
            <div class="container">
                <div class="cta reveal">
                    <div>
                        <h2>Ready to get started?</h2>
                        <p>Sign in to manage your membership, loans and machine schedules.</p>
                    </div>
                    @if (Route::has('login'))
                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-light">Sign in</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-light">
                                Sign In
                                <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-band">
                <svg viewBox="0 0 44 44" aria-hidden="true"><path d="M8 40C14 26 22 18 36 12" stroke="var(--palette-sprout-border)" stroke-width="1.5" fill="none"/><path d="M18 26c-6-1-9-6-8-11 6 1 9 6 8 11Z" fill="var(--palette-sprout-border)"/><path d="M26 20c1-6 6-9 11-8-1 6-6 9-11 8Z" fill="var(--palette-sprout-border)"/></svg>
                <div class="footer-mid">
                    <b>CENTRALA FARMERS MARKETING COOPERATIVE</b>
                    <i>Supporting our farmers. Building a stronger tomorrow.</i>
                </div>
                <div class="footer-meta">
                    <p>Centrala, Surallah, South Cotabato</p>
                    <p>&copy; {{ date('Y') }} CFMC. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            var header = document.getElementById('siteHeader');
            function onScroll() {
                header.classList.toggle('is-scrolled', window.scrollY > 8);
            }
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            var revealEls = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window && revealEls.length) {
                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('in-view');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15 });
                revealEls.forEach(function (el) { observer.observe(el); });
            } else {
                revealEls.forEach(function (el) { el.classList.add('in-view'); });
            }

            // Story rows: photo slides in from its side, text follows.
            var storyRows = document.querySelectorAll('.story-row');
            if ('IntersectionObserver' in window && storyRows.length) {
                var storyObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('in-view');
                            storyObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.2 });
                storyRows.forEach(function (el) { storyObserver.observe(el); });
            } else {
                storyRows.forEach(function (el) { el.classList.add('in-view'); });
            }

            // Banner photo drifts slightly slower than the page (parallax).
            var bannerPhoto = document.getElementById('bannerPhoto');
            var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (bannerPhoto && !reduceMotion) {
                var banner = bannerPhoto.parentElement;
                var ticking = false;
                function moveBanner() {
                    var rect = banner.getBoundingClientRect();
                    var progress = (rect.top + rect.height / 2 - window.innerHeight / 2) / window.innerHeight;
                    bannerPhoto.style.transform = 'translateY(' + (progress * -40) + 'px)';
                    ticking = false;
                }
                window.addEventListener('scroll', function () {
                    if (!ticking) { ticking = true; window.requestAnimationFrame(moveBanner); }
                }, { passive: true });
                moveBanner();
            }

            // Light / dark toggle, saved under the same key the dashboards use.
            var themeToggle = document.getElementById('themeToggle');
            function syncToggleLabel() {
                var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                themeToggle.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            }
            if (themeToggle) {
                syncToggleLabel();
                themeToggle.addEventListener('click', function () {
                    var next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-bs-theme', next);
                    try { localStorage.setItem('cfmc-theme', next); } catch (e) {}
                    syncToggleLabel();
                });
            }

            // Highlight the nav link for the section in view.
            var navLinks = document.querySelectorAll('.nav-links a');
            var sections = ['top', 'about', 'services', 'contact']
                .map(function (id) { return document.getElementById(id); })
                .filter(Boolean);
            function setActive(id) {
                navLinks.forEach(function (link) {
                    link.classList.toggle('is-active', link.getAttribute('href') === '#' + id);
                });
            }
            if ('IntersectionObserver' in window && sections.length) {
                var navObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) setActive(entry.target.id);
                    });
                }, { rootMargin: '-45% 0px -50% 0px' });
                sections.forEach(function (el) { navObserver.observe(el); });

                // The footer is too short to reach the middle of the screen,
                // so mark Contact active once the page is scrolled to the end.
                window.addEventListener('scroll', function () {
                    if (window.innerHeight + window.scrollY >= document.body.scrollHeight - 4) setActive('contact');
                }, { passive: true });
            }
        })();
    </script>
</body>
</html>
