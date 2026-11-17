<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Centrala Farmers Marketing Cooperative - CFMC</title>
    <meta name="description" content="The cooperative management system of Centrala Farmers Marketing Cooperative: membership, farmer loans, CBU and machine rental in one place.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=libre-caslon-display:400&family=libre-caslon-text:400,400i&family=hanken-grotesk:400,500,600&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="{{ asset('images/landing/hero-harvest.jpg') }}">
    <script>
        // Motion is opt-in: visitors who prefer reduced motion get a still page.
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('motion');
        }
    </script>
    <style>
        /* Quiet editorial landing page on pale sage paper. Light only by design.
           Left-aligned type, the cooperative's own photos in one shared color
           grade with caption lines, a ledger row for the live figures, a loan
           estimator, and deep-forest bands for the motto and sign-in. */
        :root {
            --paper: #eef1ea;
            --paper-2: #e4e9e0;
            --ink: #15211a;
            --ink-2: #4b574f;
            --rule: #c9d1c6;
            --forest: #123222;
            --forest-2: #1c4430;
            --brass: #a07d34;
            --on-dark: #eef1ea;
            --on-dark-2: #b9c6ba;
            --display: 'Libre Caslon Display', 'Libre Caslon Text', Georgia, serif;
            --serif: 'Libre Caslon Text', Georgia, serif;
            --sans: 'Hanken Grotesk', ui-sans-serif, system-ui, sans-serif;
            --grade: saturate(0.66) contrast(1.06) sepia(0.14) brightness(1.02);
            --ease: cubic-bezier(0.2, 0.7, 0.2, 1);
            color-scheme: light;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--sans); font-size: 17px; line-height: 1.6; background: var(--paper); color: var(--ink); -webkit-font-smoothing: antialiased; overflow-x: hidden; overflow-x: clip; }
        @media (min-width: 960px) { body { font-size: 18px; } }
        img { display: block; max-width: 100%; }
        a { color: inherit; }
        :focus-visible { outline: 2px solid var(--brass); outline-offset: 4px; border-radius: 4px; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

        .wrap { max-width: 1400px; margin-inline: auto; padding-left: max(20px, env(safe-area-inset-left, 0px)); padding-right: max(20px, env(safe-area-inset-right, 0px)); }
        @media (min-width: 960px) { .wrap { padding-left: max(56px, env(safe-area-inset-left, 0px)); padding-right: max(56px, env(safe-area-inset-right, 0px)); } }
        @media (min-width: 1800px) { .wrap { max-width: 1560px; } }

        /* Type scale */
        .h-xl { font-family: var(--display); font-weight: 400; font-size: clamp(3rem, 8.6vw, 7.4rem); line-height: 0.96; letter-spacing: -0.025em; text-wrap: balance; }
        .h-l { font-family: var(--display); font-weight: 400; font-size: clamp(2.2rem, 4.6vw, 3.9rem); line-height: 1.04; letter-spacing: -0.02em; text-wrap: balance; }
        .h-m { font-family: var(--display); font-weight: 400; font-size: clamp(1.6rem, 2.6vw, 2.2rem); line-height: 1.12; letter-spacing: -0.012em; }
        .lede { font-family: var(--serif); font-size: clamp(1.1rem, 1.6vw, 1.3rem); line-height: 1.65; color: var(--ink-2); max-width: 36em; }
        .small { font-size: 0.88rem; color: var(--ink-2); }
        .caption { font-family: var(--serif); font-style: italic; font-size: 0.9rem; color: var(--ink-2); }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 52px; padding: 0.85rem 1.6rem; border-radius: 10px; font: 600 1rem/1 var(--sans); text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: background-color 0.25s var(--ease), color 0.25s var(--ease), border-color 0.25s var(--ease); }
        .btn-solid { background: var(--forest); color: var(--on-dark); }
        .btn-solid:hover { background: var(--forest-2); }
        .btn-line { border-color: var(--ink); color: var(--ink); background: transparent; }
        .btn-line:hover { background: var(--ink); color: var(--paper); }
        .btn-light { background: var(--on-dark); color: var(--forest); }
        .btn-light:hover { background: #ffffff; }
        .link { font-weight: 600; text-decoration: underline; text-decoration-color: var(--brass); text-decoration-thickness: 1.5px; text-underline-offset: 5px; transition: text-decoration-color 0.2s var(--ease); }
        .link:hover { text-decoration-color: currentColor; }
        form.inline { display: contents; }

        /* Header */
        .hd { position: sticky; top: 0; z-index: 30; background: var(--paper); transition: box-shadow 0.3s var(--ease); }
        .hd.is-scrolled { box-shadow: 0 1px 0 var(--rule); }
        .hd-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 76px; }
        .mark { display: flex; align-items: center; gap: 0.7rem; min-height: 44px; text-decoration: none; min-width: 0; }
        .mark svg { width: 30px; height: 30px; color: var(--forest); flex-shrink: 0; }
        .mark b { display: block; font-family: var(--display); font-weight: 400; font-size: 1.5rem; line-height: 1; letter-spacing: -0.01em; }
        .mark small { display: none; font-size: 0.76rem; color: var(--ink-2); margin-top: 0.2rem; white-space: nowrap; }
        @media (min-width: 381px) { .mark small { display: block; } }
        .nav { display: none; gap: 2.2rem; font-size: 0.95rem; font-weight: 500; }
        .nav a { text-decoration: none; color: var(--ink-2); padding-block: 0.3rem; border-bottom: 1px solid transparent; transition: color 0.2s, border-color 0.2s; }
        .nav a:hover, .nav a[aria-current="true"] { color: var(--ink); border-color: var(--ink); }
        .hd .btn { min-height: 44px; padding: 0.6rem 1.25rem; }
        @media (min-width: 960px) { .nav { display: flex; } }
        .hd-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }

        /* Phones and tablets: a menu button opens a full-screen panel */
        .menu-btn { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 10px; border: 1px solid var(--rule); background: transparent; color: var(--ink); cursor: pointer; transition: border-color 0.2s; }
        .menu-btn:hover { border-color: var(--ink); }
        .menu-btn svg { width: 20px; height: 20px; }
        @media (min-width: 960px) { .hd .menu-btn { display: none; } }
        .menu { position: fixed; inset: 0; z-index: 60; display: flex; flex-direction: column; overflow-y: auto; overscroll-behavior: contain; background: var(--paper);
            padding: env(safe-area-inset-top, 0px) max(20px, env(safe-area-inset-right, 0px)) calc(1.5rem + env(safe-area-inset-bottom, 0px)) max(20px, env(safe-area-inset-left, 0px)); }
        .menu[hidden] { display: none; }
        .menu-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 76px; }
        .menu nav { display: grid; margin-block: auto; padding-block: 2rem; }
        .menu nav a { display: flex; align-items: center; min-height: 64px; border-bottom: 1px solid var(--rule); font-family: var(--display); font-size: clamp(2rem, 9vw, 2.8rem); line-height: 1.1; text-decoration: none; }
        .menu nav a:first-child { border-top: 1px solid var(--ink); }
        .menu .btn { width: 100%; min-height: 56px; }
        .motion .menu:not([hidden]) { animation: menu-in 0.35s var(--ease); }
        @keyframes menu-in { from { opacity: 0; transform: translateY(-10px); } }
        .progress { position: absolute; left: 0; right: 0; bottom: -1px; height: 2px; background: var(--brass); transform-origin: 0 50%; transform: scaleX(0); }

        /* Hero: copy on the left, a tall rounded photograph on the right. */
        .hero { padding-block: clamp(1.5rem, 3vw, 2.5rem) clamp(3.5rem, 7vw, 6rem); }
        /* Desktop: the hero fills exactly the first screen below the header, so
           the figures row starts below the fold instead of peeking in. */
        /* Desktop: the hero fills the first screen (capped on very tall screens).
           Not on tablets: a tall portrait screen would leave empty space around it. */
        @media (min-width: 720px) { .hero { padding-block: 2.5rem 4rem; } }
        @media (min-width: 960px) {
            .hero { display: flex; align-items: center; padding-block: 2.5rem; min-height: min(calc(100vh - 76px), 1080px); min-height: min(calc(100svh - 76px), 1080px); }
            .hero > .wrap { width: 100%; }
        }
        .hero-grid { display: grid; gap: clamp(2.5rem, 5vw, 4rem); align-items: center; }
        /* Text and photo side by side from tablet width */
        @media (min-width: 720px) { .hero-grid { grid-template-columns: minmax(0, 11fr) minmax(0, 10fr); gap: clamp(2rem, 5vw, 5rem); } }
        .hero-copy { min-width: 0; }
        .hero-title { font-size: clamp(3rem, 8.4vw, 4.4rem); line-height: 0.98; letter-spacing: -0.03em; }
        @media (min-width: 720px) { .hero-title { font-size: clamp(2.6rem, min(6vw, 11vh), 5.7rem); font-size: clamp(2.6rem, min(6vw, 11svh), 5.7rem); } }
        @media (min-width: 960px) { .hero-title { font-size: clamp(3.4rem, min(4.75vw, 11vh), 5.7rem); font-size: clamp(3.4rem, min(4.75vw, 11svh), 5.7rem); } }
        @media (min-width: 1800px) { .hero-title { font-size: clamp(3.4rem, min(4.75vw, 11vh), 6.4rem); font-size: clamp(3.4rem, min(4.75vw, 11svh), 6.4rem); } }
        .hero .lede { margin-top: clamp(1.4rem, 2.6vw, 2rem); font-size: clamp(1.1rem, 1.5vw, 1.25rem); }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2.25rem; }
        .hero-actions .btn, .hero-actions form { flex: 1 1 100%; }
        .hero-actions form .btn { width: 100%; }
        @media (min-width: 601px) { .hero-actions .btn, .hero-actions form { flex: 0 0 auto; } .hero-actions form .btn { width: auto; } }
        .plate { position: relative; min-width: 0; }
        .plate-img { position: relative; overflow: hidden; border-radius: 28px; aspect-ratio: 4 / 5; width: 100%; max-width: 100%; background: var(--paper-2); box-shadow: 0 40px 70px -40px rgba(21, 33, 26, 0.45); }
        @media (min-width: 600px) { .plate-img { aspect-ratio: 6 / 5; } }
        /* Desktop: never taller than the screen, so the whole photo shows in the first view */
        @media (min-width: 720px) { .plate-img { max-height: max(320px, calc(100vh - 76px - 5rem)); max-height: max(320px, calc(100svh - 76px - 5rem)); } }
        .plate-img img { width: 100%; height: 100%; object-fit: cover; object-position: 42% 55%; filter: saturate(1.04) contrast(1.03); will-change: transform; }
        .ln { display: block; overflow: hidden; padding-bottom: 0.06em; }
        .ln > span { display: block; }
        .motion .ln > span { transform: translateY(105%); animation: rise 1.1s var(--ease) forwards; }
        .motion .ln:nth-child(2) > span { animation-delay: 0.1s; }
        .motion .plate-img { clip-path: inset(100% 0 0 0 round 28px); animation: unveil 1.4s var(--ease) 0.25s forwards; }
        .motion .plate-img img { transform: scale(1.12); animation: settle 2s var(--ease) 0.25s forwards; }
        @keyframes rise { to { transform: none; } }
        @keyframes unveil { to { clip-path: inset(0 0 0 0 round 28px); } }
        @keyframes settle { to { transform: scale(1); } }

        /* Ledger of figures */
        .ledger { border-top: 1px solid var(--ink); border-bottom: 1px solid var(--rule); }
        .ledger-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 960px) { .ledger-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .fig { min-width: 0; padding: 1.6rem 1.25rem 1.6rem 0; }
        .fig + .fig { padding-left: 1.25rem; border-left: 1px solid var(--rule); }
        .fig:nth-child(3) { padding-left: 0; border-left: 0; }
        .fig:nth-child(n+3) { border-top: 1px solid var(--rule); }
        @media (min-width: 960px) { .fig:nth-child(3) { padding-left: 1.25rem; border-left: 1px solid var(--rule); } .fig:nth-child(n+3) { border-top: 0; } }
        .fig dt { font-size: 0.9rem; color: var(--ink-2); }
        .fig dd { font-family: var(--display); font-size: clamp(2.4rem, 5vw, 3.8rem); line-height: 1; letter-spacing: -0.02em; margin-top: 0.6rem; font-variant-numeric: lining-nums tabular-nums; }

        /* Sections */
        .sec { padding-block: clamp(4.5rem, 10vw, 8.5rem); }
        .sec-tight { padding-top: 0; }
        .sec-head { display: grid; gap: 1.25rem; margin-bottom: clamp(2.5rem, 6vw, 4.5rem); }
        @media (min-width: 960px) { .sec-head { grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); align-items: end; gap: 4rem; } }
        .sec-head .lede { font-size: 1.1rem; }

        /* Services */
        .svc { position: relative; isolation: isolate; border-top: 1px solid var(--ink); }
        .svc-row { position: relative; display: grid; gap: 0.75rem 3rem; padding-block: clamp(1.6rem, 3vw, 2.4rem); border-bottom: 1px solid var(--rule); text-decoration: none; }
        .svc-row::before { content: ""; position: absolute; inset: 0 -1.25rem; z-index: -1; border-radius: 8px; background: var(--paper-2); opacity: 0; transition: opacity 0.35s var(--ease); }
        .svc-row:hover::before, .svc-row:focus-visible::before { opacity: 1; }
        @media (min-width: 960px) { .svc-row { grid-template-columns: minmax(0, 5fr) minmax(0, 5fr) minmax(0, 2fr); align-items: baseline; } }
        .svc-row h3 { transition: transform 0.45s var(--ease); }
        .svc-row:hover h3, .svc-row:focus-visible h3 { transform: translateX(10px); }
        .svc-row p { color: var(--ink-2); max-width: 34em; }
        .svc-row .link { justify-self: start; }
        @media (min-width: 960px) { .svc-row .link { justify-self: end; } }
        .svc-peek { display: none; position: fixed; left: 0; top: 0; z-index: 25; width: 300px; aspect-ratio: 4 / 3; border-radius: 6px; overflow: hidden; pointer-events: none; opacity: 0; transform: scale(0.86) rotate(-2deg); transition: opacity 0.3s var(--ease), transform 0.4s var(--ease); box-shadow: 0 30px 60px -30px rgba(21, 33, 26, 0.55); }
        .svc-peek.on { opacity: 1; transform: none; }
        .svc-peek img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: var(--grade); opacity: 0; transition: opacity 0.35s var(--ease); }
        .svc-peek img.on { opacity: 1; }
        @media (hover: hover) and (min-width: 960px) { .svc-peek { display: block; } }

        /* Loan estimator */
        .est-grid { display: grid; gap: clamp(2rem, 5vw, 4rem); align-items: start; }
        @media (min-width: 960px) { .est-grid { grid-template-columns: minmax(0, 6fr) minmax(0, 5fr); } }
        .est-controls { min-width: 0; display: grid; gap: 2.2rem; }
        .est-field label, .est-field .lbl { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; font-weight: 600; font-size: 0.98rem; }
        .est-field output { font-family: var(--display); font-weight: 400; font-size: clamp(2rem, 4vw, 2.8rem); letter-spacing: -0.02em; font-variant-numeric: lining-nums tabular-nums; }
        .range { --p: 22%; -webkit-appearance: none; appearance: none; width: 100%; height: 44px; margin-top: 0.5rem; background: transparent; cursor: pointer; }
        .range::-webkit-slider-runnable-track { height: 3px; border-radius: 3px; background: linear-gradient(90deg, var(--forest) var(--p), var(--rule) var(--p)); }
        .range::-moz-range-track { height: 3px; border-radius: 3px; background: var(--rule); }
        .range::-moz-range-progress { height: 3px; border-radius: 3px; background: var(--forest); }
        .range::-webkit-slider-thumb { -webkit-appearance: none; width: 26px; height: 26px; margin-top: -11.5px; border-radius: 999px; background: var(--paper); border: 2px solid var(--forest); box-shadow: 0 4px 12px -4px rgba(21, 33, 26, 0.4); transition: transform 0.2s var(--ease); }
        .range::-moz-range-thumb { width: 22px; height: 22px; border-radius: 999px; background: var(--paper); border: 2px solid var(--forest); }
        .range:active::-webkit-slider-thumb { transform: scale(1.15); }
        .range-ends { display: flex; justify-content: space-between; font-size: 0.82rem; color: var(--ink-2); margin-top: 0.3rem; font-variant-numeric: tabular-nums; }
        .terms { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem; margin-top: 0.9rem; }
        @media (min-width: 421px) { .terms { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .terms button { min-height: 52px; border-radius: 10px; border: 1px solid var(--rule); background: transparent; font: 600 0.98rem var(--sans); color: var(--ink); cursor: pointer; transition: background-color 0.25s var(--ease), color 0.25s var(--ease), border-color 0.25s var(--ease); }
        .terms button:hover { border-color: var(--ink); }
        .terms button[aria-pressed="true"] { background: var(--forest); border-color: var(--forest); color: var(--on-dark); }
        .est-note { margin-top: -0.6rem; }
        .slip { min-width: 0; padding: clamp(1.5rem, 3vw, 2.2rem); border-radius: 10px; background: var(--paper); border: 1px solid var(--rule); box-shadow: 0 30px 60px -45px rgba(21, 33, 26, 0.5); }
        .slip-main dt { font-size: 0.95rem; color: var(--ink-2); }
        .slip-main dd { font-family: var(--display); font-size: clamp(3rem, 7vw, 4.6rem); line-height: 1; letter-spacing: -0.025em; margin-top: 0.5rem; font-variant-numeric: lining-nums tabular-nums; }
        .slip-main dd.bump { animation: bump 0.45s var(--ease); }
        @keyframes bump { 0% { color: var(--brass); } 100% { color: var(--ink); } }
        .split { display: flex; height: 10px; margin-top: 1.5rem; border-radius: 999px; overflow: hidden; background: var(--rule); }
        .split span { display: block; height: 100%; transition: flex-basis 0.5s var(--ease); }
        .split-p { background: var(--forest); flex-basis: 81%; }
        .split-i { background: var(--brass); flex-basis: 19%; }
        .split-key { display: flex; flex-wrap: wrap; gap: 0.4rem 1.25rem; margin-top: 0.7rem; font-size: 0.85rem; color: var(--ink-2); font-variant-numeric: tabular-nums; }
        .split-key span { display: inline-flex; align-items: center; gap: 0.45rem; }
        .split-key i { width: 0.6rem; height: 0.6rem; border-radius: 999px; }
        .split-key .kp { background: var(--forest); }
        .split-key .ki { background: var(--brass); }
        .slip-rows { margin-top: 1.4rem; border-top: 1px solid var(--rule); }
        .slip-rows div { display: flex; justify-content: space-between; gap: 1rem; padding-block: 0.8rem; border-bottom: 1px dashed var(--rule); font-size: 0.97rem; }
        .slip-rows dt { color: var(--ink-2); }
        .slip-rows dd { font-weight: 600; font-variant-numeric: tabular-nums; }
        .slip .btn { width: 100%; margin-top: 1.6rem; }
        .slip .small { margin-top: 1rem; }

        /* Stories */
        .stories { background: var(--paper-2); }
        .story-feature { display: grid; gap: clamp(1.75rem, 4vw, 3.5rem); align-items: end; }
        @media (min-width: 960px) { .story-feature { grid-template-columns: minmax(0, 8fr) minmax(0, 4fr); } }
        .story-pair { display: grid; gap: clamp(3rem, 6vw, 3.5rem); margin-top: clamp(3.5rem, 8vw, 6rem); }
        @media (min-width: 760px) { .story-pair { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: clamp(2rem, 4vw, 3.5rem); } }
        .story figure { min-width: 0; }
        .story .ph { overflow: hidden; border-radius: 6px; max-width: 100%; background: var(--paper); }
        .story .ph img { width: 100%; height: 100%; object-fit: cover; filter: var(--grade); transition: transform 1.2s var(--ease); }
        .story:hover .ph img { transform: scale(1.03); }
        .story-feature .ph { aspect-ratio: 16 / 10; }
        .story-pair .ph { aspect-ratio: 4 / 3; }
        .story figcaption { margin-top: 0.8rem; }
        .story-body { min-width: 0; }
        .story-pair .story-body { margin-top: 1.6rem; }
        .story-body p { margin-top: 0.9rem; color: var(--ink-2); max-width: 32em; }
        .ticks { list-style: none; display: grid; gap: 0.55rem; margin-top: 1.3rem; }
        .ticks li { display: grid; grid-template-columns: 1.1rem 1fr; gap: 0.7rem; align-items: baseline; font-size: 0.97rem; }
        .ticks li::before { content: ""; width: 0.75rem; height: 0.42rem; border-left: 1.5px solid var(--brass); border-bottom: 1.5px solid var(--brass); transform: rotate(-45deg) translate(1px, -3px); }
        .motion .wipe { clip-path: inset(0 0 100% 0); transition: clip-path 1.3s var(--ease); }
        .motion .wipe img { transform: scale(1.12); transition: transform 1.8s var(--ease); }
        .motion .wipe.in { clip-path: inset(0 0 0 0); }
        .motion .wipe.in img { transform: none; }
        .motion .story:hover .wipe.in img { transform: scale(1.03); }

        /* Motto band */
        .motto { position: relative; overflow: hidden; background: var(--forest); color: var(--on-dark); }
        .motto img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: grayscale(1) contrast(1.1); opacity: 0.22; mix-blend-mode: luminosity; }
        .motto::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(18, 50, 34, 0.92) 0%, rgba(18, 50, 34, 0.55) 100%); }
        .motto-in { position: relative; z-index: 1; padding-block: clamp(5rem, 12vw, 10rem); }
        .motto blockquote { font-family: var(--display); font-size: clamp(2.3rem, 6vw, 5.2rem); line-height: 1.04; letter-spacing: -0.02em; max-width: 14em; text-wrap: balance; }
        .motto cite { display: block; margin-top: 2rem; font-style: normal; font-size: 0.95rem; color: var(--on-dark-2); }
        .motto .w { display: inline-block; transition: opacity 0.6s var(--ease), transform 0.6s var(--ease); }
        .motion .motto .w { opacity: 0.16; transform: translateY(0.12em); }
        .motion .motto.in .w { opacity: 1; transform: none; }

        /* About */
        .about-grid { display: grid; gap: 2rem; }
        @media (min-width: 960px) { .about-grid { grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); gap: 5rem; } }
        .about-copy p { color: var(--ink-2); max-width: 36em; }
        .about-copy p + p { margin-top: 1.1rem; }
        .values { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr)); gap: 0 3rem; margin-top: clamp(3rem, 6vw, 4.5rem); border-top: 1px solid var(--ink); }
        .value { padding-block: 1.5rem; border-bottom: 1px solid var(--rule); }
        .value dt { font-family: var(--display); font-size: 1.45rem; line-height: 1.2; }
        .value dd { color: var(--ink-2); margin-top: 0.35rem; font-size: 0.98rem; }

        /* Sign-in band */
        .signin { background: var(--forest); color: var(--on-dark); }
        .signin-in { display: grid; gap: 2rem; padding-block: clamp(4rem, 9vw, 7rem); align-items: end; }
        @media (min-width: 960px) { .signin-in { grid-template-columns: minmax(0, 8fr) minmax(0, 4fr); } .signin-in > :last-child { justify-self: end; } }
        .signin p { color: var(--on-dark-2); margin-top: 1.1rem; max-width: 30em; font-size: 1.05rem; }
        .signin .btn { width: 100%; min-height: 60px; padding-inline: 2.2rem; font-size: 1.05rem; }
        @media (min-width: 601px) { .signin .btn { width: auto; } }

        /* Footer */
        .ft { padding-block: clamp(3rem, 7vw, 4.5rem) calc(2.5rem + env(safe-area-inset-bottom, 0px)); }
        .ft-grid { display: grid; gap: 2.5rem; padding-bottom: 2.5rem; border-bottom: 1px solid var(--rule); }
        @media (min-width: 760px) { .ft-grid { grid-template-columns: minmax(0, 5fr) minmax(0, 4fr) minmax(0, 3fr); } }
        .ft h2 { font-size: 0.9rem; font-weight: 600; color: var(--ink-2); }
        .phone { display: flex; align-items: center; width: fit-content; min-height: 44px; margin-top: 0.25rem; font-family: var(--display); font-size: 2.1rem; line-height: 1.1; letter-spacing: 0.01em; text-decoration: none; font-variant-numeric: lining-nums tabular-nums; }
        .ft .small { margin-top: 0.35rem; }
        .copy { margin-top: 0.9rem; min-height: 44px; padding: 0.5rem 1.1rem; font: 600 0.92rem var(--sans); color: var(--ink); background: transparent; border: 1px solid var(--rule); border-radius: 10px; cursor: pointer; transition: border-color 0.2s, background-color 0.2s; }
        .copy:hover { border-color: var(--ink); }
        .place { display: block; margin-top: 0.5rem; font-family: var(--display); font-size: 1.6rem; line-height: 1.2; }
        .ft-base { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1rem 2rem; padding-top: 1.5rem; font-size: 0.88rem; color: var(--ink-2); }
        .ft-base strong { color: var(--ink); font-weight: 600; }

        /* Fine paper grain */
        body::after { content: ""; position: fixed; inset: 0; z-index: 40; pointer-events: none; opacity: 0.05; mix-blend-mode: multiply;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.08 0 0 0 0 0.13 0 0 0 0 0.1 0 0 0 1 0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .story .ph img, .btn, .svc-row h3, .svc-row::before, .terms button, .split span { transition: none; }
            .svc-peek { display: none; }
        }
    </style>
