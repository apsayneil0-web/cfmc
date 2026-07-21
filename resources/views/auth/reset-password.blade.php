<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CFMC</title>
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
        .input-wrap { position: relative; }
        input[type="password"] {
            width: 100%; padding: 0.8rem 0.9rem; border: 1.5px solid var(--border); border-radius: 0.75rem;
            font-size: 0.95rem; font-family: inherit; background-color: #fafafa;
            transition: border-color 150ms var(--ease), box-shadow 150ms var(--ease);
        }
        input[type="password"]:focus {
            outline: none; border-color: var(--brand-success); background-color: #fff;
            box-shadow: 0 0 0 4px rgba(31, 111, 92, 0.12);
        }
        .error { color: var(--brand-danger); font-size: 0.85rem; margin-top: 0.4rem; }
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
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h1 class="title">Set a New Password</h1>
                <p class="subtitle">Choose a strong password for your account.</p>
            </div>

            @if ($errors->any())
                <div class="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <div>
                        <strong>Couldn't reset password</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.reset') }}">
                @csrf
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required autofocus placeholder="••••••••" minlength="8">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" minlength="8">
                </div>

                <button type="submit" class="btn btn-primary">
                    Reset Password
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
