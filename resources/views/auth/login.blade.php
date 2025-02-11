@extends('layouts.app')

@section('title', 'Login - Simulasi Penyesuaian')
@section('page-title', 'Simulasi Penyesuaian Anggaran')

@section('content')

<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg" style="width: 400px;">
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
                    <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
                    @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                        <label class="form-check-label" for="remember_me">
                            Ingat Saya
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a class="text-decoration-none" href="{{ route('password.request') }}">Lupa Password?</a>
                    @endif
                </div>

                <!-- Login Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Masuk</button>
                </div>

                <!-- Register Link -->
                <div class="text-center mt-3">
                    <span>Belum punya akun?</span> 
                    <a href="{{ route('register') }}" class="text-decoration-none">Daftar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