</head>
<body>
@php
    $loginUrl = Route::has('login') ? route('login') : '#';

    // Count-up targets for the live figures: ₱4.8M, ₱350K or ₱9,500.
    if (!empty($stats)) {
        $loaned = (float) $stats['loaned'];
        if ($loaned >= 1000000) {
            $loanedCount = round($loaned / 1000000, 1); $loanedSuffix = 'M'; $loanedDecimals = 1;
        } elseif ($loaned >= 1000) {
            $loanedCount = round($loaned / 1000, 1); $loanedSuffix = 'K'; $loanedDecimals = 1;
        } else {
            $loanedCount = round($loaned); $loanedSuffix = ''; $loanedDecimals = 0;
        }
        $loanedLabel = '₱' . rtrim(rtrim(number_format($loanedCount, $loanedDecimals), '0'), '.') . $loanedSuffix;
        if ($loanedDecimals && fmod($loanedCount, 1.0) == 0.0) { $loanedDecimals = 0; }
    }
@endphp

<header class="hd" id="hd">
    <div class="wrap hd-row">
        <a href="#top" class="mark" aria-label="Centrala Farmers Marketing Cooperative home">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" aria-hidden="true"><path d="M16 29V10"/><path d="M16 15c-3.2-.4-5.6-2.6-6.2-6 3.2.4 5.6 2.6 6.2 6Z"/><path d="M16 19c3.2-.4 5.6-2.6 6.2-6-3.2.4-5.6 2.6-6.2 6Z"/><path d="M16 23c-3.2-.4-5.6-2.6-6.2-6 3.2.4 5.6 2.6 6.2 6Z"/><path d="M16 10c-.9-2.2-.5-4.6 1.2-6.6"/></svg>
            <span><b>Centrala</b><small>Farmers Marketing Cooperative</small></span>
        </a>
        <nav class="nav" aria-label="Main">
            <a href="#top" aria-current="true">Home</a>
            <a href="#about">About</a>
            <a href="#services">Services</a>
            <a href="#contact">Contact</a>
        </nav>
        <div class="hd-actions">
            @auth
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-solid">Login</button>
                </form>
            @else
                <a href="{{ $loginUrl }}" class="btn btn-solid">Login</a>
            @endauth
            <button type="button" class="menu-btn" id="menuOpen" aria-label="Open menu" aria-expanded="false" aria-controls="menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 8h16M4 16h16"/></svg>
            </button>
        </div>
    </div>
    <span class="progress" id="progress" aria-hidden="true"></span>
