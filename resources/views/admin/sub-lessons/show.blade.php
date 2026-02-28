@extends('adminlte::page')

@section('title', 'Sub-Lesson Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Sub-Lesson Details</h1>
        <div>
            <a href="{{ route('admin.sub-lessons.edit', $subLesson) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.sub-lessons.index') }}" class="btn btn-default">
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
                    <h3 class="card-title">Sub-Lesson #{{ $subLesson->id }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $subLesson->id }}</dd>

                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{{ $subLesson->description }}</dd>

                        <dt class="col-sm-3">Lesson:</dt>
                        <dd class="col-sm-9">{{ $subLesson->lesson->title ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Position:</dt>
                        <dd class="col-sm-9">{{ $subLesson->position }}</dd>

                        <dt class="col-sm-3">Resources:</dt>
                        <dd class="col-sm-9">{{ $subLesson->resources->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Created At:</dt>
                        <dd class="col-sm-9">{{ $subLesson->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Updated At:</dt>
                        <dd class="col-sm-9">{{ $subLesson->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>

            @if($subLesson->resources && $subLesson->resources->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resources</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Language</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subLesson->resources as $resource)
                                    <tr>
                                        <td>{{ $resource->id }}</td>
                                        <td>{{ $resource->title }}</td>
                                        <td>{{ $resource->type ?? 'N/A' }}</td>
                                        <td>{{ $resource->language ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sub-lessons.destroy', $subLesson) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this sub-lesson? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Sub-Lesson
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

