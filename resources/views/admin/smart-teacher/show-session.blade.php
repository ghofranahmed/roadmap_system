@extends('adminlte::page')

@section('title', 'Chatbot Session #' . $session->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Chatbot Session Details</h1>
            <p class="text-muted mb-0">
                <strong>Session:</strong> {{ $session->title ?? 'Untitled Session' }} (ID: {{ $session->id }})
            </p>
        </div>
        <div>
            <a href="{{ route('admin.smart-teacher.logs') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to Logs
            </a>
            <a href="{{ route('admin.smart-teacher.index') }}" class="btn btn-default">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Session Information</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Session ID</dt>
                        <dd>{{ $session->id }}</dd>
                        
                        <dt>User</dt>
                        <dd>
                            @if($session->user)
                                <strong>{{ $session->user->username }}</strong><br>
                                <small class="text-muted">{{ $session->user->email }}</small>
                            @else
                                <span class="text-muted">Unknown User</span>
                            @endif
                        </dd>
                        
                        <dt>Title</dt>
                        <dd>{{ $session->title ?? 'Untitled Session' }}</dd>
                        
                        <dt>Total Messages</dt>
                        <dd>{{ $session->messages->count() }}</dd>
                        
                        <dt>Created</dt>
                        <dd>{{ $session->created_at->format('Y-m-d H:i:s') }}</dd>
                        
                        <dt>Last Activity</dt>
                        <dd>
                            @if($session->last_activity_at)
                                {{ $session->last_activity_at->format('Y-m-d H:i:s') }}
                                <br><small class="text-muted">{{ $session->last_activity_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Messages</h3>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @forelse($session->messages as $message)
                        <div class="mb-3 p-3 rounded {{ $message->role === 'user' ? 'bg-light' : 'bg-info text-white' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong>
                                        @if($message->role === 'user')
                                            <i class="fas fa-user"></i> User
                                        @else
                                            <i class="fas fa-robot"></i> Smart Teacher
                                        @endif
                                    </strong>
                                    <small class="ml-2 text-muted">
                                        {{ $message->created_at->format('Y-m-d H:i:s') }}
                                    </small>
                                    @if($message->tokens_used)
                                        <span class="badge badge-secondary ml-2">
                                            {{ $message->tokens_used }} tokens
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2">
                                {!! nl2br(e($message->body)) !!}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No messages in this session.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

