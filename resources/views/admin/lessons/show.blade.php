@extends('adminlte::page')

@section('title', 'Lesson Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Lesson Details</h1>
        <div>
            <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $lesson->title }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $lesson->id }}</dd>

                        <dt class="col-sm-3">Title:</dt>
                        <dd class="col-sm-9">{{ $lesson->title }}</dd>

                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{{ $lesson->description ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Learning Unit:</dt>
                        <dd class="col-sm-9">{{ $lesson->learningUnit->title ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Position:</dt>
                        <dd class="col-sm-9">{{ $lesson->position }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-{{ $lesson->is_active ? 'success' : 'secondary' }}">
                                {{ $lesson->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Sub-Lessons:</dt>
                        <dd class="col-sm-9">{{ $lesson->subLessons->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Created At:</dt>
                        <dd class="col-sm-9">{{ $lesson->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Updated At:</dt>
                        <dd class="col-sm-9">{{ $lesson->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.lessons.toggle-active', $lesson) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $lesson->is_active ? 'secondary' : 'success' }} btn-block">
                            <i class="fas fa-toggle-{{ $lesson->is_active ? 'on' : 'off' }}"></i> 
                            {{ $lesson->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lesson? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Lesson
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

