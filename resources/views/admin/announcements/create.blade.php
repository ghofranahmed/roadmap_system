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

                <hr>

                <h5>Notification Settings</h5>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="send_notification" 
                               name="send_notification" 
                               value="1" 
                               {{ old('send_notification') ? 'checked' : '' }}
                               onchange="toggleNotificationOptions()">
                        <label class="form-check-label" for="send_notification">
                            Send notification when publishing
                        </label>
                    </div>
                    <small class="form-text text-muted">If enabled, notifications will be sent to targeted users when the announcement is published.</small>
                </div>

                <div id="notification_options" style="display: {{ old('send_notification') ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label for="target_type">Target Audience <span class="text-danger">*</span></label>
                        <select class="form-control @error('target_type') is-invalid @enderror" 
                                id="target_type" 
                                name="target_type" 
                                onchange="toggleTargetRules()">
                            <option value="all" {{ old('target_type', 'all') == 'all' ? 'selected' : '' }}>All Users</option>
                            <option value="specific_users" {{ old('target_type') == 'specific_users' ? 'selected' : '' }}>Specific Users</option>
                            <option value="inactive_users" {{ old('target_type') == 'inactive_users' ? 'selected' : '' }}>Inactive Users (7+ days)</option>
                            <option value="low_progress" {{ old('target_type') == 'low_progress' ? 'selected' : '' }}>Low Progress Users</option>
                        </select>
                        @error('target_type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div id="specific_users_section" style="display: {{ old('target_type') == 'specific_users' ? 'block' : 'none' }};">
                        <div class="form-group">
                            <label for="target_rules">Select Users</label>
                            <select class="form-control select2" 
                                    id="target_rules" 
                                    name="target_rules[]" 
                                    multiple>
                                @foreach(\App\Models\User::where('role', 'user')->where('is_notifications_enabled', true)->orderBy('username')->get() as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, old('target_rules', [])) ? 'selected' : '' }}>
                                        {{ $user->username }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('target_rules')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Select one or more users to receive this notification.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                    <i class="fas fa-save"></i> Save as Draft
                </button>
                <button type="submit" name="publish" value="1" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Publish Announcement
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
        function toggleNotificationOptions() {
            const checkbox = document.getElementById('send_notification');
            const options = document.getElementById('notification_options');
            options.style.display = checkbox.checked ? 'block' : 'none';
            
            if (!checkbox.checked) {
                document.getElementById('target_type').value = 'all';
                toggleTargetRules();
            }
        }

        function toggleTargetRules() {
            const targetType = document.getElementById('target_type').value;
            const specificSection = document.getElementById('specific_users_section');
            specificSection.style.display = targetType === 'specific_users' ? 'block' : 'none';
        }

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

            // Initialize Select2 if available
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#target_rules').select2({
                    placeholder: 'Select users...',
                    allowClear: true
                });
            }
        });
    </script>
@stop

