<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CFMC</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&family=fraunces:500,500i,600" rel="stylesheet" />
    @vite('resources/css/palette.css')
    @include('partials.auth-motion')
    <style>
        :root {
            --brand-success: var(--palette-field);
            --brand-success-dark: var(--palette-field-deep);
            --brand-success-light: var(--palette-sprout);
            --brand-primary: var(--palette-field-light);
            --brand-danger: var(--palette-clay);
            --text-primary: var(--palette-ink);
            --text-secondary: var(--palette-text-secondary);
            --text-muted: var(--palette-muted);
            --border: var(--palette-stone);
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(180deg, var(--palette-paper) 0%, var(--palette-surface-muted) 100%);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; color: var(--text-primary); padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2 { font-family: 'Fraunces', Georgia, 'Times New Roman', serif; letter-spacing: -0.02em; }
        .container { max-width: 440px; width: 100%; }
        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px) saturate(160%);
            border-radius: 1.5rem;
            box-shadow: 0 24px 48px -16px color-mix(in srgb, var(--palette-ink) 18%, transparent), 0 8px 16px -8px color-mix(in srgb, var(--palette-ink) 8%, transparent);
            border: 1px solid rgba(255, 255, 255, 0.6);
            padding: 2.75rem 2.5rem;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.75rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: color 200ms var(--ease);
        }
        .back-link-icon {
            display: grid;
            place-items: center;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--palette-surface);
            color: var(--text-primary);
            transition: background-color 200ms var(--ease), border-color 200ms var(--ease), color 200ms var(--ease);
        }
        .back-link svg { width: 0.95rem; height: 0.95rem; transition: transform 200ms var(--ease); }
        .back-link:hover { color: var(--text-primary); }
        .back-link:hover .back-link-icon {
            background: var(--palette-sprout);
            border-color: var(--palette-sprout-border);
            color: var(--brand-success);
        }
        .back-link:hover svg { transform: translateX(-2px); }
        .back-link:focus-visible { outline: none; }
        .back-link:focus-visible .back-link-icon { box-shadow: 0 0 0 3px var(--palette-sprout-border); }
        .header { text-align: center; margin-bottom: 2rem; }
        .brand-mark {
            display: grid; place-items: center; width: 4rem; height: 4rem; margin: 0 auto 1.25rem;
            border-radius: 1.1rem;
            background: linear-gradient(135deg, var(--brand-success), var(--palette-field-hover) 55%, var(--brand-primary));
            box-shadow: 0 12px 28px -8px color-mix(in srgb, var(--palette-field) 45%, transparent); color: #fff;
        }
        .brand-mark svg { width: 2rem; height: 2rem; }
        .title { font-size: 1.85rem; font-weight: 600; line-height: 1.1; margin-bottom: 0.5rem; }
        .subtitle { color: var(--text-secondary); font-size: 0.92rem; }
        .form-group { margin-bottom: 1.4rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        .input-wrap { position: relative; }
        .input-wrap > svg {
            position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%);
            width: 1.1rem; height: 1.1rem; color: var(--text-muted); pointer-events: none;
        }
        input[type="text"] {
            width: 100%; padding: 0.8rem 0.9rem 0.8rem 2.6rem; border: 1.5px solid var(--border);
            border-radius: 0.75rem; font-size: 0.95rem; font-family: inherit; background-color: var(--palette-surface-muted);
            transition: border-color 150ms var(--ease), box-shadow 150ms var(--ease);
        }
        input[type="text"]:focus {
            outline: none; border-color: var(--brand-success); background-color: var(--palette-surface);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--palette-field) 12%, transparent);
        }
        .error { color: var(--brand-danger); font-size: 0.85rem; margin-top: 0.4rem; }
        .btn {
            width: 100%; padding: 0.9rem; border: none; border-radius: 0.75rem; font-size: 0.98rem;
            font-weight: 700; cursor: pointer; font-family: inherit; display: flex; align-items: center;
            justify-content: center; gap: 0.5rem; transition: transform 180ms var(--ease), filter 180ms var(--ease);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-success), var(--brand-success-dark));
            color: white; box-shadow: 0 12px 24px -8px color-mix(in srgb, var(--palette-field) 50%, transparent);
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.04); }
        .alert {
            display: flex; align-items: flex-start; gap: 0.65rem; padding: 0.9rem 1rem; border-radius: 0.75rem;
            margin-bottom: 1.5rem; font-size: 0.9rem; background-color: var(--palette-clay-tint); color: var(--palette-clay-text);
            border: 1px solid var(--palette-clay-border);
        }
        .alert svg { width: 1.15rem; height: 1.15rem; flex-shrink: 0; margin-top: 0.1rem; color: var(--brand-danger); }
        .alert ul { margin-top: 0.35rem; margin-left: 1.1rem; }
        .hint { text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-muted); }
        .hint a { color: var(--brand-success-dark); font-weight: 600; text-decoration: none; }
        .hint a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <a href="{{ route('login') }}" class="back-link" aria-label="Back to Login">
                <span class="back-link-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </span>
                Back to Login
            </a>

            <div class="header">
                <div class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                </div>
                <h1 class="title">Forgot Password</h1>
                <p class="subtitle">Enter your email. Staff accounts only.</p>
            </div>

            @if ($errors->any())
                <div class="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <div>
                        <strong>Something went wrong</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.otp.send') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email on File</label>
                    <div class="input-wrap">
                        <input type="text" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter the email linked to your account">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Send Reset Code
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>

            <div class="hint">Remembered it? <a href="{{ route('login') }}">Back to login</a></div>
        </div>
    </div>
</body>
</html>
