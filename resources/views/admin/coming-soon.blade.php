@extends('adminlte::page')

@section('title', 'Coming Soon')

@section('content_header')
    <h1>Coming Soon</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-5x text-muted mb-4"></i>
                    <h3 class="mb-3">{{ $feature ?? 'This Feature' }} is Coming Soon</h3>
                    <p class="text-muted mb-4">
                        This feature is currently under development. 
                        Please check back later or use the API endpoints for now.
                    </p>
                    <a href="{{ $backUrl }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

