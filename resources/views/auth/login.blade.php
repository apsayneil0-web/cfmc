<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CFMC</title>
    @include('partials.fonts')
    <link href="https://fonts.bunny.net/css?family=libre-caslon-display:400&family=libre-caslon-text:400&family=hanken-grotesk:400,500,600&display=swap" rel="stylesheet">
    @vite('resources/css/palette.css')
    @include('partials.auth-motion')
    <style>
        /* Premium look (UI only): same layout and markup as before, restyled to
           match the landing page. Light mode only. */
        :root {
            --brand-success: #123222;
            --brand-success-dark: #123222;
            --brand-success-light: var(--palette-sprout);
            --brand-primary: var(--palette-field-light);
            --brand-danger: var(--palette-clay);
            --text-primary: #15211a;
            --text-secondary: #4b574f;
            --text-muted: #4b574f;
            --border: #c9d1c6;
            --brass: #a07d34;
            --card-bg: rgba(244, 247, 241, 0.9);
            --display: 'Libre Caslon Display', 'Libre Caslon Text', Georgia, serif;
            --serif: 'Libre Caslon Text', Georgia, serif;
            --sans: 'Hanken Grotesk', 'Public Sans', ui-sans-serif, system-ui, sans-serif;
            --ease: cubic-bezier(0.2, 0.7, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--sans);
            background: #1d3b25;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            min-height: 100svh;
            color: var(--text-primary);
            padding: clamp(1rem, 4vh, 2.5rem) max(16px, env(safe-area-inset-right, 0px)) clamp(1rem, 4vh, 2.5rem) max(16px, env(safe-area-inset-left, 0px));
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        h1, h2 { font-family: var(--display); font-weight: 400; letter-spacing: -0.025em; }

        /* The same rice-field photo, sharper and lightly blurred, with a soft
           vignette so the eye lands on the card. */
        .bg-photo {
            position: fixed;
            inset: -12px;
            background: url('{{ asset('images/login-rice-field-hd.jpg') }}') 50% 45% / cover no-repeat;
            filter: blur(1.5px) saturate(0.95);
            transform: scale(1.04);
            pointer-events: none;
            z-index: 0;
            animation: bg-settle 2.2s var(--ease) both;
        }
        @keyframes bg-settle { from { transform: scale(1.12); } to { transform: scale(1.04); } }
        .bg-wash {
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 60% 70% at 50% 50%, rgba(18, 40, 26, 0.18) 0%, rgba(18, 40, 26, 0.55) 70%, rgba(12, 26, 17, 0.75) 100%);
            pointer-events: none;
            z-index: 0;
        }

        .container { max-width: 30rem; width: 100%; position: relative; z-index: 1; }
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(18px) saturate(1.2);
            -webkit-backdrop-filter: blur(18px) saturate(1.2);
            border-radius: 28px;
            border: 0;
            box-shadow: 0 50px 100px -40px rgba(0, 0, 0, 0.6), inset 0 0 0 1px rgba(255, 255, 255, 0.55), 0 0 0 1px rgba(18, 50, 34, 0.08);
            padding: clamp(1.6rem, 4vw, 2.75rem) clamp(1.4rem, 4vw, 3rem) clamp(1.6rem, 3.5vw, 2.4rem);
            animation: card-in 0.9s var(--ease) 0.1s both;
        }
        @keyframes card-in {
            from { opacity: 0; transform: translateY(20px) scale(0.985); }
            to { opacity: 1; transform: none; }
        }
        @media (prefers-reduced-motion: reduce) { .card, .bg-photo { animation: none; } }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            min-height: 44px;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 200ms var(--ease);
        }
        .back-link-icon {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 0;
            background: #fff;
            color: var(--text-primary);
            box-shadow: 0 2px 8px -3px rgba(18, 50, 34, 0.3);
            transition: transform 200ms var(--ease);
        }
        .back-link svg { width: 16px; height: 16px; }
        .back-link:hover { color: var(--text-primary); }
        .back-link:hover .back-link-icon { transform: translateX(-2px); }
        .back-link:focus-visible { outline: none; }
        .back-link:focus-visible .back-link-icon { box-shadow: 0 0 0 3px rgba(160, 125, 52, 0.55); }

        .header { display: grid; justify-items: center; text-align: center; margin-top: clamp(0.5rem, 2vh, 1.25rem); margin-bottom: clamp(1.5rem, 3.5vh, 2rem); }
        .brand-mark {
            display: grid;
            place-items: center;
            width: 60px;
            height: 60px;
            margin: 0 auto 1.1rem;
            border-radius: 999px;
            background: #fff;
            color: var(--brand-success);
            box-shadow: 0 8px 20px -10px rgba(18, 50, 34, 0.45), 0 0 0 6px rgba(255, 255, 255, 0.5);
        }
        .brand-mark svg { width: 30px; height: 30px; }
        .title { font-size: clamp(2.3rem, 7vw, 2.9rem); font-weight: 400; line-height: 1; color: var(--text-primary); margin-bottom: 0.6rem; }
        .subtitle { color: var(--text-secondary); font-family: var(--serif); font-size: 1.02rem; }

        .form-group { margin-bottom: 1.15rem; }
        label {
            display: block;
            margin-bottom: 0.45rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.93rem;
        }

        .input-wrap { position: relative; }
        .input-wrap > svg {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            pointer-events: none;
            transition: color 0.2s var(--ease);
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            min-height: 54px;
            padding: 0.8rem 1rem 0.8rem 2.85rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: max(16px, 1rem);
            font-family: inherit;
            color: var(--text-primary);
            background-color: #fff;
            transition: border-color 0.2s var(--ease), box-shadow 0.2s var(--ease);
        }
        #password { padding-right: 3.4rem !important; }
        input::placeholder { color: #8b958d; }
        input[type="text"]:hover, input[type="password"]:hover { border-color: #aab5ab; }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--brand-success);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(18, 50, 34, 0.12);
        }
        input[type="text"]:focus ~ svg, input[type="password"]:focus ~ svg { color: var(--brand-success); }

        .toggle-password {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 0;
            display: grid;
            place-items: center;
            border-radius: 9px;
            transition: color 0.2s var(--ease), background-color 0.2s var(--ease);
        }
        .toggle-password:hover { color: var(--text-primary); background: #eef1ea; }
        .toggle-password svg { width: 19px; height: 19px; }

        .error { color: var(--brand-danger); font-size: 0.86rem; margin-top: 0.4rem; }

        .checkbox-group { display: inline-flex; align-items: center; gap: 0.6rem; min-height: 44px; margin-bottom: 0; font-size: 0.96rem; font-weight: 400; color: var(--text-primary); cursor: pointer; }
        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            margin: 0;
            border-radius: 6px;
            border: 1.5px solid #9aa59c;
            background: #fff;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: background-color 0.15s, border-color 0.15s;
        }
        input[type="checkbox"]::after { content: ""; width: 10px; height: 6px; border-left: 2px solid #fff; border-bottom: 2px solid #fff; transform: rotate(-45deg) translate(1px, -1px); opacity: 0; }
        input[type="checkbox"]:checked { background: var(--brand-success); border-color: var(--brand-success); }
        input[type="checkbox"]:checked::after { opacity: 1; }
        input[type="checkbox"]:focus-visible { outline: 2px solid var(--brass); outline-offset: 2px; }

        .forgot-password { text-align: right; margin-top: 1rem; margin-bottom: 0; }
        .forgot-password a {
            color: var(--text-primary);
            text-decoration: underline;
            text-decoration-color: var(--brass);
            text-decoration-thickness: 1.5px;
            text-underline-offset: 4px;
            font-size: 0.93rem;
            font-weight: 600;
            transition: text-decoration-color 0.2s var(--ease);
        }
        .forgot-password a:hover { color: var(--text-primary); text-decoration-color: currentColor; }
        .forgot-password .description { font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem; font-weight: 400; }

        .btn {
            width: 100%;
            min-height: 56px;
            padding: 0.9rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1.02rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            transition: transform 0.15s var(--ease), box-shadow 0.25s var(--ease), filter 0.25s var(--ease);
        }
        .btn-primary {
            background: linear-gradient(180deg, #1c4430 0%, #123222 100%);
            color: #eef1ea;
            box-shadow: 0 14px 28px -16px rgba(18, 50, 34, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }
        .btn-primary svg { transition: transform 0.25s var(--ease); }
        .btn-primary:hover { filter: brightness(1.08); box-shadow: 0 18px 32px -16px rgba(18, 50, 34, 0.85), inset 0 1px 0 rgba(255, 255, 255, 0.12); }
        .btn-primary:hover svg { transform: translateX(3px); }
        .btn-primary:focus-visible { outline: 2px solid var(--brass); outline-offset: 3px; }
        .btn-primary:active { transform: translateY(1px); }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            font-size: 0.92rem;
            background-color: var(--palette-clay-tint);
            color: var(--palette-clay-text);
            border: 1px solid var(--palette-clay-border);
            animation: card-in 300ms var(--ease);
        }
        .alert svg { width: 1.15rem; height: 1.15rem; flex-shrink: 0; margin-top: 0.1rem; color: var(--brand-danger); }
        .alert ul { margin-top: 0.35rem; margin-left: 1.1rem; }

        /* Short screens (laptops, Windows display scaling): tighter spacing so
           the whole card fits without scrolling. */
        @media (max-height: 820px) and (min-width: 600px) {
            .card { padding-top: 1.4rem; padding-bottom: 1.3rem; }
            .header { margin-top: 0.25rem; margin-bottom: 1.2rem; }
            .brand-mark { width: 50px; height: 50px; margin-bottom: 0.75rem; }
            .brand-mark svg { width: 26px; height: 26px; }
            .title { font-size: 2.3rem; margin-bottom: 0.35rem; }
            .form-group { margin-bottom: 0.85rem; }
            input[type="text"], input[type="password"] { min-height: 50px; }
            .btn { min-height: 52px; }
        }
    </style>
</head>
<body>
    <div class="bg-photo" aria-hidden="true"></div>
    <div class="bg-wash" aria-hidden="true"></div>

    <div class="container">
        <div class="card">
            <a href="{{ route('welcome') }}" class="back-link" aria-label="Back to Home">
                <span class="back-link-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </span>
                Home
            </a>

            <div class="header">
                <div class="brand-mark">
                    {{-- Same sprout as the landing page logo: a stem and two outlined leaves. --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22V9"/><path d="M12 14c-4.2 0-7.2-3-7.2-7.2 4.2 0 7.2 3 7.2 7.2Z"/><path d="M12 14c4.2 0 7.2-3 7.2-7.2-4.2 0-7.2 3-7.2 7.2Z"/></svg>
                </div>
                <h1 class="title">Welcome Back</h1>
                <p class="subtitle">Sign in to your CFMC cooperative account</p>
            </div>

            @if (session('status'))
                <div class="alert" style="background-color: var(--brand-success-light); color: var(--brand-success-dark); border-color: var(--palette-sprout-border);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--brand-success);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
                    <div>{{ session('status') }}</div>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
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
