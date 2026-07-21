<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CFMC</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&family=outfit:600,700,800" rel="stylesheet" />
    <style>
        :root {
            --brand-success: #1f6f5c;
            --brand-success-dark: #123f34;
            --brand-success-light: #e4f1ee;
            --brand-primary: #2f8f78;
            --brand-danger: #dc2626;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e5e7eb;
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(180deg, #f2f9f7 0%, #eef4f2 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-primary);
            padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
        }
        h1, h2 { font-family: 'Outfit', 'Instrument Sans', sans-serif; letter-spacing: -0.02em; }

        .bg-blob { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.55; pointer-events: none; z-index: 0; }
        .bg-blob.b1 { width: 28rem; height: 28rem; top: -9rem; left: -8rem; background: radial-gradient(circle, #a7e8d9, transparent 70%); }
        .bg-blob.b2 { width: 24rem; height: 24rem; bottom: -10rem; right: -8rem; background: radial-gradient(circle, #fdecc0, transparent 70%); }
        .bg-blob.b3 { width: 16rem; height: 16rem; top: 40%; right: 12%; background: radial-gradient(circle, #d7ede7, transparent 70%); opacity: 0.35; }

        .container { max-width: 440px; width: 100%; position: relative; z-index: 1; }
        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            border-radius: 1.5rem;
            box-shadow: 0 24px 48px -16px rgba(15, 23, 42, 0.18), 0 8px 16px -8px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.6);
            padding: 2.75rem 2.5rem;
            animation: card-in 460ms var(--ease);
        }
        @keyframes card-in {
            from { opacity: 0; transform: translateY(14px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @media (prefers-reduced-motion: reduce) { .card { animation: none; } }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 1.75rem;
            color: var(--brand-success-dark);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: gap 200ms var(--ease), color 200ms var(--ease);
        }
        .back-link svg { width: 1rem; height: 1rem; transition: transform 200ms var(--ease); }
        .back-link:hover { color: var(--brand-success); gap: 0.6rem; }
        .back-link:hover svg { transform: translateX(-2px); }

        .header { text-align: center; margin-bottom: 2rem; }
        .brand-mark {
            display: grid;
            place-items: center;
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.25rem;
            border-radius: 1.1rem;
            background: linear-gradient(135deg, var(--brand-success), #175b4b 55%, var(--brand-primary));
            box-shadow: 0 12px 28px -8px rgba(31, 111, 92, 0.45);
            color: #fff;
        }
        .brand-mark svg { width: 2rem; height: 2rem; }
        .title { font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.4rem; }
        .subtitle { color: var(--text-secondary); font-size: 0.95rem; }

        .form-group { margin-bottom: 1.4rem; }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .input-wrap { position: relative; }
        .input-wrap > svg {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.1rem;
            height: 1.1rem;
            color: var(--text-muted);
            pointer-events: none;
            transition: color 150ms var(--ease);
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem 0.9rem 0.8rem 2.6rem;
            border: 1.5px solid var(--border);
            border-radius: 0.75rem;
            font-size: 0.95rem;
            font-family: inherit;
            background-color: #fafafa;
            transition: border-color 150ms var(--ease), box-shadow 150ms var(--ease), background-color 150ms var(--ease);
        }
        input[type="text"]:hover, input[type="password"]:hover { border-color: #d1d5db; }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--brand-success);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(31, 111, 92, 0.12);
        }
        input[type="text"]:focus ~ svg, input[type="password"]:focus ~ svg { color: var(--brand-success); }

        .toggle-password {
            position: absolute;
            right: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 0.4rem;
            display: grid;
            place-items: center;
            border-radius: 0.5rem;
            transition: color 150ms var(--ease), background-color 150ms var(--ease);
        }
        .toggle-password:hover { color: var(--text-primary); background: rgba(15, 23, 42, 0.05); }
        .toggle-password svg { width: 1.1rem; height: 1.1rem; }

        .error { color: var(--brand-danger); font-size: 0.85rem; margin-top: 0.4rem; }

        .checkbox-group { display: flex; align-items: center; gap: 0.55rem; font-size: 0.92rem; cursor: pointer; color: var(--text-secondary); font-weight: 500; }
        input[type="checkbox"] { width: 1.05rem; height: 1.05rem; cursor: pointer; accent-color: var(--brand-success); }

        .forgot-password { text-align: right; margin-bottom: 1.75rem; }
        .forgot-password a {
            color: var(--brand-success-dark);
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 600;
            transition: color 150ms var(--ease);
        }
        .forgot-password a:hover { color: var(--brand-success); text-decoration: underline; }
        .forgot-password .description { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem; font-weight: 400; }

        .btn {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: transform 180ms var(--ease), box-shadow 180ms var(--ease), filter 180ms var(--ease);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-success), var(--brand-success-dark));
            color: white;
            box-shadow: 0 12px 24px -8px rgba(31, 111, 92, 0.5);
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.04); }
        .btn-primary:focus-visible { outline: none; box-shadow: 0 0 0 4px rgba(31, 111, 92, 0.3); }
        .btn-primary:active { transform: translateY(0) scale(0.98); }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            padding: 0.9rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            animation: card-in 300ms var(--ease);
        }
        .alert svg { width: 1.15rem; height: 1.15rem; flex-shrink: 0; margin-top: 0.1rem; color: var(--brand-danger); }
        .alert ul { margin-top: 0.35rem; margin-left: 1.1rem; }
    </style>
</head>
<body>
    <div class="bg-blob b1"></div>
    <div class="bg-blob b2"></div>
    <div class="bg-blob b3"></div>

    <div class="container">
        <div class="card">
            <a href="{{ route('welcome') }}" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Back to Home
            </a>

            <div class="header">
                <div class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c4.97 0 9-4.03 9-9-4.97 0-9 4.03-9 9Z"/><path d="M12 22c-4.97 0-9-4.03-9-9 4.97 0 9 4.03 9 9Z"/><path d="M12 22V8"/><path d="M12 8c0-3.31 2.69-6 6-6-.5 3-2.5 5.5-6 6Z"/><path d="M12 8C12 4.69 9.31 2 6 2c.5 3 2.5 5.5 6 6Z"/></svg>
                </div>
                <h1 class="title">Welcome Back</h1>
                <p class="subtitle">Sign in to your CFMC cooperative account</p>
            </div>

            @if (session('status'))
                <div class="alert" style="background-color: var(--brand-success-light); color: var(--brand-success-dark); border-color: #bfe3d9;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--brand-success);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <div>
                        <strong>Login Failed</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Username / Email -->
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-wrap">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            placeholder="Enter username or email"
                        >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    @error('username')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            style="padding-right: 2.6rem;"
                        >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary">
                    Login
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>

                <!-- Forgot Password Link -->
                <div class="forgot-password">
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                    <div class="description">Admin and Manager only</div>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var toggle = document.getElementById('togglePassword');
            var input = document.getElementById('password');
            var eyeIcon = document.getElementById('eyeIcon');
            var eyeOpen = '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
            var eyeClosed = '<path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a13.16 13.16 0 0 1-2.16 3.19"/><path d="M6.61 6.61A13.53 13.53 0 0 0 2 11s3.5 7 10 7a9.1 9.1 0 0 0 4.24-1.02"/><path d="m2 2 20 20"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>';

            toggle.addEventListener('click', function () {
                var isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                eyeIcon.innerHTML = isPassword ? eyeClosed : eyeOpen;
                toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        })();
    </script>
</body>
</html>
