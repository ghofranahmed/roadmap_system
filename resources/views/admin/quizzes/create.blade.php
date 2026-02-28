@extends('adminlte::page')

@section('title', 'Create Quiz')

@section('content_header')
    <h1>Create Quiz</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Quiz</h3>
        </div>
        <form action="{{ route('admin.quizzes.store') }}" method="POST">
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
                    <label for="learning_unit_id">Learning Unit <span class="text-danger">*</span></label>
                    <select class="form-control @error('learning_unit_id') is-invalid @enderror" 
                            id="learning_unit_id" 
                            name="learning_unit_id" 
                            required>
                        <option value="">Select Learning Unit</option>
                        @foreach($learningUnits as $unit)
                            <option value="{{ $unit->id }}" {{ old('learning_unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->title }} ({{ $unit->roadmap->title ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('learning_unit_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" 
                           class="form-control @error('title') is-invalid @enderror" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}" 
                           maxlength="255">
                    @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Optional title for the quiz.</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="min_xp">Minimum XP <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('min_xp') is-invalid @enderror" 
                                   id="min_xp" 
                                   name="min_xp" 
                                   value="{{ old('min_xp', 0) }}" 
                                   min="0"
                                   required>
                            @error('min_xp')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_xp">Maximum XP <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('max_xp') is-invalid @enderror" 
                                   id="max_xp" 
                                   name="max_xp" 
                                   value="{{ old('max_xp', 0) }}" 
                                   min="0"
                                   required>
                            @error('max_xp')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
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
                    <i class="fas fa-save"></i> Create Quiz
                </button>
                <a href="{{ route('admin.quizzes.index') }}" class="btn btn-default">
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