</header>

<div class="menu" id="menu" role="dialog" aria-modal="true" aria-label="Menu" hidden>
    <div class="menu-top">
        <a href="#top" class="mark" data-close>
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" aria-hidden="true"><path d="M16 29V10"/><path d="M16 15c-3.2-.4-5.6-2.6-6.2-6 3.2.4 5.6 2.6 6.2 6Z"/><path d="M16 19c3.2-.4 5.6-2.6 6.2-6-3.2.4-5.6 2.6-6.2 6Z"/><path d="M16 23c-3.2-.4-5.6-2.6-6.2-6 3.2.4 5.6 2.6 6.2 6Z"/><path d="M16 10c-.9-2.2-.5-4.6 1.2-6.6"/></svg>
            <span><b>Centrala</b><small>Farmers Marketing Cooperative</small></span>
        </a>
        <button type="button" class="menu-btn" id="menuClose" aria-label="Close menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>
    <nav aria-label="Mobile">
        <a href="#top" data-close>Home</a>
        <a href="#about" data-close>About</a>
        <a href="#services" data-close>Services</a>
        <a href="#contact" data-close>Contact</a>
    </nav>
    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-solid">Login</button>
        </form>
    @else
        <a href="{{ $loginUrl }}" class="btn btn-solid">Login</a>
    @endauth
