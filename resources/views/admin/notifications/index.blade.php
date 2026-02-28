@extends('adminlte::page')

@section('title', 'Notifications Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Notifications Management</h1>
            <p class="text-muted mb-0">Manage system notifications sent to users</p>
        </div>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Notification
        </a>
    </div>
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

    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>Total Notifications</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['unread'] ?? 0 }}</h3>
                    <p>Unread</p>
                </div>
                <div class="icon">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['broadcast'] ?? 0 }}</h3>
                    <p>Broadcast</p>
                </div>
                <div class="icon">
                    <i class="fas fa-globe"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['linked'] ?? 0 }}</h3>
                    <p>Linked to Announcements</p>
                </div>
                <div class="icon">
                    <i class="fas fa-link"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Notifications</h3>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                            <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>Announcement</option>
                            <option value="reminder" {{ request('type') == 'reminder' ? 'selected' : '' }}>Reminder</option>
                            <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>System</option>
                            <option value="alert" {{ request('type') == 'alert' ? 'selected' : '' }}>Alert</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="priority" class="form-control form-control-sm">
                            <option value="">All Priorities</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="read_status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Read</option>
                            <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Unread</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-default">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>

            {{-- Notifications Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Announcement</th>
                            <th>Status</th>
                            <th>Scheduled</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $notification)
                            <tr>
                                <td>{{ $notification->id }}</td>
                                <td>{{ Str::limit($notification->title, 40) }}</td>
                                <td>
                                    @if($notification->user)
                                        <span title="{{ $notification->user->email }}">
                                            {{ $notification->user->username }}
                                        </span>
                                    @else
                                        <span class="badge badge-info">Broadcast</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-secondary">{{ ucfirst($notification->type) }}</span></td>
                                <td>
                                    @php
                                        $pBadge = match($notification->priority ?? 'medium') {
                                            'high' => 'danger',
                                            'medium' => 'warning',
                                            'low' => 'info',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $pBadge }}">
                                        {{ ucfirst($notification->priority ?? 'Medium') }}
                                    </span>
                                </td>
                                <td>
                                    @if($notification->announcement)
                                        <a href="{{ route('admin.announcements.show', $notification->announcement->id) }}" title="{{ $notification->announcement->title }}">
                                            {{ Str::limit($notification->announcement->title, 25) }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notification->read_at)
                                        <span class="badge badge-success">Read</span>
                                    @else
                                        <span class="badge badge-warning">Unread</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notification->scheduled_at)
                                        <small>{{ $notification->scheduled_at->format('Y-m-d H:i') }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><small>{{ $notification->created_at->format('Y-m-d H:i') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.notifications.show', $notification->id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}"
                                          method="POST"
                                          style="display: inline-block;"
                                          onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    <i class="fas fa-bell-slash mr-1"></i> No notifications found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
