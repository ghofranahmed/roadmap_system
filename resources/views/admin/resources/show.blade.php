@extends('adminlte::page')

@section('title', 'Resource Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Resource Details</h1>
        <div>
            <a href="{{ route('admin.resources.edit', $resource) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.resources.index') }}" class="btn btn-default">
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
                    <h3 class="card-title">{{ $resource->title }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $resource->id }}</dd>

                        <dt class="col-sm-3">Title:</dt>
                        <dd class="col-sm-9">{{ $resource->title }}</dd>

                        <dt class="col-sm-3">Type:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-info">{{ ucfirst($resource->type) }}</span>
                        </dd>

                        <dt class="col-sm-3">Language:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-secondary">{{ strtoupper($resource->language) }}</span>
                        </dd>

                        <dt class="col-sm-3">Link:</dt>
                        <dd class="col-sm-9">
                            <a href="{{ $resource->link }}" target="_blank" class="text-primary">
                                {{ $resource->link }} <i class="fas fa-external-link-alt"></i>
                            </a>
                        </dd>

                        <dt class="col-sm-3">Sub-Lesson:</dt>
                        <dd class="col-sm-9">{{ $resource->subLesson->description ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Created At:</dt>
                        <dd class="col-sm-9">{{ $resource->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Updated At:</dt>
                        <dd class="col-sm-9">{{ $resource->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>
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
                    <form action="{{ route('admin.resources.destroy', $resource) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resource? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Resource
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