</div>

<main>
    <section class="hero" id="top">
        <div class="wrap">
            <div class="hero-grid">
                <div class="hero-copy">
                    <h1 class="h-xl hero-title">
                        <span class="ln"><span>Growing together,</span></span>
                        <span class="ln"><span>managing better.</span></span>
                    </h1>
                    <p class="lede">The unified cooperative management system for Centrala farmers. Apply for loans, track your CBU, book machinery and keep your membership up to date, all in one place.</p>
                    <div class="hero-actions">
                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-solid">Get started</button>
                            </form>
                        @else
                            <a href="{{ $loginUrl }}" class="btn btn-solid">Get started</a>
                        @endauth
                        <a href="#services" class="btn btn-line">Learn more</a>
                    </div>
                </div>
                <figure class="plate">
                    <div class="plate-img">
                        <img src="{{ asset('images/landing/hero-harvest.jpg') }}" width="1600" height="1042" fetchpriority="high"
                             alt="Cooperative farmers loading sacks of harvested rice onto a combine harvester in a Centrala rice field">
                    </div>
                </figure>
            </div>
        </div>
    </section>

    @if (!empty($stats))
        <section aria-label="Cooperative at a glance">
            <div class="wrap">
                <dl class="ledger">
                    <div class="ledger-grid">
                        <div class="fig"><dt>Registered farmers</dt><dd data-count="{{ $stats['farmers'] }}">{{ number_format($stats['farmers']) }}</dd></div>
                        <div class="fig"><dt>Active members</dt><dd data-count="{{ $stats['members'] }}">{{ number_format($stats['members']) }}</dd></div>
                        <div class="fig"><dt>Available machines</dt><dd data-count="{{ $stats['machines'] }}">{{ number_format($stats['machines']) }}</dd></div>
                        <div class="fig"><dt>Total loaned</dt><dd data-count="{{ $loanedCount }}" data-decimals="{{ $loanedDecimals }}" data-prefix="₱" data-suffix="{{ $loanedSuffix }}">{{ $loanedLabel }}</dd></div>
                    </div>
                </dl>
            </div>
        </section>
    @endif

    <section class="sec" id="services">
        <div class="wrap">
            <div class="sec-head">
                <h2 class="h-l">Everything a member needs, in one system</h2>
                <p class="lede">Three services, one login. Everything you do with the cooperative is recorded and easy to look up.</p>
            </div>
            <div class="svc" id="svc">
                <div class="svc-peek" id="svcPeek" aria-hidden="true">
                    <img src="{{ asset('images/landing/member-seminar.jpg') }}" alt="" loading="lazy" decoding="async">
                    <img src="{{ asset('images/landing/harvest-hauling.jpg') }}" alt="" loading="lazy" decoding="async">
                    <img src="{{ asset('images/landing/rcef-harvester.jpg') }}" alt="" loading="lazy" decoding="async">
                </div>
                <a href="{{ $loginUrl }}" class="svc-row">
                    <h3 class="h-m">Member registration</h3>
                    <p>Register as a cooperative member and keep your farmer profile and records organized and secure.</p>
                    <span class="link">Register</span>
                </a>
                <a href="{{ $loginUrl }}" class="svc-row">
                    <h3 class="h-m">Farmer loans</h3>
                    <p>Apply for loans, follow your repayment schedule, view your CBU and pay on time, all from one place.</p>
                    <span class="link">Apply for a loan</span>
                </a>
                <a href="{{ $loginUrl }}" class="svc-row">
                    <h3 class="h-m">Machine rental</h3>
                    <p>Check tractor and harvester availability, request a schedule, and see your bookings on the calendar.</p>
                    <span class="link">Book a machine</span>
                </a>
            </div>
        </div>
    </section>

    <section class="sec sec-tight" id="estimate" aria-labelledby="estTitle">
        <div class="wrap">
            <div class="sec-head">
                <h2 class="h-l" id="estTitle">See what a loan would cost you</h2>
                <p class="lede">Move the slider and pick a term. The estimate uses the cooperative's usual 2% monthly rate.</p>
            </div>
            <div class="est-grid">
                <div class="est-controls">
                    <div class="est-field">
                        <label for="estAmount">Loan amount <output id="estAmountOut" for="estAmount">₱15,000</output></label>
                        <input class="range" type="range" id="estAmount" min="5000" max="50000" step="1000" value="15000">
                        <div class="range-ends"><span>₱5,000</span><span>₱50,000</span></div>
                    </div>
                    <div class="est-field">
                        <span class="lbl" id="termLabel">Repayment term</span>
                        <div class="terms" role="group" aria-labelledby="termLabel">
                            <button type="button" data-term="6" aria-pressed="false">6 months</button>
                            <button type="button" data-term="12" aria-pressed="true">12 months</button>
                            <button type="button" data-term="18" aria-pressed="false">18 months</button>
                            <button type="button" data-term="24" aria-pressed="false">24 months</button>
                        </div>
                    </div>
                    <p class="small est-note">How much you can borrow depends on your CBU balance.</p>
                </div>
                <div class="slip" aria-live="polite">
                    <dl class="slip-main"><dt>Monthly payment</dt><dd id="estMonthly">₱1,550.00</dd></dl>
                    <div class="split" aria-hidden="true"><span class="split-p" id="splitP"></span><span class="split-i" id="splitI"></span></div>
                    <p class="split-key"><span><i class="kp"></i>Principal <b id="keyP">81%</b></span><span><i class="ki"></i>Interest <b id="keyI">19%</b></span></p>
                    <dl class="slip-rows">
                        <div><dt>Principal per month</dt><dd id="estPrincipal">₱1,250.00</dd></div>
                        <div><dt>Interest per month (2%)</dt><dd id="estInterest">₱300.00</dd></div>
                        <div><dt>Total to repay</dt><dd id="estTotal">₱18,600.00</dd></div>
                    </dl>
                    <a href="{{ $loginUrl }}" class="btn btn-solid">Sign in to apply</a>
                    <p class="small">This is an estimate. Your final terms are set when your loan is approved.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="sec stories" id="stories" aria-labelledby="storiesTitle">
        <div class="wrap">
            <div class="sec-head">
                <h2 class="h-l" id="storiesTitle">From the field to the market, together.</h2>
                <p class="lede">A look at what Centrala members do every season, and how the cooperative helps along the way.</p>
            </div>

            <article class="story story-feature">
                <figure>
                    <div class="ph wipe"><img src="{{ asset('images/landing/harvest-hauling.jpg') }}" width="1400" height="1050" loading="lazy" decoding="async"
                         alt="Farmers loading sacks of harvested palay onto the cooperative truck while a carabao cart brings more from the field"></div>
                    <figcaption class="caption">Harvest season. Palay arrives by hand and by carabao cart.</figcaption>
                </figure>
                <div class="story-body">
                    <h3 class="h-m">From field to market</h3>
                    <p>When the harvest comes in, members bring their palay straight from the field to the cooperative truck, by hand and by carabao cart, to be sold together at a fair price.</p>
                    <ul class="ticks">
                        <li>Harvest payments recorded for every member</li>
                        <li>Fair pricing through collective marketing</li>
                    </ul>
                </div>
            </article>

            <div class="story-pair">
                <article class="story">
                    <figure>
                        <div class="ph wipe"><img src="{{ asset('images/landing/member-seminar.jpg') }}" width="1200" height="900" loading="lazy" decoding="async"
                             alt="Cooperative members attending a seminar on cooperativism beside a Kubota combine harvester"></div>
                        <figcaption class="caption">Member seminar on cooperativism.</figcaption>
                    </figure>
                    <div class="story-body">
                        <h3 class="h-m">Learning and growing together</h3>
                        <p>Members meet regularly for seminars on cooperativism, farm practices and how to use the cooperative's services, including this system.</p>
                        <ul class="ticks">
                            <li>Membership registration and approval online</li>
                            <li>Announcements sent to every member</li>
                        </ul>
                    </div>
                </article>
                <article class="story">
                    <figure>
                        <div class="ph wipe"><img src="{{ asset('images/landing/rcef-harvester.jpg') }}" width="1200" height="675" loading="lazy" decoding="async"
                             alt="A new Yanmar AW70V combine harvester on a trailer pulled by a tractor, from the RCEF Mechanization Program"></div>
                        <figcaption class="caption">A new combine harvester from the RCEF Mechanization Program.</figcaption>
                    </figure>
                    <div class="story-body">
                        <h3 class="h-m">Modern machines for every member</h3>
                        <p>Through the RCEF Mechanization Program, the cooperative now has combine harvesters and tractors that members can book for their own fields.</p>
                        <ul class="ticks">
                            <li>See which machines are available</li>
                            <li>Request a schedule and track it on the calendar</li>
                        </ul>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="motto" id="motto" aria-label="Cooperative motto">
        <img src="{{ asset('images/landing/harvest-hauling.jpg') }}" alt="" loading="lazy" decoding="async">
        <div class="wrap motto-in">
            <blockquote>&ldquo;Stronger together than any one of us alone.&rdquo;</blockquote>
            <cite>Centrala Farmers Marketing Cooperative</cite>
        </div>
    </section>

    <section class="sec" id="about">
        <div class="wrap">
            <div class="about-grid">
                <h2 class="h-l">A cooperative that grows with its farmers</h2>
                <div class="about-copy">
                    <p class="lede">Centrala Farmers Marketing Cooperative brings farmers in Centrala, Surallah together to share resources, access fair financing and market their harvest.</p>
                    <p>This system puts the cooperative's everyday work online: membership records, loan applications and payments, capital build-up, machinery schedules and announcements, so members and staff spend less time on paperwork.</p>
                </div>
            </div>
            <dl class="values">
                <div class="value"><dt>Easy to use</dt><dd>Simple screens designed for every member</dd></div>
                <div class="value"><dt>Transparent</dt><dd>See your loans, payments and CBU anytime</dd></div>
                <div class="value"><dt>Community focused</dt><dd>Built around cooperative values</dd></div>
                <div class="value"><dt>Secure</dt><dd>Your records are protected and private</dd></div>
            </dl>
        </div>
    </section>

    <section class="signin" aria-labelledby="signinTitle">
        <div class="wrap signin-in">
            <div>
                <h2 class="h-l" id="signinTitle">Ready to get started?</h2>
                <p>Sign in to manage your membership, loans and machine schedules.</p>
            </div>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-light">Sign in</button>
                </form>
            @else
                <a href="{{ $loginUrl }}" class="btn btn-light">Sign in</a>
            @endauth
        </div>
    </section>
