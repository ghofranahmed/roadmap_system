@extends('adminlte::page')

@section('title', 'Change Password')

@section('content_header')
    <h1><i class="fas fa-key mr-2"></i>Change Password</h1>
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
        <div class="col-md-8 offset-md-2">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lock mr-2"></i>Update Your Password</h3>
                </div>
                <form action="{{ route('admin.profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle mr-1"></i> Password Requirements</h5>
                            <ul class="mb-0">
                                <li>Minimum 8 characters</li>
                                <li>At least one uppercase letter (A-Z)</li>
                                <li>At least one lowercase letter (a-z)</li>
                                <li>At least one digit (0-9)</li>
                            </ul>
                        </div>

                        {{-- Current Password --}}
                        <div class="form-group">
                            <label for="current_password">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       id="current_password"
                                       name="current_password"
                                       required
                                       placeholder="Enter your current password">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        {{-- New Password --}}
                        <div class="form-group">
                            <label for="password">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       required
                                       minlength="8"
                                       placeholder="Enter new password">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Confirm New Password --}}
                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       required
                                       minlength="8"
                                       placeholder="Confirm new password">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Password Strength Indicator --}}
                        <div class="form-group">
                            <label>Password Strength</label>
                            <div class="progress" style="height: 8px;">
                                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="password-strength-text" class="form-text text-muted">Enter a password to see its strength.</small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i> Update Password
                        </button>
                        <a href="{{ route('admin.profile.show') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Profile
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

@section('css')
@stop

@section('js')
<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            var icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        var password = this.value;
        var strength = 0;
        var bar = document.getElementById('password-strength-bar');
        var text = document.getElementById('password-strength-text');

        if (password.length >= 8) strength += 25;
        if (/[a-z]/.test(password)) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;

        bar.style.width = strength + '%';

        if (strength <= 25) {
            bar.className = 'progress-bar bg-danger';
            text.textContent = 'Weak';
            text.className = 'form-text text-danger';
        } else if (strength <= 50) {
            bar.className = 'progress-bar bg-warning';
            text.textContent = 'Fair';
            text.className = 'form-text text-warning';
        } else if (strength <= 75) {
            bar.className = 'progress-bar bg-info';
            text.textContent = 'Good';
            text.className = 'form-text text-info';
        } else {
            bar.className = 'progress-bar bg-success';
            text.textContent = 'Strong';
            text.className = 'form-text text-success';
        }

        if (password.length === 0) {
            bar.style.width = '0%';
            text.textContent = 'Enter a password to see its strength.';
            text.className = 'form-text text-muted';
        }
    });
</script>
@stop

