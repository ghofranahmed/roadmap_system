@extends('adminlte::page')

@section('title', 'Edit Resource')

@section('content_header')
    <h1>Edit Resource</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Resource #{{ $resource->id }}</h3>
        </div>
        <form action="{{ route('admin.resources.update', $resource) }}" method="POST">
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
                    <label for="sub_lesson_id">Sub-Lesson <span class="text-danger">*</span></label>
                    <select class="form-control @error('sub_lesson_id') is-invalid @enderror" 
                            id="sub_lesson_id" 
                            name="sub_lesson_id" 
                            required>
                        <option value="">Select Sub-Lesson</option>
                        @foreach($subLessons as $subLesson)
                            <option value="{{ $subLesson->id }}" {{ old('sub_lesson_id', $resource->sub_lesson_id) == $subLesson->id ? 'selected' : '' }}>
                                {{ Str::limit($subLesson->description, 50) }} ({{ $subLesson->lesson->title ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('sub_lesson_id')
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
                           value="{{ old('title', $resource->title) }}" 
                           required 
                           maxlength="255">
                    @error('title')
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
                        <option value="book" {{ old('type', $resource->type) == 'book' ? 'selected' : '' }}>Book</option>
                        <option value="video" {{ old('type', $resource->type) == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="article" {{ old('type', $resource->type) == 'article' ? 'selected' : '' }}>Article</option>
                    </select>
                    @error('type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="language">Language <span class="text-danger">*</span></label>
                    <select class="form-control @error('language') is-invalid @enderror" 
                            id="language" 
                            name="language" 
                            required>
                        <option value="">Select Language</option>
                        <option value="ar" {{ old('language', $resource->language) == 'ar' ? 'selected' : '' }}>Arabic</option>
                        <option value="en" {{ old('language', $resource->language) == 'en' ? 'selected' : '' }}>English</option>
                    </select>
                    @error('language')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="link">Link <span class="text-danger">*</span></label>
                    <input type="url" 
                           class="form-control @error('link') is-invalid @enderror" 
                           id="link" 
                           name="link" 
                           value="{{ old('link', $resource->link) }}" 
                           required>
                    @error('link')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Resource
                </button>
                <a href="{{ route('admin.resources.index') }}" class="btn btn-default">
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

