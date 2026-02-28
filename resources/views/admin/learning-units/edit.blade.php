@extends('adminlte::page')

@section('title', 'Edit Learning Unit')

@section('content_header')
    <h1>Edit Learning Unit</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Learning Unit #{{ $learningUnit->id }}</h3>
        </div>
        <form action="{{ route('admin.learning-units.update', $learningUnit) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label for="roadmap_id">Roadmap <span class="text-danger">*</span></label>
                    <select class="form-control @error('roadmap_id') is-invalid @enderror" 
                            id="roadmap_id" 
                            name="roadmap_id" 
                            required>
                        <option value="">Select Roadmap</option>
                        @foreach($roadmaps as $roadmap)
                            <option value="{{ $roadmap->id }}" {{ old('roadmap_id', $learningUnit->roadmap_id) == $roadmap->id ? 'selected' : '' }}>
                                {{ $roadmap->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('roadmap_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('title') is-invalid @enderror" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $learningUnit->title) }}" 
                           required 
                           maxlength="255">
                    @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="unit_type">Unit Type</label>
                    <input type="text" 
                           class="form-control @error('unit_type') is-invalid @enderror" 
                           id="unit_type" 
                           name="unit_type" 
                           value="{{ old('unit_type', $learningUnit->unit_type) }}" 
                           maxlength="255">
                    @error('unit_type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Current Position: {{ $learningUnit->position }}</label>
                    <small class="form-text text-muted d-block">Use the reorder action to change position.</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input @error('is_active') is-invalid @enderror" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $learningUnit->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                    @error('is_active')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Learning Unit
                </button>
                <a href="{{ route('admin.learning-units.index') }}" class="btn btn-default">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

