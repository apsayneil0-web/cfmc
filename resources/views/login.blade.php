<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agricultural Cooperative Login</title>

    <!-- ✅ CSS -->
      <link rel=stylesheet href="{{url('css/login.css')}}">
  
    <!-- ✅ Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-page">

<div class="overlay">

    <div class="login-card">

        <div class="user-icon">
            <i class="fa-regular fa-circle-user"></i>
        </div>

        <h1>Welcome Back</h1>

        <p class="subtitle">
            Log in with your assigned username and password
        </p>

        <!-- ✅ Error message -->
        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- ✅ FORM -->
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label>USERNAME OR EMAIL</label>
            <div class="input-box">
                <i class="fa-regular fa-user"></i>
                <input type="text" name="username" placeholder="Enter username or email" required>
            </div>

            <label>PASSWORD</label>
            <div class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" id="password" required>
                <i class="fa-regular fa-eye" id="togglePassword"></i>
            </div>

            <button type="submit" class="login-btn">
                LOG IN
                <i class="fa-solid fa-arrow-right"></i>
            </button>

            <a href="{{ url('/forgot-password') }}" class="forgot">
                Forgot Password? <span class="forgot-note">(Manager & Admin only)</span>
            </a>

        </form>
    </div>
</div>

<script>
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");

togglePassword.addEventListener("click", () => {
    const type = password.type === "password" ? "text" : "password";
    password.type = type;

    togglePassword.classList.toggle("fa-eye");
    togglePassword.classList.toggle("fa-eye-slash");
});
</script>

</body>
</html>
