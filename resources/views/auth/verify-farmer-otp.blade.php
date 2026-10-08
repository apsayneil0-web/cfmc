<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - CFMC</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&family=fraunces:500,500i,600" rel="stylesheet" />
    @vite('resources/css/palette.css')
    @include('partials.auth-motion')
    <style>
        :root {
            --brand-success: var(--palette-field); --brand-success-dark: var(--palette-field-deep); --brand-primary: var(--palette-field-light);
            --brand-danger: var(--palette-clay); --text-primary: var(--palette-ink); --text-secondary: var(--palette-text-secondary);
            --text-muted: var(--palette-muted); --border: var(--palette-stone); --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(180deg, var(--palette-paper) 0%, var(--palette-surface-muted) 100%);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; color: var(--text-primary); padding: 1.5rem;
        }
        h1, h2 { font-family: 'Fraunces', Georgia, 'Times New Roman', serif; letter-spacing: -0.02em; }
        .container { max-width: 440px; width: 100%; }
        .card {
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px) saturate(160%);
            border-radius: 1.5rem; box-shadow: 0 24px 48px -16px color-mix(in srgb, var(--palette-ink) 18%, transparent), 0 8px 16px -8px color-mix(in srgb, var(--palette-ink) 8%, transparent);
            border: 1px solid rgba(255, 255, 255, 0.6); padding: 2.75rem 2.5rem;
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
            border-radius: 1.1rem; background: linear-gradient(135deg, var(--brand-success), var(--palette-field-hover) 55%, var(--brand-primary));
            box-shadow: 0 12px 28px -8px color-mix(in srgb, var(--palette-field) 45%, transparent); color: #fff;
        }
        .brand-mark svg { width: 2rem; height: 2rem; }
        .title { font-size: 1.85rem; font-weight: 600; line-height: 1.1; margin-bottom: 0.5rem; }
        .subtitle { color: var(--text-secondary); font-size: 0.92rem; }
        .form-group { margin-bottom: 1.4rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        input[type="text"] {
            width: 100%; padding: 0.9rem; border: 1.5px solid var(--border); border-radius: 0.75rem;
            font-size: 1.4rem; font-weight: 700; letter-spacing: 0.4em; text-align: center; font-family: inherit;
            background-color: var(--palette-surface-muted); transition: border-color 150ms var(--ease), box-shadow 150ms var(--ease);
        }
        input[type="text"]:focus {
            outline: none; border-color: var(--brand-success); background-color: var(--palette-surface);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--palette-field) 12%, transparent);
        }
        .error { color: var(--brand-danger); font-size: 0.85rem; margin-top: 0.4rem; text-align: center; }
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
            margin-bottom: 1.5rem; font-size: 0.9rem; background-color: var(--palette-clay-tint); color: var(--palette-clay-text); border: 1px solid var(--palette-clay-border);
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
            <a href="{{ route('login') }}" class="back-link" aria-label="Back">
                <span class="back-link-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </span>
                Back
            </a>

            <div class="header">
                <div class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <h1 class="title">Enter Verification Code</h1>
                <p class="subtitle">We texted a 6-digit code to your mobile number on file. It expires in 10 minutes.</p>
            </div>

            @if ($errors->any())
                <div class="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <div>
                        <strong>Verification failed</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('farmer.otp.verify') }}">
                @csrf
                <div class="form-group">
                    <label for="otp">6-Digit Code</label>
                    <input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus placeholder="000000">
                </div>

                <button type="submit" class="btn btn-primary">
                    Verify Code
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>

            <div class="hint">Didn't get a code? <a href="{{ route('login') }}">Log in again to resend</a></div>
        </div>
    </div>
</body>
</html>
