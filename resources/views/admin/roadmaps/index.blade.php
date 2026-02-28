@extends('adminlte::page')

@section('title', 'Roadmaps')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Roadmaps</h1>
        <a href="{{ route('admin.roadmaps.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Roadmap
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
        <div class="card-header">
            <h3 class="card-title">All Roadmaps</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.roadmaps.index') }}" class="form-inline">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <select name="level" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Levels</option>
                        <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                    <select name="is_active" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
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
                        <th>Description</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Learning Units</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roadmaps as $roadmap)
                        <tr>
                            <td>{{ $roadmap->id }}</td>
                            <td>{{ Str::limit($roadmap->title, 50) }}</td>
                            <td>{{ Str::limit($roadmap->description ?? 'N/A', 50) }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ ucfirst($roadmap->level ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $roadmap->is_active ? 'success' : 'secondary' }}">
                                    {{ $roadmap->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $roadmap->learningUnits->count() ?? 0 }}</td>
                            <td>{{ $roadmap->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.roadmaps.show', $roadmap) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.roadmaps.edit', $roadmap) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.roadmaps.toggle-active', $roadmap) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $roadmap->is_active ? 'secondary' : 'success' }}">
                                            <i class="fas fa-toggle-{{ $roadmap->is_active ? 'on' : 'off' }}"></i> {{ $roadmap->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.roadmaps.destroy', $roadmap) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this roadmap?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No roadmaps found.</td>
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
