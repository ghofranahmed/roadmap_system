@extends('adminlte::page')

@section('title', 'Create Notification')

@section('content_header')
    <h1>Create Notification</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Notification</h3>
        </div>
        <form action="{{ route('admin.notifications.store') }}" method="POST">
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

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                {{-- Title --}}
                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control @error('title') is-invalid @enderror"
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
                           required
                           maxlength="255"
                           placeholder="Enter notification title">
                    @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Message --}}
                <div class="form-group">
                    <label for="message">Message <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('message') is-invalid @enderror"
                              id="message"
                              name="message"
                              rows="4"
                              required
                              placeholder="Enter notification message">{{ old('message') }}</textarea>
                    @error('message')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="row">
                    {{-- Type --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror"
                                    id="type"
                                    name="type"
                                    required>
                                <option value="general" {{ old('type', 'general') == 'general' ? 'selected' : '' }}>General</option>
                                <option value="announcement" {{ old('type') == 'announcement' ? 'selected' : '' }}>Announcement</option>
                                <option value="reminder" {{ old('type') == 'reminder' ? 'selected' : '' }}>Reminder</option>
                                <option value="system" {{ old('type') == 'system' ? 'selected' : '' }}>System</option>
                                <option value="alert" {{ old('type') == 'alert' ? 'selected' : '' }}>Alert</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Priority --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="priority">Priority <span class="text-danger">*</span></label>
                            <select class="form-control @error('priority') is-invalid @enderror"
                                    id="priority"
                                    name="priority"
                                    required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                            @error('priority')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>
                <h5><i class="fas fa-paper-plane mr-1"></i> Delivery Settings</h5>

                {{-- Delivery Type --}}
                <div class="form-group">
                    <label for="delivery_type">Delivery Type <span class="text-danger">*</span></label>
                    <select class="form-control @error('delivery_type') is-invalid @enderror"
                            id="delivery_type"
                            name="delivery_type"
                            required
                            onchange="toggleDeliveryOptions()">
                        <option value="single" {{ old('delivery_type', 'single') == 'single' ? 'selected' : '' }}>Single User</option>
                        <option value="broadcast" {{ old('delivery_type') == 'broadcast' ? 'selected' : '' }}>Broadcast (All Users)</option>
                        <option value="targeted" {{ old('delivery_type') == 'targeted' ? 'selected' : '' }}>Targeted (Select Users)</option>
                    </select>
                    @error('delivery_type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">
                        <strong>Single:</strong> Send to one user.
                        <strong>Broadcast:</strong> Send to all eligible users.
                        <strong>Targeted:</strong> Send to selected users.
                    </small>
                </div>

                {{-- Single User Selection --}}
                <div id="single_user_section" style="display: {{ old('delivery_type', 'single') == 'single' ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label for="user_id">Select User <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('user_id') is-invalid @enderror"
                                id="user_id"
                                name="user_id">
                            <option value="">-- Select a User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->username }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                {{-- Targeted Users Selection --}}
                <div id="targeted_users_section" style="display: {{ old('delivery_type') == 'targeted' ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label for="user_ids">Select Users <span class="text-danger">*</span></label>
                        <select class="form-control select2-multiple @error('user_ids') is-invalid @enderror"
                                id="user_ids"
                                name="user_ids[]"
                                multiple>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'selected' : '' }}>
                                    {{ $user->username }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_ids')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">Select one or more users to receive the notification.</small>
                    </div>
                </div>

                {{-- Broadcast Info --}}
                <div id="broadcast_info" style="display: {{ old('delivery_type') == 'broadcast' ? 'block' : 'none' }};">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle"></i> Broadcast Notification</h5>
                        <p>This notification will be sent to <strong>all regular users</strong> who have notifications enabled. Per-user notification records will be created.</p>
                    </div>
                </div>

                <hr>
                <h5><i class="fas fa-cog mr-1"></i> Optional Settings</h5>

                <div class="row">
                    {{-- Link to Announcement --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="announcement_id">Link to Announcement (Optional)</label>
                            <select class="form-control @error('announcement_id') is-invalid @enderror"
                                    id="announcement_id"
                                    name="announcement_id">
                                <option value="">-- None --</option>
                                @foreach($announcements as $announcement)
                                    <option value="{{ $announcement->id }}" {{ old('announcement_id') == $announcement->id ? 'selected' : '' }}>
                                        #{{ $announcement->id }} - {{ Str::limit($announcement->title, 60) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('announcement_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Optionally link this notification to an existing announcement.</small>
                        </div>
                    </div>

                    {{-- Schedule --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="scheduled_at">Schedule Time (Optional)</label>
                            <input type="datetime-local"
                                   class="form-control @error('scheduled_at') is-invalid @enderror"
                                   id="scheduled_at"
                                   name="scheduled_at"
                                   value="{{ old('scheduled_at') }}">
                            @error('scheduled_at')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Leave blank to send immediately.</small>
                        </div>
                    </div>
                </div>

                {{-- Metadata --}}
                <div class="form-group">
                    <label for="metadata">Metadata (Optional JSON)</label>
                    <textarea class="form-control @error('metadata') is-invalid @enderror"
                              id="metadata"
                              name="metadata"
                              rows="3"
                              placeholder='{"key": "value"}'>{{ old('metadata') }}</textarea>
                    @error('metadata')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Optional JSON data to store with the notification (e.g., action URLs, extra context).</small>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </button>
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-default">
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
        function toggleDeliveryOptions() {
            const deliveryType = document.getElementById('delivery_type').value;

            document.getElementById('single_user_section').style.display =
                deliveryType === 'single' ? 'block' : 'none';
            document.getElementById('targeted_users_section').style.display =
                deliveryType === 'targeted' ? 'block' : 'none';
            document.getElementById('broadcast_info').style.display =
                deliveryType === 'broadcast' ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 if available
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#user_id').select2({
                    placeholder: 'Search for a user...',
                    allowClear: true
                });
                $('#user_ids').select2({
                    placeholder: 'Select users...',
                    allowClear: true
                });
                $('#announcement_id').select2({
                    placeholder: 'Select an announcement...',
                    allowClear: true
                });
            }
        });
    </script>
@stop

