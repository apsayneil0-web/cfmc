<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CFMC - Cooperative Management System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&family=outfit:600,700,800" rel="stylesheet" />
    <style>
        :root {
            --brand-primary: #2f8f78;
            --brand-primary-dark: #1f6f5c;
            --brand-success: #1f6f5c;
            --brand-success-dark: #123f34;
            --brand-success-light: #e4f1ee;
            --brand-amber: #d97706;
            --brand-purple: #7c3aed;
            --brand-pink: #db2777;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --surface: #ffffff;
            --surface-muted: #f8fafc;
            --border: #e6e9ef;
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;
            --shadow-sm: 0 1px 2px 0 rgba(15, 23, 42, 0.05);
            --shadow-md: 0 4px 16px -4px rgba(15, 23, 42, 0.1), 0 2px 6px -2px rgba(15, 23, 42, 0.06);
            --shadow-lg: 0 24px 48px -16px rgba(15, 23, 42, 0.18), 0 8px 16px -8px rgba(15, 23, 42, 0.08);
            --shadow-glow: 0 12px 32px -8px rgba(31, 111, 92, 0.35);
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: var(--surface);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            line-height: 1.6;
            overflow-x: hidden;
        }
        a { color: inherit; text-decoration: none; }
        h1, h2, h3 {
            font-family: 'Outfit', 'Instrument Sans', sans-serif;
            letter-spacing: -0.03em;
            line-height: 1.15;
        }
        button { font-family: inherit; }

        a:focus-visible, button:focus-visible {
            outline: 2px solid var(--brand-success);
            outline-offset: 3px;
            border-radius: 4px;
        }

        /* ---------- Layout ---------- */
        .max-w-6xl { max-width: 72rem; margin-inline: auto; }
        .max-w-4xl { max-width: 56rem; margin-inline: auto; }
        .max-w-2xl { max-width: 42rem; margin-inline: auto; }
        .px-4 { padding-inline: 1.25rem; }
        .section { padding-block: 6.5rem; position: relative; }
        @media (max-width: 767px) { .section { padding-block: 4rem; } }

        /* ---------- Header ---------- */
        header {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(16px) saturate(160%);
            -webkit-backdrop-filter: blur(16px) saturate(160%);
            border-bottom: 1px solid transparent;
            transition: border-color 260ms var(--ease), box-shadow 260ms var(--ease);
        }
        header.is-scrolled {
            border-bottom-color: var(--border);
            box-shadow: var(--shadow-sm);
        }
        nav.navbar {
            max-width: 72rem;
            margin-inline: auto;
            padding: 0.9rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--text-primary);
        }
        .brand-mark {
            display: grid;
            place-items: center;
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.8rem;
            background: linear-gradient(135deg, var(--brand-success), #175b4b 55%, var(--brand-primary));
            box-shadow: var(--shadow-glow);
            color: #fff;
        }
        .brand-mark svg { width: 1.3rem; height: 1.3rem; }
        .brand .accent { color: var(--brand-success); }

        .nav-links { display: none; align-items: center; gap: 2.25rem; }
        .nav-links a {
            position: relative;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-secondary);
            padding-block: 0.25rem;
            transition: color 200ms var(--ease);
        }
        .nav-links a::after {
            content: '';
            position: absolute;
            left: 0; bottom: -2px;
            width: 0; height: 2px;
            background: var(--brand-success);
            transition: width 220ms var(--ease);
            border-radius: 2px;
        }
        .nav-links a:hover { color: var(--text-primary); }
        .nav-links a:hover::after { width: 100%; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            transition: transform 200ms var(--ease), box-shadow 200ms var(--ease), background-color 200ms var(--ease), color 200ms var(--ease);
            white-space: nowrap;
        }
        .btn:active { transform: translateY(0) scale(0.98); }
        .btn-sm { padding: 0.55rem 1.25rem; }
        .btn-lg { padding: 0.85rem 1.9rem; font-size: 1.02rem; }

        .btn-outline {
            background: transparent;
            color: var(--brand-success);
            border: 1.5px solid var(--brand-success);
        }
        .btn-outline:hover { background: var(--brand-success-light); transform: translateY(-2px); }

        .btn-solid {
            background: linear-gradient(135deg, var(--brand-success), var(--brand-success-dark));
            color: #fff;
            box-shadow: var(--shadow-glow);
        }
        .btn-solid:hover { transform: translateY(-2px); box-shadow: 0 16px 36px -8px rgba(31, 111, 92, 0.45); }

        .btn-ghost {
            background: rgba(15, 23, 42, 0.04);
            color: var(--text-primary);
        }
        .btn-ghost:hover { background: rgba(15, 23, 42, 0.08); transform: translateY(-2px); }

        .btn-danger {
            background: #fef2f2;
            color: #dc2626;
        }
        .btn-danger:hover { background: #fee2e2; transform: translateY(-2px); }

        .btn-white {
            background: #fff;
            color: var(--brand-success-dark);
        }
        .btn-white:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }

        /* ---------- Hero ---------- */
        .hero {
            position: relative;
            padding-top: 9.5rem;
            padding-bottom: 6rem;
            overflow: hidden;
            background: linear-gradient(180deg, #f2f9f7 0%, #ffffff 65%);
        }
        .hero-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: 0.55;
            pointer-events: none;
            z-index: 0;
        }
        .hero-blob.b1 { width: 26rem; height: 26rem; top: -8rem; left: -6rem; background: radial-gradient(circle, #a7e8d9, transparent 70%); }
        .hero-blob.b2 { width: 22rem; height: 22rem; top: -4rem; right: -8rem; background: radial-gradient(circle, #fdecc0, transparent 70%); }
        .hero-blob.b3 { width: 18rem; height: 18rem; bottom: -6rem; left: 40%; background: radial-gradient(circle, #d7ede7, transparent 70%); opacity: 0.4; }

        .hero-inner { position: relative; z-index: 1; text-align: center; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border-radius: 999px;
            background: var(--surface);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--brand-success-dark);
            margin-bottom: 1.75rem;
        }
        .eyebrow .dot {
            width: 0.45rem; height: 0.45rem;
            border-radius: 50%;
            background: var(--brand-success);
            box-shadow: 0 0 0 3px var(--brand-success-light);
        }

        .hero h1 {
            font-size: 3.1rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
        }
        .hero h1 .gradient-text {
            background: linear-gradient(120deg, var(--brand-success), #175b4b 45%, var(--brand-primary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        @media (min-width: 768px) { .hero h1 { font-size: 4.25rem; } }

        .hero p.lead {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 40rem;
            margin: 0 auto 2.5rem;
        }

        .hero-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            justify-content: center;
            margin-bottom: 3.5rem;
        }
        @media (min-width: 640px) { .hero-actions { flex-direction: row; } }

        .trust-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.6rem 2rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }
        .trust-row span { display: inline-flex; align-items: center; gap: 0.4rem; }
        .trust-row svg { width: 1rem; height: 1rem; color: var(--brand-success); flex-shrink: 0; }

        /* ---------- Section headings ---------- */
        .section-eyebrow {
            display: block;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--brand-success);
            margin-bottom: 0.75rem;
        }
        .section-title {
            font-size: 2.35rem;
            font-weight: 800;
            text-align: center;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .section-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 1.08rem;
            max-width: 36rem;
            margin: 0 auto 3.75rem;
        }

        /* ---------- Feature cards ---------- */
        .grid { display: grid; gap: 1.75rem; }
        .grid-features { grid-template-columns: 1fr; }
        @media (min-width: 768px) { .grid-features { grid-template-columns: repeat(3, 1fr); } }

        .feature-card {
            padding: 2rem;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            background: var(--surface);
            transition: transform 260ms var(--ease), box-shadow 260ms var(--ease), border-color 260ms var(--ease);
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }
        .feature-icon {
            display: grid;
            place-items: center;
            width: 3.1rem;
            height: 3.1rem;
            border-radius: 0.9rem;
            margin-bottom: 1.4rem;
            color: #fff;
        }
        .feature-icon svg { width: 1.5rem; height: 1.5rem; }
        .feature-icon.c1 { background: linear-gradient(135deg, #2f8f78, #1f6f5c); }
        .feature-icon.c2 { background: linear-gradient(135deg, #1f6f5c, #123f34); }
        .feature-icon.c3 { background: linear-gradient(135deg, #4fb49a, #2f8f78); }
        .feature-icon.c4 { background: linear-gradient(135deg, #175b4b, #0d2e27); }
        .feature-icon.c5 { background: linear-gradient(135deg, #3fa78d, #1f6f5c); }
        .feature-icon.c6 { background: linear-gradient(135deg, #1a5c4f, #123f34); }

        .feature-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 0.6rem; color: var(--text-primary); }
        .feature-card p { color: var(--text-secondary); font-size: 0.97rem; }

        /* ---------- Stats ---------- */
        .stats-section {
            position: relative;
            background: linear-gradient(120deg, #123f34, #1f6f5c 45%, #2f8f78);
            overflow: hidden;
        }
        .stats-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.12), transparent 40%),
                               radial-gradient(circle at 80% 60%, rgba(255,255,255,0.1), transparent 45%);
            pointer-events: none;
        }
        .grid-stats { grid-template-columns: repeat(2, 1fr); position: relative; z-index: 1; }
        @media (min-width: 768px) { .grid-stats { grid-template-columns: repeat(4, 1fr); } }
        .stat-tile {
            text-align: center;
            padding: 1.75rem 1rem;
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(6px);
        }
        .stat-tile .num { font-size: 2.5rem; font-weight: 800; color: #fff; font-family: 'Outfit', sans-serif; }
        .stat-tile .label { color: rgba(255,255,255,0.85); font-size: 0.92rem; margin-top: 0.35rem; }

        /* ---------- About ---------- */
        .about-section { background: var(--surface-muted); }
        .grid-about { grid-template-columns: 1fr; align-items: center; }
        @media (min-width: 768px) { .grid-about { grid-template-columns: 1fr 1fr; gap: 3.5rem; } }
        .about-copy h2 { font-size: 2.1rem; font-weight: 800; margin-bottom: 1.25rem; }
        .about-copy p { color: var(--text-secondary); font-size: 1.05rem; margin-bottom: 1rem; }

        .about-card {
            background: var(--surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            padding: 2rem;
            margin-top: 2.5rem;
        }
        @media (min-width: 768px) { .about-card { margin-top: 0; } }
        .checklist-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding-block: 0.9rem;
            border-bottom: 1px solid var(--border);
        }
        .checklist-item:last-child { border-bottom: none; }
        .check-badge {
            flex-shrink: 0;
            display: grid;
            place-items: center;
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 50%;
            background: var(--brand-success-light);
            color: var(--brand-success-dark);
        }
        .check-badge svg { width: 1.1rem; height: 1.1rem; }
        .checklist-item h3 { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.15rem; }
        .checklist-item p { color: var(--text-secondary); font-size: 0.92rem; }

        /* ---------- CTA ---------- */
        .cta-section { background: var(--surface); }
        .cta-panel {
            position: relative;
            max-width: 64rem;
            margin-inline: auto;
            padding: 4rem 2rem;
            border-radius: var(--radius-xl);
            background: linear-gradient(135deg, #1f6f5c, #123f34 55%, #0d2e27);
            text-align: center;
            color: #fff;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        .cta-panel::before, .cta-panel::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(50px);
        }
        .cta-panel::before { width: 16rem; height: 16rem; background: rgba(255,255,255,0.15); top: -6rem; right: -4rem; }
        .cta-panel::after { width: 14rem; height: 14rem; background: rgba(255,255,255,0.1); bottom: -6rem; left: -4rem; }
        .cta-panel h2 { font-size: 2.1rem; font-weight: 800; margin-bottom: 0.9rem; position: relative; }
        .cta-panel p { font-size: 1.08rem; opacity: 0.92; margin-bottom: 2rem; position: relative; }
        .cta-panel .btn { position: relative; }

        /* ---------- Footer ---------- */
        footer { background: var(--text-primary); color: #cbd5e1; padding-block: 4rem 2rem; }
        .footer-grid { grid-template-columns: 1.4fr repeat(3, 1fr); gap: 2.5rem; margin-bottom: 3rem; }
        @media (max-width: 767px) { .footer-grid { grid-template-columns: 1fr 1fr; } }
        .footer-brand .brand { color: #fff; margin-bottom: 0.75rem; }
        .footer-brand p { font-size: 0.92rem; color: #94a3b8; max-width: 20rem; }
        footer h4 { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #fff; margin-bottom: 1rem; }
        footer ul { list-style: none; display: flex; flex-direction: column; gap: 0.65rem; }
        footer ul a { font-size: 0.92rem; color: #94a3b8; transition: color 200ms var(--ease); }
        footer ul a:hover { color: #5cc9ae; }
        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
        }
        @media (min-width: 640px) { .footer-bottom { flex-direction: row; justify-content: space-between; text-align: left; } }

        /* ---------- Reveal-on-scroll ---------- */
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity 640ms var(--ease), transform 640ms var(--ease); }
        .reveal.in-view { opacity: 1; transform: translateY(0); }

        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hero-inner { animation: fade-in-up 640ms var(--ease); }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .hero-inner, .reveal { animation: none !important; transition: none !important; opacity: 1 !important; transform: none !important; }
            .btn:hover, .feature-card:hover { transform: none !important; }
        }

        @media (min-width: 768px) { .nav-links { display: flex; } }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <header id="siteHeader">
        <nav class="navbar">
            <div class="brand">
                <span class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c4.97 0 9-4.03 9-9-4.97 0-9 4.03-9 9Z"/><path d="M12 22c-4.97 0-9-4.03-9-9 4.97 0 9 4.03 9 9Z"/><path d="M12 22V8"/><path d="M12 8c0-3.31 2.69-6 6-6-.5 3-2.5 5.5-6 6Z"/><path d="M12 8C12 4.69 9.31 2 6 2c.5 3 2.5 5.5 6 6Z"/></svg>
                </span>
                CFMC
            </div>

            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#about">About</a>
                <a href="#stats">Stats</a>
            </div>

            @if (Route::has('login'))
                <div style="display:flex; align-items:center; gap:1rem;">
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Log Out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Log In</a>
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-blob b1"></div>
        <div class="hero-blob b2"></div>
        <div class="hero-blob b3"></div>

        <div class="max-w-4xl px-4 hero-inner">
            <span class="eyebrow"><span class="dot"></span> Built for cooperative growth</span>

            <h1>Welcome to <span class="gradient-text">CFMC</span></h1>
            <p class="lead">
                Empower your cooperative with modern management solutions. Streamline operations,
                connect members, and grow together.
            </p>

            <div class="hero-actions">
                @if (Route::has('login'))
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg">Log Out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-solid btn-lg">
                            Sign In
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    @endauth
                @endif
                <a href="#features" class="btn btn-ghost btn-lg">Explore Features</a>
            </div>

            <div class="trust-row">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg> Member management</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg> Financial transparency</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg> Built for every device</span>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
        <div class="max-w-6xl px-4">
            <span class="section-eyebrow reveal">Capabilities</span>
            <h2 class="section-title reveal">Key Features</h2>
            <p class="section-subtitle reveal">Everything your cooperative needs to operate smoothly, in one connected system.</p>

            <div class="grid grid-features">
                <div class="feature-card reveal">
                    <div class="feature-icon c1">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3>Member Management</h3>
                    <p>Easily manage member profiles, roles, and permissions. Keep your cooperative organized and connected.</p>
                </div>

                <div class="feature-card reveal">
                    <div class="feature-icon c2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                    </div>
                    <h3>Analytics Dashboard</h3>
                    <p>Get real-time insights with powerful analytics. Make data-driven decisions for your cooperative.</p>
                </div>

                <div class="feature-card reveal">
                    <div class="feature-icon c3">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v10"/><path d="M15 9.5c0-1.38-1.34-2.5-3-2.5s-3 1.12-3 2.5S10.34 12 12 12s3 1.12 3 2.5-1.34 2.5-3 2.5-3-1.12-3-2.5"/></svg>
                    </div>
                    <h3>Financial Tracking</h3>
                    <p>Track transactions, manage budgets, and maintain financial transparency across your organization.</p>
                </div>

                <div class="feature-card reveal">
                    <div class="feature-icon c4">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                    </div>
                    <h3>Event Planning</h3>
                    <p>Schedule meetings, organize events, and keep members informed with integrated event management.</p>
                </div>

                <div class="feature-card reveal">
                    <div class="feature-icon c5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access your cooperative management tools on any device. Work anytime, anywhere.</p>
                </div>

                <div class="feature-card reveal">
                    <div class="feature-icon c6">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                    </div>
                    <h3>Secure &amp; Reliable</h3>
                    <p>Enterprise-grade security ensures your cooperative data is always protected and backed up.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section id="stats" class="section stats-section">
        <div class="max-w-6xl px-4">
            <div class="grid grid-stats reveal">
                <div class="stat-tile">
                    <div class="num">100+</div>
                    <p class="label">Active Cooperatives</p>
                </div>
                <div class="stat-tile">
                    <div class="num">10K+</div>
                    <p class="label">Total Members</p>
                </div>
                <div class="stat-tile">
                    <div class="num">99.9%</div>
                    <p class="label">Uptime</p>
                </div>
                <div class="stat-tile">
                    <div class="num">24/7</div>
                    <p class="label">Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section about-section">
        <div class="max-w-6xl px-4">
            <div class="grid grid-about">
                <div class="about-copy reveal">
                    <span class="section-eyebrow" style="text-align:left;">Why CFMC</span>
                    <h2>About CFMC</h2>
                    <p>CFMC is a comprehensive cooperative management system designed to help organizations like yours succeed in the digital age.</p>
                    <p>We provide tools for member management, financial tracking, event planning, and much more. Our mission is to empower cooperatives with technology that brings members together and drives growth.</p>
                    <p>Whether you're just starting out or scaling an established cooperative, CFMC has the features you need to thrive.</p>
                </div>
                <div class="about-card reveal">
                    <div class="checklist-item">
                        <span class="check-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg></span>
                        <div>
                            <h3>Easy to Use</h3>
                            <p>Intuitive interface designed for all skill levels</p>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <span class="check-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg></span>
                        <div>
                            <h3>Scalable</h3>
                            <p>Grows with your cooperative's needs</p>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <span class="check-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg></span>
                        <div>
                            <h3>Community Focused</h3>
                            <p>Built with cooperative values in mind</p>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <span class="check-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg></span>
                        <div>
                            <h3>Dedicated Support</h3>
                            <p>Expert assistance when you need it</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section cta-section">
        <div class="px-4">
            <div class="cta-panel reveal">
                <h2>Ready to Transform Your Cooperative?</h2>
                <p>Join CFMC and start managing your cooperative today.</p>

                @if (Route::has('login'))
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-white btn-lg">Log Out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-white btn-lg">Sign In</a>
                    @endauth
                @endif
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="max-w-6xl px-4">
            <div class="grid footer-grid">
                <div class="footer-brand">
                    <div class="brand">
                        <span class="brand-mark">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c4.97 0 9-4.03 9-9-4.97 0-9 4.03-9 9Z"/><path d="M12 22c-4.97 0-9-4.03-9-9 4.97 0 9 4.03 9 9Z"/><path d="M12 22V8"/><path d="M12 8c0-3.31 2.69-6 6-6-.5 3-2.5 5.5-6 6Z"/><path d="M12 8C12 4.69 9.31 2 6 2c.5 3 2.5 5.5 6 6Z"/></svg>
                        </span>
                        CFMC
                    </div>
                    <p>Modern management software for cooperatives who want to move faster, together.</p>
                </div>
                <div>
                    <h4>Product</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#stats">Stats</a></li>
                        <li><a href="#about">About</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Terms</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 CFMC - Cooperative Management System. All rights reserved.</p>
                <p>Made for cooperatives that grow together.</p>
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
        })();
    </script>
</body>
</html>
