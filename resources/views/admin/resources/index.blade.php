@extends('adminlte::page')

@section('title', 'Resources')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Resources</h1>
        <a href="{{ route('admin.resources.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Resource
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
            <h3 class="card-title">All Resources</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.resources.index') }}" class="form-inline">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    @if(isset($subLessons) && $subLessons->count() > 0)
                        <select name="sub_lesson_id" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                            <option value="">All Sub-Lessons</option>
                            @foreach($subLessons as $subLesson)
                                <option value="{{ $subLesson->id }}" {{ (string) $subLesson->id === request('sub_lesson_id') ? 'selected' : '' }}>
                                    {{ Str::limit($subLesson->description, 30) }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <select name="type" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="book" {{ request('type') == 'book' ? 'selected' : '' }}>Book</option>
                        <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="article" {{ request('type') == 'article' ? 'selected' : '' }}>Article</option>
                    </select>
                    <select name="language" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Languages</option>
                        <option value="ar" {{ request('language') == 'ar' ? 'selected' : '' }}>Arabic</option>
                        <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>English</option>
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
                        <th>Language</th>
                        <th>Sub-Lesson</th>
                        <th>Link</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resources as $resource)
                        <tr>
                            <td>{{ $resource->id }}</td>
                            <td>{{ Str::limit($resource->title, 50) }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ ucfirst($resource->type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ strtoupper($resource->language) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($resource->subLesson->description ?? 'N/A', 30) }}</td>
                            <td>
                                <a href="{{ $resource->link }}" target="_blank" class="text-primary">
                                    <i class="fas fa-external-link-alt"></i> Open
                                </a>
                            </td>
                            <td>{{ $resource->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.resources.show', $resource) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.resources.edit', $resource) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.resources.destroy', $resource) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this resource?');">
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
                            <td colspan="8" class="text-center">No resources found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $resources->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

