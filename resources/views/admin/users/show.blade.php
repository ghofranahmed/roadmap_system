@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <h1>User Details</h1>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">User #{{ $user->id }}</h3>
            <a href="{{ route('admin.users.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Username</dt>
                <dd class="col-sm-9">{{ $user->username ?? 'N/A' }}</dd>

                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $user->email }}</dd>

                <dt class="col-sm-3">Role</dt>
                <dd class="col-sm-9">
                    <span class="badge badge-{{ $user->role === 'tech_admin' ? 'warning' : ($user->role === 'admin' ? 'primary' : 'secondary') }}">
                        {{ $user->role }}
                    </span>
                </dd>

                <dt class="col-sm-3">Created At</dt>
                <dd class="col-sm-9">{{ $user->created_at->format('Y-m-d H:i') }}</dd>

                <dt class="col-sm-3">Last Login</dt>
                <dd class="col-sm-9">{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i') : 'Never' }}</dd>
            </dl>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit User
                </a>
            </div>
            <div class="d-flex">
                <form action="{{ route('admin.users.revoke-tokens', $user) }}" method="POST" class="mr-2" onsubmit="return confirm('Revoke all sessions for this user?');">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-sign-out-alt"></i> Revoke Tokens
                    </button>
                </form>

                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop


