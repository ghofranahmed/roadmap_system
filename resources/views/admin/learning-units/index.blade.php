@extends('adminlte::page')

@section('title', 'Learning Units')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Learning Units</h1>
        <a href="{{ route('admin.learning-units.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Learning Unit
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
            <h3 class="card-title">All Learning Units</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.learning-units.index') }}" class="form-inline">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control" placeholder="Search title..." value="{{ request('search') }}">
                    </div>

                    <select name="roadmap_id" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Roadmaps</option>
                        @foreach($roadmaps as $roadmap)
                            <option value="{{ $roadmap->id }}" {{ (string) $roadmap->id === request('roadmap_id') ? 'selected' : '' }}>
                                {{ $roadmap->title }}
                            </option>
                        @endforeach
                    </select>

                    <select name="is_active" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <button type="submit" class="btn btn-sm btn-default ml-2">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Roadmap</th>
                        <th>Position</th>
                        <th>Type</th>
                        <th>Active</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        <tr>
                            <td>{{ $unit->id }}</td>
                            <td>{{ $unit->title }}</td>
                            <td>{{ $unit->roadmap->title ?? 'N/A' }}</td>
                            <td>{{ $unit->position }}</td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ ucfirst($unit->unit_type ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $unit->is_active ? 'success' : 'secondary' }}">
                                    {{ $unit->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $unit->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.learning-units.show', $unit) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.learning-units.edit', $unit) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.learning-units.toggle-active', $unit) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $unit->is_active ? 'secondary' : 'success' }}">
                                            <i class="fas fa-toggle-{{ $unit->is_active ? 'on' : 'off' }}"></i> {{ $unit->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.learning-units.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this learning unit?');">
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
                            <td colspan="8" class="text-center">No learning units found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $units->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
