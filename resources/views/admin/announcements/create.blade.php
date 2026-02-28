@extends('adminlte::page')

@section('title', 'Create Announcement')

@section('content_header')
    <h1>Create Announcement</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Announcement</h3>
        </div>
        <form action="{{ route('admin.announcements.store') }}" method="POST">
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
                    <label for="description">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="4" 
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type">Type <span class="text-danger">*</span></label>
                    <select class="form-control @error('type') is-invalid @enderror" 
                            id="type" 
                            name="type" 
                            required>
                        <option value="">Select Type</option>
                        <option value="general" {{ old('type', 'general') == 'general' ? 'selected' : '' }}>General</option>
                        <option value="technical" {{ old('type') == 'technical' ? 'selected' : '' }}>Technical</option>
                        <option value="opportunity" {{ old('type') == 'opportunity' ? 'selected' : '' }}>Opportunity</option>
                    </select>
                    @error('type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="link">Link</label>
                    <input type="url" 
                           class="form-control @error('link') is-invalid @enderror" 
                           id="link" 
                           name="link" 
                           value="{{ old('link') }}" 
                           maxlength="255">
                    @error('link')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Optional URL for the announcement.</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="starts_at">Starts At</label>
                            <input type="datetime-local" 
                                   class="form-control @error('starts_at') is-invalid @enderror" 
                                   id="starts_at" 
                                   name="starts_at" 
                                   value="{{ old('starts_at') }}">
                            @error('starts_at')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ends_at">Ends At</label>
                            <input type="datetime-local" 
                                   class="form-control @error('ends_at') is-invalid @enderror" 
                                   id="ends_at" 
                                   name="ends_at" 
                                   value="{{ old('ends_at') }}">
                            @error('ends_at')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Must be after or equal to start date.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Announcement
                </button>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-default">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
@stop

@section('js')
    <script>
        // Convert datetime-local format for display
        document.addEventListener('DOMContentLoaded', function() {
            const startsAt = document.getElementById('starts_at');
            const endsAt = document.getElementById('ends_at');
            
            if (startsAt && startsAt.value) {
                const date = new Date(startsAt.value);
                startsAt.value = date.toISOString().slice(0, 16);
            }
            
            if (endsAt && endsAt.value) {
                const date = new Date(endsAt.value);
                endsAt.value = date.toISOString().slice(0, 16);
            }
        });
    </script>
@stop

