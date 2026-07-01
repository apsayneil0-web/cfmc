<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="{{ url('css/login.css') }}">
</head>
<body class="login-page">
<div class="overlay">
    <div class="login-card">
        <div class="user-icon">
            <i class="fa-regular fa-circle-user"></i>
        </div>

        <h1>Reset Password</h1>
        <p class="subtitle">Set a new password for your account.</p>

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reset-password', ['token' => $token]) }}">
            @csrf

            <input type="hidden" name="email" value="{{ request('email') }}">

            <label>NEW PASSWORD</label>
            <div class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Enter new password" required>
            </div>

            <label>CONFIRM PASSWORD</label>
            <div class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password_confirmation" placeholder="Confirm new password" required>
            </div>

            <button type="submit" class="login-btn">
                RESET PASSWORD
                <i class="fa-solid fa-arrow-right"></i>
            </button>

            <a href="{{ url('/') }}" class="forgot">Back to Login</a>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</body>
</html>