</main>

<footer class="ft" id="contact">
    <div class="wrap">
        <div class="ft-grid">
            <div>
                <h2>Contact us</h2>
                <a class="phone" id="contactPhone" href="tel:+639483141215">0948 314 1215</a>
                <p class="small">For inquiries only.</p>
                <button type="button" class="copy" id="copyContactPhone" data-phone="09483141215">Copy number</button>
            </div>
            <div>
                <h2>Visit us</h2>
                <span class="place">Centrala, Surallah</span>
                <p class="small">South Cotabato</p>
            </div>
            <div>
                <h2>Centrala Farmers Marketing Cooperative</h2>
                <p class="small">Supporting our farmers. Building a stronger tomorrow.</p>
            </div>
        </div>
        <div class="ft-base">
            <span>&copy; {{ date('Y') }} CFMC. All rights reserved.</span>
            <span>System developed by <strong>Neil Delfin Apsay</strong></span>
        </div>
    </div>
</footer>

<script>
(function () {
    var root = document.documentElement;
    var reduce = !root.classList.contains('motion');
    var hasIO = 'IntersectionObserver' in window;
    var fine = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var peso = function (n, d) { return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: d, maximumFractionDigits: d }); };

    // Header shadow + reading progress
    var hd = document.getElementById('hd'), bar = document.getElementById('progress'), ticking = false;
    function onScroll() {
        ticking = false;
        var y = window.scrollY, max = document.documentElement.scrollHeight - window.innerHeight;
        hd.classList.toggle('is-scrolled', y > 8);
        bar.style.transform = 'scaleX(' + (max > 0 ? Math.min(1, y / max) : 0).toFixed(4) + ')';
    }
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
    onScroll();

    // Mobile menu: full-screen panel with focus handling, Escape to close,
    // and the page underneath locked so it doesn't scroll behind the panel.
    var menu = document.getElementById('menu'), menuOpen = document.getElementById('menuOpen'), menuClose = document.getElementById('menuClose');
    function setMenu(open) {
        menu.hidden = !open;
        menuOpen.setAttribute('aria-expanded', String(open));
        document.body.style.overflow = open ? 'hidden' : '';
        (open ? menuClose : menuOpen).focus();
    }
    menuOpen.addEventListener('click', function () { setMenu(true); });
    menuClose.addEventListener('click', function () { setMenu(false); });
    menu.querySelectorAll('[data-close]').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !menu.hidden) setMenu(false); });
    window.matchMedia('(min-width: 960px)').addEventListener('change', function (mq) { if (mq.matches && !menu.hidden) setMenu(false); });

    // Active nav link
    if (hasIO) {
        var links = [].slice.call(document.querySelectorAll('.nav a'));
        var nio = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                links.forEach(function (a) {
                    if (a.getAttribute('href') === '#' + en.target.id) a.setAttribute('aria-current', 'true');
                    else a.removeAttribute('aria-current');
                });
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        ['top', 'services', 'about', 'contact'].forEach(function (id) { var el = document.getElementById(id); if (el) nio.observe(el); });
    }

    // Hero photo drifts gently with the pointer (after the opening animation)
    var heroImg = document.querySelector('.plate-img img'), hero = document.querySelector('.hero');
    if (!reduce && fine && heroImg) {
        setTimeout(function () {
            heroImg.style.animation = 'none';
            heroImg.style.transform = 'scale(1.06)';
            heroImg.style.transition = 'transform 0.9s cubic-bezier(0.2, 0.7, 0.2, 1)';
            hero.addEventListener('pointermove', function (e) {
                var r = hero.getBoundingClientRect();
                var dx = (e.clientX - r.left) / r.width - 0.5, dy = (e.clientY - r.top) / r.height - 0.5;
                heroImg.style.transform = 'scale(1.06) translate3d(' + (dx * -16).toFixed(1) + 'px,' + (dy * -12).toFixed(1) + 'px,0)';
            });
            hero.addEventListener('pointerleave', function () { heroImg.style.transform = 'scale(1.06)'; });
        }, 2300);
    }

    // Service rows: a photo preview follows the cursor
    var peek = document.getElementById('svcPeek');
    if (!reduce && fine && peek) {
        var rows = [].slice.call(document.querySelectorAll('.svc-row')), imgs = [].slice.call(peek.querySelectorAll('img'));
        var px = 0, py = 0, tx = 0, ty = 0, raf = null;
        function follow() {
            px += (tx - px) * 0.18; py += (ty - py) * 0.18;
            peek.style.left = px.toFixed(1) + 'px'; peek.style.top = py.toFixed(1) + 'px';
            raf = (Math.abs(tx - px) > 0.5 || Math.abs(ty - py) > 0.5) ? requestAnimationFrame(follow) : null;
        }
        rows.forEach(function (row, i) {
            row.addEventListener('pointerenter', function (e) {
                imgs.forEach(function (im, j) { im.classList.toggle('on', j === i); });
                if (!peek.classList.contains('on')) { px = tx = e.clientX + 28; py = ty = e.clientY - 120; }
                peek.classList.add('on');
            });
            row.addEventListener('pointermove', function (e) {
                tx = e.clientX + 28; ty = e.clientY - 120;
                if (!raf) raf = requestAnimationFrame(follow);
            });
        });
        document.getElementById('svc').addEventListener('pointerleave', function () { peek.classList.remove('on'); });
    }

    // Live figures count up once
    var ledger = document.querySelector('.ledger');
    if (!reduce && hasIO && ledger) {
        var nums = [].slice.call(ledger.querySelectorAll('[data-count]'));
        var show = function (el, v) {
            var d = parseInt(el.dataset.decimals || '0', 10);
            el.textContent = (el.dataset.prefix || '') + (d ? v.toFixed(d) : Math.round(v).toLocaleString('en-US')) + (el.dataset.suffix || '');
        };
        var lio = new IntersectionObserver(function (entries) {
            if (!entries[0].isIntersecting) return;
            lio.disconnect();
            nums.forEach(function (el) {
                var end = parseFloat(el.dataset.count), t0 = null;
                requestAnimationFrame(function step(t) {
                    if (!t0) t0 = t;
                    var p = Math.min((t - t0) / 1600, 1);
                    show(el, end * (1 - Math.pow(1 - p, 4)));
                    if (p < 1) requestAnimationFrame(step);
                });
            });
        }, { threshold: 0.5 });
        lio.observe(ledger);
    }

    // Story photos unveil; the motto lights up word by word
    var motto = document.getElementById('motto'), quote = motto.querySelector('blockquote');
    quote.innerHTML = quote.textContent.trim().split(/\s+/).map(function (w, i) {
        return '<span class="w" style="transition-delay:' + (i * 70) + 'ms">' + w + '</span>';
    }).join(' ');
    if (!reduce && hasIO) {
        var sio = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                (en.target.querySelector('.wipe') || en.target).classList.add('in');
                sio.unobserve(en.target);
            });
        }, { threshold: 0.2 });
        // Watch each photo's unclipped frame: Chrome counts a fully clipped
        // element as never visible, so watching the photo itself never fires.
        document.querySelectorAll('.wipe').forEach(function (el) { sio.observe(el.parentElement); });
        sio.observe(motto);
    } else {
        root.classList.remove('motion');
        motto.classList.add('in');
    }

    // Loan estimator: monthly = principal x 2% + principal / term (the cooperative's formula)
    var amt = document.getElementById('estAmount'), amtOut = document.getElementById('estAmountOut');
    var outM = document.getElementById('estMonthly'), outP = document.getElementById('estPrincipal');
    var outI = document.getElementById('estInterest'), outT = document.getElementById('estTotal');
    var termBtns = [].slice.call(document.querySelectorAll('.terms button'));
    var term = 12, shown = 1550, tween = null;
    function calc(bump) {
        var p = parseInt(amt.value, 10);
        amt.style.setProperty('--p', ((p - amt.min) / (amt.max - amt.min) * 100) + '%');
        amtOut.textContent = peso(p, 0);
        var interest = Math.round(p * 0.02 * 100) / 100;
        var principal = Math.round(p / term * 100) / 100;
        var monthly = Math.round((interest + principal) * 100) / 100;
        var total = Math.round(monthly * term * 100) / 100;
        outP.textContent = peso(principal, 2);
        outI.textContent = peso(interest, 2);
        outT.textContent = peso(total, 2);
        var pp = Math.round(p / total * 100);
        document.getElementById('splitP').style.flexBasis = pp + '%';
        document.getElementById('splitI').style.flexBasis = (100 - pp) + '%';
        document.getElementById('keyP').textContent = pp + '%';
        document.getElementById('keyI').textContent = (100 - pp) + '%';
        if (reduce) { outM.textContent = peso(monthly, 2); shown = monthly; return; }
        var from = shown, t0 = null;
        cancelAnimationFrame(tween);
        tween = requestAnimationFrame(function step(t) {
            if (!t0) t0 = t;
            var k = Math.min((t - t0) / 380, 1), e = 1 - Math.pow(1 - k, 3);
            shown = from + (monthly - from) * e;
            outM.textContent = peso(shown, 2);
            if (k < 1) tween = requestAnimationFrame(step); else shown = monthly;
        });
        if (bump) { outM.classList.remove('bump'); void outM.offsetWidth; outM.classList.add('bump'); }
    }
    amt.addEventListener('input', function () { calc(false); });
    termBtns.forEach(function (b) {
        b.addEventListener('click', function () {
            term = parseInt(b.dataset.term, 10);
            termBtns.forEach(function (o) { o.setAttribute('aria-pressed', String(o === b)); });
            calc(true);
        });
    });
    calc(false);

    // Copy the inquiry number; if the browser refuses, select it for copying by hand
    var copyBtn = document.getElementById('copyContactPhone');
    copyBtn.addEventListener('click', function () {
        function pick() {
            var r = document.createRange(); r.selectNodeContents(document.getElementById('contactPhone'));
            var s = window.getSelection(); s.removeAllRanges(); s.addRange(r);
        }
        if (!navigator.clipboard) { pick(); return; }
        navigator.clipboard.writeText(copyBtn.dataset.phone).then(function () {
            copyBtn.textContent = 'Copied';
            setTimeout(function () { copyBtn.textContent = 'Copy number'; }, 1600);
        }, pick);
    });
})();
</script>
</body>
</html>
