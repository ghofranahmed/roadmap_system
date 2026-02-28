@extends('adminlte::page')

@section('title', 'System Settings')

@section('content_header')
    <h1>System Settings</h1>
    <p class="text-muted mb-0">Manage general system settings and branding</p>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">General Settings</h3>
        </div>
        <form action="{{ route('admin.system-settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="app_name">Application Name</label>
                    <input type="text" 
                           class="form-control @error('app_name') is-invalid @enderror" 
                           id="app_name" 
                           name="app_name" 
                           value="{{ old('app_name', $settings['app_name'] ?? config('adminlte.title', 'Admin Panel')) }}" 
                           maxlength="255">
                    <small class="form-text text-muted">This will be displayed in the admin panel title and browser tab.</small>
                    @error('app_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="support_email">Support Email</label>
                    <input type="email" 
                           class="form-control @error('support_email') is-invalid @enderror" 
                           id="support_email" 
                           name="support_email" 
                           value="{{ old('support_email', $settings['support_email'] ?? '') }}" 
                           maxlength="255">
                    <small class="form-text text-muted">Email address for user support inquiries.</small>
                    @error('support_email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="maintenance_message">Maintenance Message</label>
                    <textarea class="form-control @error('maintenance_message') is-invalid @enderror" 
                              id="maintenance_message" 
                              name="maintenance_message" 
                              rows="3"
                              maxlength="1000">{{ old('maintenance_message', $settings['maintenance_message'] ?? '') }}</textarea>
                    <small class="form-text text-muted">Message to display during maintenance mode (if applicable).</small>
                    @error('maintenance_message')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Branding</h3>
        </div>
        <div class="card-body">
            {{-- Logo Upload --}}
            <div class="form-group">
                <label>Application Logo</label>
                <div class="mb-2">
                    @if($settings['app_logo'] && \Storage::disk('public')->exists($settings['app_logo']))
                        <img src="{{ \Storage::disk('public')->url($settings['app_logo']) }}" 
                             alt="Current Logo" 
                             style="max-height: 100px; max-width: 200px;"
                             class="img-thumbnail mb-2">
                        <br>
                    @else
                        <p class="text-muted">No logo uploaded</p>
                    @endif
                </div>
                <form action="{{ route('admin.system-settings.upload-logo') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="d-inline">
                    @csrf
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" 
                                   class="custom-file-input @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml">
                            <label class="custom-file-label" for="logo">Choose logo file</label>
                        </div>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Logo
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF, SVG. Max size: 2MB</small>
                    @error('logo')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </form>
            </div>

            <hr>

            {{-- Favicon Upload --}}
            <div class="form-group">
                <label>Favicon</label>
                <div class="mb-2">
                    @if($settings['app_favicon'] && \Storage::disk('public')->exists($settings['app_favicon']))
                        <img src="{{ \Storage::disk('public')->url($settings['app_favicon']) }}" 
                             alt="Current Favicon" 
                             style="max-height: 32px; max-width: 32px;"
                             class="img-thumbnail mb-2">
                        <br>
                    @else
                        <p class="text-muted">No favicon uploaded</p>
                    @endif
                </div>
                <form action="{{ route('admin.system-settings.upload-favicon') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="d-inline">
                    @csrf
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" 
                                   class="custom-file-input @error('favicon') is-invalid @enderror" 
                                   id="favicon" 
                                   name="favicon" 
                                   accept="image/x-icon,image/png">
                            <label class="custom-file-label" for="favicon">Choose favicon file</label>
                        </div>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Favicon
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Accepted formats: ICO, PNG. Max size: 512KB. Recommended size: 32x32px</small>
                    @error('favicon')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
    // Update file input labels
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choose file';
            const label = e.target.nextElementSibling;
            if (label) {
                label.textContent = fileName;
            }
        });
    });
</script>
@stop

