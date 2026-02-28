@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Forgot Password')

@section('auth_header')
    <p class="login-box-msg">You forgot your password? Here you can easily retrieve a new password.</p>
@endsection

@section('auth_body')
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        {{-- Email --}}
        <div class="input-group mb-3">
            <input type="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
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

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
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

