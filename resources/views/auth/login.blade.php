<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - COOP</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #111827;
        }

        .container { max-width: 450px; width: 100%; }
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            padding: 2.5rem;
        }
        .header { text-align: center; margin-bottom: 2rem; }
        .logo { font-size: 2rem; margin-bottom: 1rem; }
        .title { font-size: 1.875rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem; }
        .subtitle { color: #4b5563; font-size: 0.95rem; }

        .form-group { margin-bottom: 1.5rem; }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #111827;
            font-size: 0.95rem;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.15s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
        }

        .error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.375rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }
        input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            cursor: pointer;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 1.5rem;
        }
        .forgot-password a {
            color: #16a34a;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.15s;
            font-weight: 600;
        }
        .forgot-password a:hover {
            color: #15803d;
        }
        .forgot-password .description {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
            font-weight: 400;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            font-family: inherit;
        }
        .btn-primary {
            background-color: #16a34a;
            color: white;
        }
        .btn-primary:hover {
            background-color: #15803d;
        }
        .btn-primary:active {
            transform: scale(0.98);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            color: #16a34a;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.15s;
        }
        .back-link:hover {
            color: #15803d;
        }

        .alert {
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <a href="{{ route('welcome') }}" class="back-link">← Back to Home</a>

            <div class="header">
                <div class="logo">🤝</div>
                <h1 class="title">Welcome Back</h1>
                <p class="subtitle">Sign in to your COOP account</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Login Failed</strong>
                    <ul style="margin-top: 0.5rem; margin-left: 1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Username / Email -->
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        placeholder="Enter username or email"
                    >
                    @error('username')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="••••••••"
                    >
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
                <button type="submit" class="btn btn-primary">Login</button>

                <!-- Forgot Password Link -->
                <div class="forgot-password">
                    <a href="#">Forgot your password?</a>
                    <div class="description">Admin and Manager only</div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>