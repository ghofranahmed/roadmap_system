@extends('adminlte::page')

@section('title', 'Roadmap Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Roadmap Details</h1>
        <div>
            <a href="{{ route('admin.roadmaps.edit', $roadmap) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.roadmaps.index') }}" class="btn btn-default">
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
                    <h3 class="card-title">{{ $roadmap->title }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $roadmap->id }}</dd>

                        <dt class="col-sm-3">Title:</dt>
                        <dd class="col-sm-9">{{ $roadmap->title }}</dd>

                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{{ $roadmap->description ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Level:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-info">{{ ucfirst($roadmap->level ?? 'N/A') }}</span>
                        </dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-{{ $roadmap->is_active ? 'success' : 'secondary' }}">
                                {{ $roadmap->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Learning Units:</dt>
                        <dd class="col-sm-9">{{ $roadmap->learningUnits->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Enrollments:</dt>
                        <dd class="col-sm-9">{{ $roadmap->enrollments->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Created At:</dt>
                        <dd class="col-sm-9">{{ $roadmap->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Updated At:</dt>
                        <dd class="col-sm-9">{{ $roadmap->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Learning Units</h3>
                </div>
                <div class="card-body">
                    @if($roadmap->learningUnits && $roadmap->learningUnits->count() > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Position</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roadmap->learningUnits as $unit)
                                    <tr>
                                        <td>{{ $unit->id }}</td>
                                        <td>{{ $unit->title }}</td>
                                        <td>{{ $unit->position }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ ucfirst($unit->unit_type ?? 'N/A') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $unit->is_active ? 'success' : 'secondary' }}">
                                                {{ $unit->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No learning units found.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.roadmaps.toggle-active', $roadmap) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $roadmap->is_active ? 'secondary' : 'success' }} btn-block">
                            <i class="fas fa-toggle-{{ $roadmap->is_active ? 'on' : 'off' }}"></i> 
                            {{ $roadmap->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.roadmaps.destroy', $roadmap) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this roadmap? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Roadmap
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
