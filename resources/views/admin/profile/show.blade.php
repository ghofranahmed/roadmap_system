@extends('adminlte::page')

@section('title', 'My Profile')

@section('content_header')
    <h1><i class="fas fa-user-circle mr-2"></i>My Profile</h1>
@stop

@section('content')

    {{-- Success / Error Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- Profile Card --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle elevation-2"
                             src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                             alt="Profile Picture"
                             style="width: 128px; height: 128px; object-fit: cover;">
                    </div>

                    <h3 class="profile-username text-center mt-3">{{ $user->username }}</h3>
                    <p class="text-muted text-center">
                        <span class="badge badge-{{ $user->role === 'admin' ? 'primary' : 'success' }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b><i class="fas fa-envelope mr-2"></i>Email</b>
                            <a class="float-right text-muted">{{ $user->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-calendar mr-2"></i>Joined</b>
                            <a class="float-right text-muted">{{ $user->created_at->format('M d, Y') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-clock mr-2"></i>Last Login</b>
                            <a class="float-right text-muted">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'N/A' }}
                            </a>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-signal mr-2"></i>Last Active</b>
                            <a class="float-right text-muted">
                                {{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'N/A' }}
                            </a>
                        </li>
                    </ul>

                    <div class="text-center">
                        <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary btn-sm mr-1">
                            <i class="fas fa-edit mr-1"></i> Edit Profile
                        </a>
                        <a href="{{ route('admin.profile.password') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-key mr-1"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Account Details Card --}}
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Account Details</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Username</strong></div>
                        <div class="col-sm-8">{{ $user->username }}</div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Email Address</strong></div>
                        <div class="col-sm-8">{{ $user->email }}</div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Role</strong></div>
                        <div class="col-sm-8">
                            <span class="badge badge-{{ $user->role === 'admin' ? 'primary' : 'success' }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Email Verified</strong></div>
                        <div class="col-sm-8">
                            @if($user->email_verified_at)
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Verified on {{ $user->email_verified_at->format('M d, Y') }}</span>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-times mr-1"></i>Not Verified</span>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Notifications</strong></div>
                        <div class="col-sm-8">
                            @if($user->is_notifications_enabled)
                                <span class="badge badge-success"><i class="fas fa-bell mr-1"></i>Enabled</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-bell-slash mr-1"></i>Disabled</span>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Account Created</strong></div>
                        <div class="col-sm-8">{{ $user->created_at->format('F d, Y \\a\\t h:i A') }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4"><strong>Last Updated</strong></div>
                        <div class="col-sm-8">{{ $user->updated_at->format('F d, Y \\a\\t h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
<style>
    .profile-user-img {
        border: 3px solid #adb5bd;
    }
</style>
@stop

@section('js')
@stop

