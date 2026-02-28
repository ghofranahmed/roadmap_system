@extends('adminlte::page')

@section('title', 'Create Learning Unit')

@section('content_header')
    <h1>Create Learning Unit</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Learning Unit</h3>
        </div>
        <form action="{{ route('admin.learning-units.store') }}" method="POST">
            @csrf
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
                            <option value="{{ $roadmap->id }}" {{ old('roadmap_id') == $roadmap->id ? 'selected' : '' }}>
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
                           value="{{ old('title') }}" 
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
                           value="{{ old('unit_type') }}" 
                           maxlength="255">
                    @error('unit_type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="number" 
                           class="form-control @error('position') is-invalid @enderror" 
                           id="position" 
                           name="position" 
                           value="{{ old('position') }}" 
                           min="1">
                    @error('position')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Leave empty to add at the end.</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input @error('is_active') is-invalid @enderror" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
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
                    <i class="fas fa-save"></i> Create Learning Unit
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

