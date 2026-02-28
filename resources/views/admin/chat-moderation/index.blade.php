@extends('adminlte::page')

@section('title', 'Chat Moderation')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Chat Moderation</h1>
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
            <h3 class="card-title">Chat Rooms (Roadmaps)</h3>
            <div class="card-tools">
                <span class="badge badge-info">{{ $roadmaps->total() }} Roadmap(s) with Chat Rooms</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Roadmap Title</th>
                        <th>Level</th>
                        <th>Members</th>
                        <th>Chat Room Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roadmaps as $roadmap)
                        <tr>
                            <td>{{ $roadmap->id }}</td>
                            <td>
                                <strong>{{ $roadmap->title }}</strong>
                                @if($roadmap->description)
                                    <br>
                                    <small class="text-muted">{{ Str::limit($roadmap->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($roadmap->level) {
                                        'beginner' => 'success',
                                        'intermediate' => 'warning',
                                        'advanced' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $badgeColor }}">
                                    {{ ucfirst($roadmap->level ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $roadmap->enrollments_count ?? 0 }} enrolled</span>
                            </td>
                            <td>
                                @if($roadmap->chatRoom)
                                    <span class="badge badge-{{ $roadmap->chatRoom->is_active ? 'success' : 'secondary' }}">
                                        {{ $roadmap->chatRoom->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">No Chat Room</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.chat-moderation.members', $roadmap) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-users"></i> Manage Members
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <p class="text-muted mb-0">No roadmaps with chat rooms found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $roadmaps->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

