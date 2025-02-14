<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
        }
        .card {
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.02);
        }
        .form-control {
            padding-left: 2.5rem;
        }
        .input-group-text {
            width: 2.5rem;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-lg p-4" style="width: 400px; background: white;">
            <div class="text-center">
                <img src="assets/images/bpkadlogo.png" alt="BPKAD Logo" style="width: 200px; margin-bottom: 10px;">
            </div>
            <div class="card-body">
                <h3 class="card-title text-center mb-3">Login</h3>
                <p class="text-muted text-center">Masuk untuk melanjutkan</p>

                <!-- Session Status -->
                @if(session('status'))
                    <div class="alert alert-success text-center">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="username">
                        </div>
                        @error('email')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
                        </div>
                        @error('password')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                            <label class="form-check-label" for="remember_me"> Ingat Saya </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="text-decoration-none text-primary" href="{{ route('password.request') }}">Lupa Password?</a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Masuk</button>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center mt-3">
                        <span>Belum punya akun?</span> 
                        <a href="{{ route('register') }}" class="text-decoration-none text-primary">Daftar</a>
                    </div>
                </form>
            </div>
            <div class="text-center mt-3 text-muted">
                <small>By: Tim IT BPKAD</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
