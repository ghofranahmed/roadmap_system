@extends('adminlte::page')

@section('title', 'Announcements')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Announcements</h1>
        @can('create', App\Models\Announcement::class)
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Announcement
            </a>
        @endcan
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Announcements</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.announcements.index') }}" class="form-inline">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <select name="type" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                        <option value="technical" {{ request('type') == 'technical' ? 'selected' : '' }}>Technical</option>
                        <option value="opportunity" {{ request('type') == 'opportunity' ? 'selected' : '' }}>Opportunity</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created By</th>
                        <th>Starts At</th>
                        <th>Ends At</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>{{ $announcement->id }}</td>
                            <td>{{ Str::limit($announcement->title, 50) }}</td>
                            <td>
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
                            </td>
                            <td>{{ $announcement->creator->username ?? 'N/A' }}</td>
                            <td>{{ $announcement->starts_at ? $announcement->starts_at->format('Y-m-d H:i') : 'N/A' }}</td>
                            <td>{{ $announcement->ends_at ? $announcement->ends_at->format('Y-m-d H:i') : 'N/A' }}</td>
                            <td>{{ $announcement->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    @can('update', $announcement)
                                        <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endcan
                                    @can('delete', $announcement)
                                        <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No announcements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $announcements->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

