@extends('adminlte::page')

@section('title', 'Chat Members - ' . $roadmap->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Chat Members</h1>
            <p class="text-muted mb-0">
                <strong>Roadmap:</strong> {{ $roadmap->title }}
            </p>
        </div>
        <a href="{{ route('admin.chat-moderation.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Chat Rooms
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chat Room Members</h3>
            <div class="card-tools">
                <span class="badge badge-info">{{ $enrollments->total() }} Member(s)</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Enrolled At</th>
                        <th>Status</th>
                        <th>Moderation Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $memberData)
                        @php
                            $user = $memberData['user'];
                            $enrollment = $memberData['enrollment'];
                            $isMuted = $memberData['is_muted'];
                            $isBanned = $memberData['is_banned'];
                            $muteRecord = $memberData['mute_record'];
                            $banRecord = $memberData['ban_record'];
                        @endphp
                        @if($user)
                        <tr>
                            <td>
                                <strong>{{ $user->username ?? 'Unknown User' }}</strong>
                                @if($user->profile_picture)
                                    <br>
                                    <img src="{{ $user->profile_picture }}" alt="{{ $user->username ?? 'User' }}" class="img-circle img-size-32" style="width: 32px; height: 32px;">
                                @endif
                            </td>
                            <td>{{ $user->email ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $roleBadge = match($user->role ?? 'user') {
                                        'admin' => 'danger',
                                        'tech_admin' => 'warning',
                                        'user' => 'primary',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $roleBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}
                                </span>
                            </td>
                            <td>{{ $enrollment->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @php
                                    $statusBadge = match($enrollment->status) {
                                        'active' => 'success',
                                        'completed' => 'info',
                                        'paused' => 'warning',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $statusBadge }}">
                                    {{ ucfirst($enrollment->status ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                @if($isBanned)
                                    <span class="badge badge-danger">
                                        <i class="fas fa-ban"></i> Banned
                                    </span>
                                    @if($banRecord && $banRecord->reason)
                                        <br>
                                        <small class="text-muted">Reason: {{ Str::limit($banRecord->reason, 30) }}</small>
                                    @endif
                                @elseif($isMuted)
                                    <span class="badge badge-warning">
                                        <i class="fas fa-volume-mute"></i> Muted
                                    </span>
                                    @if($muteRecord)
                                        @if($muteRecord->muted_until)
                                            <br>
                                            <small class="text-muted">Until: {{ $muteRecord->muted_until->format('Y-m-d H:i') }}</small>
                                        @endif
                                        @if($muteRecord->reason)
                                            <br>
                                            <small class="text-muted">Reason: {{ Str::limit($muteRecord->reason, 30) }}</small>
                                        @endif
                                    @endif
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Active
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if(in_array($user->role ?? 'user', ['admin', 'tech_admin']))
                                        <span class="btn btn-sm btn-secondary disabled" title="Cannot moderate admin users">
                                            <i class="fas fa-shield-alt"></i> Admin
                                        </span>
                                    @else
                                        @if($isBanned)
                                            <form action="{{ route('admin.chat-moderation.unban', $roadmap) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to unban {{ $user->username ?? 'this user' }}?');">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-unlock"></i> Unban
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.chat-moderation.ban', $roadmap) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to ban {{ $user->username ?? 'this user' }} from this chat room?');">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-ban"></i> Ban
                                                </button>
                                            </form>
                                        @endif

                                        @if($isMuted)
                                            <form action="{{ route('admin.chat-moderation.unmute', $roadmap) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to unmute {{ $user->username ?? 'this user' }}?');">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-volume-up"></i> Unmute
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.chat-moderation.mute', $roadmap) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to mute {{ $user->username ?? 'this user' }}?');">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-volume-mute"></i> Mute
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <p class="text-muted mb-0">No members found in this chat room.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $enrollments->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

