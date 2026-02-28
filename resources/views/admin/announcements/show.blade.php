@extends('adminlte::page')

@section('title', 'Announcement Details')

@section('content_header')
    <h1>Announcement Details</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Announcement #{{ $announcement->id }}</h3>
                    <div>
                        @if($announcement->status === 'published')
                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Published</span>
                        @else
                            <span class="badge badge-secondary"><i class="fas fa-edit mr-1"></i> Draft</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Title</dt>
                        <dd class="col-sm-9">{{ $announcement->title }}</dd>

                        <dt class="col-sm-3">Type</dt>
                        <dd class="col-sm-9">
                            @php
                                $badgeColor = match($announcement->type) {
                                    'general' => 'primary',
                                    'technical' => 'success',
                                    'opportunity' => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge badge-{{ $badgeColor }}">
                                {{ ucfirst($announcement->type) }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $announcement->description }}</dd>

                        <dt class="col-sm-3">Link</dt>
                        <dd class="col-sm-9">
                            @if($announcement->link)
                                <a href="{{ $announcement->link }}" target="_blank" rel="noopener noreferrer">
                                    {{ $announcement->link }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Starts At</dt>
                        <dd class="col-sm-9">
                            {{ $announcement->starts_at ? $announcement->starts_at->format('Y-m-d H:i') : 'N/A' }}
                        </dd>

                        <dt class="col-sm-3">Ends At</dt>
                        <dd class="col-sm-9">
                            {{ $announcement->ends_at ? $announcement->ends_at->format('Y-m-d H:i') : 'N/A' }}
                        </dd>

                        <dt class="col-sm-3">Created By</dt>
                        <dd class="col-sm-9">
                            {{ optional($announcement->creator)->username ?? 'N/A' }}
                        </dd>

                        <dt class="col-sm-3">Created At</dt>
                        <dd class="col-sm-9">{{ $announcement->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        @can('update', $announcement)
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endcan
                    </div>
                    <div>
                        @can('delete', $announcement)
                            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- Notification Settings & Info Sidebar --}}
        <div class="col-md-4">
            {{-- Notification Settings Card --}}
            <div class="card card-outline {{ $announcement->send_notification ? 'card-success' : 'card-secondary' }}">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-bell mr-1"></i> Notification Settings</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Send Notifications</td>
                            <td class="text-right">
                                @if($announcement->send_notification)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        @if($announcement->send_notification)
                            <tr>
                                <td>Target Audience</td>
                                <td class="text-right">
                                    @php
                                        $targetLabel = match($announcement->target_type) {
                                            'all' => 'All Users',
                                            'specific_users' => 'Specific Users',
                                            'inactive_users' => 'Inactive Users',
                                            'low_progress' => 'Low Progress',
                                            default => ucfirst($announcement->target_type ?? 'N/A'),
                                        };
                                    @endphp
                                    <span class="badge badge-info">{{ $targetLabel }}</span>
                                </td>
                            </tr>
                            @if($announcement->target_type === 'specific_users' && $announcement->target_rules)
                                <tr>
                                    <td>Selected Users</td>
                                    <td class="text-right">
                                        <span class="badge badge-primary">{{ count($announcement->target_rules) }} user(s)</span>
                                    </td>
                                </tr>
                            @endif
                        @endif
                    </table>
                </div>
            </div>

            {{-- Linked Notifications Card --}}
            @php
                $notificationCount = $announcement->notifications()->count();
                $readCount = $announcement->notifications()->whereNotNull('read_at')->count();
                $unreadCount = $notificationCount - $readCount;
            @endphp
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-paper-plane mr-1"></i> Linked Notifications</h5>
                </div>
                <div class="card-body">
                    @if($notificationCount > 0)
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-bold text-lg">{{ $notificationCount }}</div>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4">
                                <div class="text-bold text-lg text-success">{{ $readCount }}</div>
                                <small class="text-muted">Read</small>
                            </div>
                            <div class="col-4">
                                <div class="text-bold text-lg text-warning">{{ $unreadCount }}</div>
                                <small class="text-muted">Unread</small>
                            </div>
                        </div>
                        <hr>
                        <a href="{{ route('admin.notifications.index', ['announcement_id' => $announcement->id]) }}"
                           class="btn btn-sm btn-outline-info btn-block">
                            <i class="fas fa-list mr-1"></i> View All Notifications
                        </a>
                    @else
                        <p class="text-muted text-center mb-0">
                            <i class="fas fa-info-circle mr-1"></i> No notifications linked to this announcement.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
