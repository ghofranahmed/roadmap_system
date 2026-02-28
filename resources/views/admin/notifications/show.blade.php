@extends('adminlte::page')

@section('title', 'Notification Details')

@section('content_header')
    <h1>Notification Details</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Notification #{{ $notification->id }}</h3>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Notifications
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <dl class="row">
                        <dt class="col-sm-3">Title</dt>
                        <dd class="col-sm-9">{{ $notification->title }}</dd>

                        <dt class="col-sm-3">Message</dt>
                        <dd class="col-sm-9">{{ $notification->message }}</dd>

                        <dt class="col-sm-3">Type</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-secondary">{{ ucfirst($notification->type) }}</span>
                        </dd>

                        <dt class="col-sm-3">Priority</dt>
                        <dd class="col-sm-9">
                            @php
                                $priorityBadge = match($notification->priority ?? 'medium') {
                                    'high' => 'danger',
                                    'medium' => 'warning',
                                    'low' => 'info',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge badge-{{ $priorityBadge }}">
                                {{ ucfirst($notification->priority ?? 'Medium') }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Recipient</dt>
                        <dd class="col-sm-9">
                            @if($notification->user)
                                <i class="fas fa-user mr-1"></i>
                                {{ $notification->user->username }} ({{ $notification->user->email }})
                            @else
                                <span class="badge badge-info"><i class="fas fa-globe mr-1"></i> Broadcast</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Read Status</dt>
                        <dd class="col-sm-9">
                            @if($notification->read_at)
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Read</span>
                                <small class="text-muted ml-2">{{ $notification->read_at->format('Y-m-d H:i') }}</small>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Unread</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Active</dt>
                        <dd class="col-sm-9">
                            @if($notification->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Scheduled At</dt>
                        <dd class="col-sm-9">
                            @if($notification->scheduled_at)
                                {{ $notification->scheduled_at->format('Y-m-d H:i') }}
                                @if($notification->scheduled_at->isFuture())
                                    <span class="badge badge-info ml-1">Pending</span>
                                @else
                                    <span class="badge badge-success ml-1">Delivered</span>
                                @endif
                            @else
                                <span class="text-muted">Immediate</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Linked Announcement</dt>
                        <dd class="col-sm-9">
                            @if($notification->announcement)
                                <a href="{{ route('admin.announcements.show', $notification->announcement->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-bullhorn mr-1"></i>
                                    #{{ $notification->announcement->id }} - {{ Str::limit($notification->announcement->title, 50) }}
                                </a>
                            @else
                                <span class="text-muted">Not linked to any announcement</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Created At</dt>
                        <dd class="col-sm-9">{{ $notification->created_at->format('Y-m-d H:i:s') }}</dd>

                        <dt class="col-sm-3">Updated At</dt>
                        <dd class="col-sm-9">{{ $notification->updated_at->format('Y-m-d H:i:s') }}</dd>
                    </dl>

                    @if($notification->metadata)
                        <hr>
                        <h5><i class="fas fa-database mr-1"></i> Metadata</h5>
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($notification->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-info-circle mr-1"></i> Summary</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td><i class="fas fa-tag mr-1"></i> Type</td>
                                    <td class="text-right">{{ ucfirst($notification->type) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-flag mr-1"></i> Priority</td>
                                    <td class="text-right">{{ ucfirst($notification->priority ?? 'Medium') }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-user mr-1"></i> Delivery</td>
                                    <td class="text-right">{{ $notification->user_id ? 'Personal' : 'Broadcast' }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-link mr-1"></i> Announcement</td>
                                    <td class="text-right">{{ $notification->announcement_id ? 'Yes' : 'No' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <form action="{{ route('admin.notifications.destroy', $notification->id) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('Are you sure you want to delete this notification?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Notification
                </button>
            </form>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

