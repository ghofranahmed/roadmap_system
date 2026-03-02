@extends('adminlte::page')

@section('title', 'Edit Profile')

@section('content_header')
    <h1><i class="fas fa-user-edit mr-2"></i>Edit Profile</h1>
@stop

@section('content')

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="fas fa-exclamation-triangle mr-1"></i> Please fix the following errors:</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Avatar Preview Card --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-camera mr-2"></i>Profile Picture</h3>
                </div>
                <div class="card-body text-center">
                    <img id="avatar-preview"
                         class="img-circle elevation-2 mb-3"
                         src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                         alt="Avatar Preview"
                         style="width: 150px; height: 150px; object-fit: cover;">

                    <p class="text-muted">
                        <small>Allowed: JPG, JPEG, PNG, WEBP. Max 2MB.</small>
                    </p>

                    <div class="form-group">
                        <label for="avatar" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-upload mr-1"></i> Choose New Photo
                        </label>
                        <input type="file"
                               id="avatar"
                               name="avatar"
                               form="profile-form"
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               class="d-none"
                               onchange="previewImage(event)">
                    </div>

                    @if($user->profile_picture)
                        <p class="text-success mb-0">
                            <i class="fas fa-check-circle mr-1"></i>
                            <small>Current photo will be replaced on upload.</small>
                        </p>
                    @endif
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-body text-center">
                    <a href="{{ route('admin.profile.password') }}" class="btn btn-warning btn-block">
                        <i class="fas fa-key mr-1"></i> Change Password
                    </a>
                </div>
            </div>
        </div>

        {{-- Profile Edit Form --}}
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Profile Information</h3>
                </div>
                <form id="profile-form" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        {{-- Username --}}
                        <div class="form-group">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text"
                                       class="form-control @error('username') is-invalid @enderror"
                                       id="username"
                                       name="username"
                                       value="{{ old('username', $user->username) }}"
                                       required
                                       minlength="3"
                                       maxlength="60"
                                       placeholder="Enter username">
                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Letters, numbers, underscores, and dots only. 3-60 characters.</small>
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       required
                                       maxlength="120"
                                       placeholder="Enter email address">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Role (read-only) --}}
                        <div class="form-group">
                            <label>Role</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                </div>
                                <input type="text"
                                       class="form-control"
                                       value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}"
                                       disabled>
                            </div>
                            <small class="form-text text-muted">Role cannot be changed from this page.</small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.profile.show') }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

@section('css')
<style>
    .img-circle {
        border: 3px solid #adb5bd;
    }
</style>
@stop

@section('js')
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB.');
                event.target.value = '';
                return;
            }

            // Validate file type
            const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowed.includes(file.type)) {
                alert('Only JPG, JPEG, PNG, and WEBP files are allowed.');
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@stop

