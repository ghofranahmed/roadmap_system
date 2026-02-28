@extends('adminlte::page')

@section('title', 'Sub-Lessons')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Sub-Lessons</h1>
        <a href="{{ route('admin.sub-lessons.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Sub-Lesson
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
            <h3 class="card-title">All Sub-Lessons</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.sub-lessons.index') }}" class="form-inline">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    @if(isset($lessons) && $lessons->count() > 0)
                        <select name="lesson_id" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                            <option value="">All Lessons</option>
                            @foreach($lessons as $lesson)
                                <option value="{{ $lesson->id }}" {{ (string) $lesson->id === request('lesson_id') ? 'selected' : '' }}>
                                    {{ $lesson->title }} ({{ $lesson->learningUnit->roadmap->title ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Lesson</th>
                        <th>Position</th>
                        <th>Resources</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subLessons as $subLesson)
                        <tr>
                            <td>{{ $subLesson->id }}</td>
                            <td>{{ Str::limit($subLesson->description, 100) }}</td>
                            <td>{{ $subLesson->lesson->title ?? 'N/A' }}</td>
                            <td>{{ $subLesson->position }}</td>
                            <td>{{ $subLesson->resources->count() ?? 0 }}</td>
                            <td>{{ $subLesson->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.sub-lessons.show', $subLesson) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.sub-lessons.edit', $subLesson) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.sub-lessons.destroy', $subLesson) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sub-lesson?');">
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
                            <td colspan="7" class="text-center">No sub-lessons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $subLessons->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

