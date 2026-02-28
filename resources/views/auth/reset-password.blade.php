@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Reset Password')

@section('auth_header')
    <p class="login-box-msg">Enter your new password</p>
@endsection

@section('auth_body')
    <form action="{{ route('password.update') }}" method="POST">
        @csrf

        {{-- Token (hidden) --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email --}}
        <div class="input-group mb-3">
            <input type="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $email) }}"
                   placeholder="Email"
                   required
                   autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password --}}
        <div class="input-group mb-3">
            <input type="password"
                   name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="New Password"
                   required
                   autocomplete="new-password">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password Confirmation --}}
        <div class="input-group mb-3">
            <input type="password"
                   name="password_confirmation"
                   class="form-control"
                   placeholder="Confirm New Password"
                   required
                   autocomplete="new-password">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </div>
        </div>
    </form>
@endsection

@section('auth_footer')
    <p class="mt-3 mb-1">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
    </p>
@endsection

