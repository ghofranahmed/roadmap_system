@extends('adminlte::page')

@section('title', 'Create Tech Admin')

@section('content_header')
    <h1>Create New Technical Admin</h1>
    <p class="text-muted">Create a new Technical Admin user. You can only create Technical Admin accounts.</p>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($rateLimitInfo) && !$rateLimitInfo['allowed'])
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Rate Limit Reached:</strong> {{ $rateLimitInfo['message'] }}
            @if($rateLimitInfo['remaining_time'])
                @php
                    $hours = floor($rateLimitInfo['remaining_minutes'] / 60);
                    $minutes = $rateLimitInfo['remaining_minutes'] % 60;
                @endphp
                You can create another admin in 
                @if($hours > 0) {{ $hours }} hour(s) and @endif 
                {{ $minutes }} minute(s).
            @endif
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($rateLimitInfo) && $rateLimitInfo['allowed'])
        <div class="alert alert-info">
            <strong>Rate Limit:</strong> You can create {{ $rateLimitInfo['remaining_creations'] }} more Technical Admin(s) in the next 24 hours.
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Technical Admin Information</h3>
        </div>
        <form action="{{ route('admin.create-tech-admin.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label for="username">Username <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('username') is-invalid @enderror" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
                           required 
                           maxlength="255">
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           maxlength="255">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Password must be at least 8 characters with letters, numbers, and symbols.</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required>
                    @error('password_confirmation')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_notifications_enabled" 
                               name="is_notifications_enabled" 
                               value="1" 
                               {{ old('is_notifications_enabled', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_notifications_enabled">
                            Enable Notifications
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success" @if(isset($rateLimitInfo) && !$rateLimitInfo['allowed']) disabled @endif>
                    <i class="fas fa-check"></i> Create Technical Admin
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-default">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

