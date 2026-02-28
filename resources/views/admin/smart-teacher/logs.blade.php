@extends('adminlte::page')

@section('title', 'Smart Teacher Logs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Smart Teacher Logs</h1>
            <p class="text-muted mb-0">View chatbot sessions and messages</p>
        </div>
        <a href="{{ route('admin.smart-teacher.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Settings
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chatbot Sessions</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Title</th>
                        <th>Messages</th>
                        <th>Last Activity</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>{{ $session->id }}</td>
                            <td>
                                @if($session->user)
                                    <strong>{{ $session->user->username }}</strong><br>
                                    <small class="text-muted">{{ $session->user->email }}</small>
                                @else
                                    <span class="text-muted">Unknown User</span>
                                @endif
                            </td>
                            <td>{{ $session->title ?? 'Untitled Session' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $session->messages_count }}</span>
                            </td>
                            <td>
                                @if($session->last_activity_at)
                                    {{ $session->last_activity_at->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>{{ $session->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.smart-teacher.show-session', $session->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <p class="text-muted mb-0">No chatbot sessions found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $sessions->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

