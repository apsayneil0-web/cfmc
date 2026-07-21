<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - CFMC</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&family=outfit:600,700,800" rel="stylesheet" />
    <style>
        :root {
            --brand-success: #1f6f5c; --brand-success-dark: #123f34; --brand-primary: #2f8f78;
            --brand-danger: #dc2626; --text-primary: #0f172a; --text-secondary: #475569;
            --text-muted: #94a3b8; --border: #e5e7eb; --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(180deg, #f2f9f7 0%, #eef4f2 100%);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; color: var(--text-primary); padding: 1.5rem;
        }
        h1, h2 { font-family: 'Outfit', 'Instrument Sans', sans-serif; letter-spacing: -0.02em; }
        .container { max-width: 440px; width: 100%; }
        .card {
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px) saturate(160%);
            border-radius: 1.5rem; box-shadow: 0 24px 48px -16px rgba(15, 23, 42, 0.18), 0 8px 16px -8px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.6); padding: 2.75rem 2.5rem;
        }
        .back-link { display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 1.75rem; color: var(--brand-success-dark); text-decoration: none; font-size: 0.9rem; font-weight: 600; }
        .back-link svg { width: 1rem; height: 1rem; }
        .back-link:hover { color: var(--brand-success); }
        .header { text-align: center; margin-bottom: 2rem; }
        .brand-mark {
            display: grid; place-items: center; width: 4rem; height: 4rem; margin: 0 auto 1.25rem;
            border-radius: 1.1rem; background: linear-gradient(135deg, var(--brand-success), #175b4b 55%, var(--brand-primary));
            box-shadow: 0 12px 28px -8px rgba(31, 111, 92, 0.45); color: #fff;
        }
        .brand-mark svg { width: 2rem; height: 2rem; }
        .title { font-size: 1.6rem; font-weight: 800; margin-bottom: 0.4rem; }
        .subtitle { color: var(--text-secondary); font-size: 0.92rem; }
        .form-group { margin-bottom: 1.4rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        input[type="text"] {
            width: 100%; padding: 0.9rem; border: 1.5px solid var(--border); border-radius: 0.75rem;
            font-size: 1.4rem; font-weight: 700; letter-spacing: 0.4em; text-align: center; font-family: inherit;
            background-color: #fafafa; transition: border-color 150ms var(--ease), box-shadow 150ms var(--ease);
        }
        input[type="text"]:focus {
            outline: none; border-color: var(--brand-success); background-color: #fff;
            box-shadow: 0 0 0 4px rgba(31, 111, 92, 0.12);
        }
        .error { color: var(--brand-danger); font-size: 0.85rem; margin-top: 0.4rem; text-align: center; }
        .btn {
            width: 100%; padding: 0.9rem; border: none; border-radius: 0.75rem; font-size: 0.98rem;
            font-weight: 700; cursor: pointer; font-family: inherit; display: flex; align-items: center;
            justify-content: center; gap: 0.5rem; transition: transform 180ms var(--ease), filter 180ms var(--ease);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-success), var(--brand-success-dark));
            color: white; box-shadow: 0 12px 24px -8px rgba(31, 111, 92, 0.5);
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.04); }
        .alert {
            display: flex; align-items: flex-start; gap: 0.65rem; padding: 0.9rem 1rem; border-radius: 0.75rem;
            margin-bottom: 1.5rem; font-size: 0.9rem; background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca;
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
            <a href="{{ route('password.request') }}" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Back
            </a>

            <div class="header">
                <div class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M12 16v2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                </div>
                <h1 class="title">Enter Reset Code</h1>
                <p class="subtitle">We emailed a 6-digit code. It expires in 10 minutes.</p>
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

            <form method="POST" action="{{ route('password.otp.verify') }}">
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

            <div class="hint">Didn't get a code? <a href="{{ route('password.request') }}">Send a new one</a></div>
        </div>
    </div>
</body>
</html>
