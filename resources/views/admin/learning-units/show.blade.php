@extends('adminlte::page')

@section('title', 'Learning Unit Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Learning Unit Details</h1>
        <div>
            <a href="{{ route('admin.learning-units.edit', $learningUnit) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.learning-units.index') }}" class="btn btn-default">
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
                    <h3 class="card-title">{{ $learningUnit->title }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->id }}</dd>

                        <dt class="col-sm-3">Title:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->title }}</dd>

                        <dt class="col-sm-3">Roadmap:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->roadmap->title ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Position:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->position }}</dd>

                        <dt class="col-sm-3">Unit Type:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->unit_type ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-{{ $learningUnit->is_active ? 'success' : 'secondary' }}">
                                {{ $learningUnit->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Lessons:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->lessons->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Quizzes:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->quizzes->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Challenges:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->challenges->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Created At:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Updated At:</dt>
                        <dd class="col-sm-9">{{ $learningUnit->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>
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
                    <form action="{{ route('admin.learning-units.toggle-active', $learningUnit) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $learningUnit->is_active ? 'secondary' : 'success' }} btn-block">
                            <i class="fas fa-toggle-{{ $learningUnit->is_active ? 'on' : 'off' }}"></i> 
                            {{ $learningUnit->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.learning-units.destroy', $learningUnit) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this learning unit? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Learning Unit
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

