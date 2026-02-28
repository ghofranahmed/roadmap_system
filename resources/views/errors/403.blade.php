@extends('adminlte::page')

@section('title', 'Access Denied')

@section('content_header')
    <h1><i class="fas fa-ban text-danger"></i> Access Denied</h1>
@stop

@section('content')
    <div class="error-page">
        <div class="error-content" style="margin-left: auto; margin-right: auto; max-width: 600px;">
            <h3 class="text-danger">
                <i class="fas fa-exclamation-triangle"></i> You don't have permission to access this page.
            </h3>

            <p class="text-muted">
                @if(isset($exception) && $exception->getMessage())
                    {{ $exception->getMessage() }}
                @else
                    The page you are trying to access requires specific permissions that your account doesn't have.
                @endif
            </p>

            @auth
                @php
                    $user = auth()->user();
                    $message = $exception->getMessage() ?? '';
                    $requiredRole = null;
                    $userRole = $user->role ?? 'user';
                    
                    // Extract required role from message if available
                    if (preg_match('/Required role: (.+)/', $message, $matches)) {
                        $requiredRole = $matches[1];
                    }
                @endphp

                @if($requiredRole && $userRole !== 'user')
                    <div class="alert alert-info">
                        <strong>Your role:</strong> {{ ucfirst(str_replace('_', ' ', $userRole)) }}<br>
                        <strong>Required role:</strong> {{ ucfirst(str_replace('_', ' ', $requiredRole)) }}
                    </div>
                @endif
            @endauth

            <div class="mt-4">
                <a href="javascript:history.back()" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                @auth
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'tech_admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .error-page {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
    }
    .error-content {
        text-align: center;
        padding: 2rem;
    }
    .error-content h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    .error-content p {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
    }
</style>
@stop

@section('js')
@stop

