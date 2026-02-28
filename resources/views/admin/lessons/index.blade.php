@extends('adminlte::page')

@section('title', 'Lessons')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Lessons</h1>
        <a href="{{ route('admin.lessons.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Lesson
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
            <h3 class="card-title">All Lessons</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.lessons.index') }}" class="form-inline">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    @if(isset($learningUnits) && $learningUnits->count() > 0)
                        <select name="learning_unit_id" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                            <option value="">All Learning Units</option>
                            @foreach($learningUnits as $unit)
                                <option value="{{ $unit->id }}" {{ (string) $unit->id === request('learning_unit_id') ? 'selected' : '' }}>
                                    {{ $unit->title }}
                                </option>
                            @endforeach
                        </select>
                    @endif
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
                        <th>Learning Unit</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Sub-Lessons</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lessons as $lesson)
                        <tr>
                            <td>{{ $lesson->id }}</td>
                            <td>{{ Str::limit($lesson->title, 50) }}</td>
                            <td>{{ $lesson->learningUnit->title ?? 'N/A' }}</td>
                            <td>{{ $lesson->position }}</td>
                            <td>
                                <span class="badge badge-{{ $lesson->is_active ? 'success' : 'secondary' }}">
                                    {{ $lesson->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $lesson->subLessons->count() ?? 0 }}</td>
                            <td>{{ $lesson->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.lessons.toggle-active', $lesson) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $lesson->is_active ? 'secondary' : 'success' }}">
                                            <i class="fas fa-toggle-{{ $lesson->is_active ? 'on' : 'off' }}"></i> {{ $lesson->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this lesson?');">
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
                            <td colspan="8" class="text-center">No lessons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $lessons->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

