<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="{{ url('css/login.css') }}">
</head>
<body class="login-page">
<div class="overlay">
    <div class="login-card">
        <div class="user-icon">
            <i class="fa-regular fa-circle-user"></i>
        </div>

        <h1>Forgot Password</h1>
        <p class="subtitle">Enter your email to receive a reset link.</p>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('forgot-password') }}">
            @csrf

            <label>EMAIL</label>
            <div class="input-box">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <button type="submit" class="login-btn">
                SEND RESET LINK
                <i class="fa-solid fa-arrow-right"></i>
            </button>

            <a href="{{ url('/') }}" class="forgot">Back to Login</a>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</body>
</html>
