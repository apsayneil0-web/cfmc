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
        input[type="password"], input[type="text"].password-input {
            width: 100%; padding: 0.8rem 2.6rem 0.8rem 0.9rem; border: 1.5px solid var(--border); border-radius: 0.75rem;
            font-size: 0.95rem; font-family: inherit; background-color: #fafafa;
            transition: border-color 150ms var(--ease), box-shadow 150ms var(--ease);
        }
        input[type="password"]:focus, input[type="text"].password-input:focus {
            outline: none; border-color: var(--brand-success); background-color: #fff;
            box-shadow: 0 0 0 4px rgba(31, 111, 92, 0.12);
        }
        .toggle-password {
            position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: var(--text-muted);
            padding: 0.4rem; display: grid; place-items: center; border-radius: 0.5rem;
            transition: color 150ms var(--ease), background-color 150ms var(--ease);
        }
        .toggle-password:hover { color: var(--text-primary); background: rgba(15, 23, 42, 0.05); }
        .toggle-password svg { width: 1.1rem; height: 1.1rem; }
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
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" required autofocus placeholder="••••••••" minlength="8">
                        <button type="button" class="toggle-password" data-target="password" aria-label="Show password">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" minlength="8">
                        <button type="button" class="toggle-password" data-target="password_confirmation" aria-label="Show password">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Reset Password
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        var eyeOpen = '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
        var eyeClosed = '<path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a13.16 13.16 0 0 1-2.16 3.19"/><path d="M6.61 6.61A13.53 13.53 0 0 0 2 11s3.5 7 10 7a9.1 9.1 0 0 0 4.24-1.02"/><path d="m2 2 20 20"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>';

        document.querySelectorAll('.toggle-password').forEach(function (toggle) {
            var input = document.getElementById(toggle.dataset.target);
            var icon = toggle.querySelector('.eye-icon');

            toggle.addEventListener('click', function () {
                var isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                input.classList.toggle('password-input', isPassword);
                icon.innerHTML = isPassword ? eyeClosed : eyeOpen;
                toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        });
    </script>
</body>
</html>
