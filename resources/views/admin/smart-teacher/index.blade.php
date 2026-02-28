@extends('adminlte::page')

@section('title', 'Smart Teacher Settings')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Smart Teacher (Chatbot) Management</h1>
            <p class="text-muted mb-0">Configure and monitor the Smart Teacher chatbot feature</p>
        </div>
        <a href="{{ route('admin.smart-teacher.logs') }}" class="btn btn-info">
            <i class="fas fa-list"></i> View Logs
        </a>
    </div>
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

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-comments"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Sessions</span>
                    <span class="info-box-number">{{ number_format($totalSessions) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-envelope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Messages</span>
                    <span class="info-box-number">{{ number_format($totalMessages) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active (Last 7 Days)</span>
                    <span class="info-box-number">{{ number_format($activeSessions) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chatbot Configuration</h3>
        </div>
        <form action="{{ route('admin.smart-teacher.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" 
                               class="custom-control-input" 
                               id="is_enabled" 
                               name="is_enabled" 
                               value="1"
                               {{ $settings->is_enabled ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_enabled">
                            <strong>Enable Smart Teacher</strong>
                            <small class="text-muted d-block">When disabled, users will see a message that Smart Teacher is temporarily unavailable.</small>
                        </label>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="provider">AI Provider <span class="text-danger">*</span></label>
                    <select class="form-control @error('provider') is-invalid @enderror" 
                            id="provider" 
                            name="provider" 
                            required>
                        <option value="openai" {{ old('provider', $settings->provider) == 'openai' ? 'selected' : '' }}>OpenAI</option>
                        <option value="groq" {{ old('provider', $settings->provider) == 'groq' ? 'selected' : '' }}>Groq</option>
                        <option value="gemini" {{ old('provider', $settings->provider) == 'gemini' ? 'selected' : '' }}>Google Gemini</option>
                        <option value="dummy" {{ old('provider', $settings->provider) == 'dummy' ? 'selected' : '' }}>Dummy (Testing)</option>
                    </select>
                    @error('provider')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="model_name">Model Name</label>
                    <input type="text" 
                           class="form-control @error('model_name') is-invalid @enderror" 
                           id="model_name" 
                           name="model_name" 
                           value="{{ old('model_name', $settings->model_name) }}" 
                           placeholder="Leave empty to use provider default">
                    <small class="form-text text-muted">Override the default model for the selected provider. Leave empty to use provider defaults.</small>
                    @error('model_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="temperature">Temperature <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('temperature') is-invalid @enderror" 
                                   id="temperature" 
                                   name="temperature" 
                                   value="{{ old('temperature', $settings->temperature) }}" 
                                   step="0.01" 
                                   min="0" 
                                   max="2" 
                                   required>
                            <small class="form-text text-muted">Controls randomness (0.0 = deterministic, 2.0 = very creative). Recommended: 0.7</small>
                            @error('temperature')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_tokens">Max Tokens <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('max_tokens') is-invalid @enderror" 
                                   id="max_tokens" 
                                   name="max_tokens" 
                                   value="{{ old('max_tokens', $settings->max_tokens) }}" 
                                   min="1" 
                                   max="10000" 
                                   required>
                            <small class="form-text text-muted">Maximum tokens in the response. Recommended: 1000</small>
                            @error('max_tokens')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_context_messages">Max Context Messages <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('max_context_messages') is-invalid @enderror" 
                                   id="max_context_messages" 
                                   name="max_context_messages" 
                                   value="{{ old('max_context_messages', $settings->max_context_messages) }}" 
                                   min="1" 
                                   max="100" 
                                   required>
                            <small class="form-text text-muted">Number of previous messages to include in context. Recommended: 10</small>
                            @error('max_context_messages')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="request_timeout">Request Timeout (seconds) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('request_timeout') is-invalid @enderror" 
                                   id="request_timeout" 
                                   name="request_timeout" 
                                   value="{{ old('request_timeout', $settings->request_timeout) }}" 
                                   min="1" 
                                   max="300" 
                                   required>
                            <small class="form-text text-muted">Timeout for API requests. Recommended: 15</small>
                            @error('request_timeout')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="system_prompt_template">System Prompt Template</label>
                    <textarea class="form-control @error('system_prompt_template') is-invalid @enderror" 
                              id="system_prompt_template" 
                              name="system_prompt_template" 
                              rows="6"
                              maxlength="5000">{{ old('system_prompt_template', $settings->system_prompt_template) }}</textarea>
                    <small class="form-text text-muted">Custom system prompt. Leave empty to use default. Max 5000 characters.</small>
                    @error('system_prompt_template')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                @if($settings->updated_by && $settings->updater)
                    <div class="form-group">
                        <small class="text-muted">
                            Last updated by: <strong>{{ $settings->updater->username }}</strong> 
                            on {{ $settings->updated_at->format('Y-m-d H:i:s') }}
                        </small>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
                <a href="{{ route('admin.smart-teacher.logs') }}" class="btn btn-default">
                    <i class="fas fa-list"></i> View Logs
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

