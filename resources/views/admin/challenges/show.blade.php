@extends('adminlte::page')

@section('title', 'Challenge Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Challenge Details</h1>
        <div>
            <a href="{{ route('admin.challenges.edit', $challenge) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.challenges.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $challenge->title }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $challenge->id }}</dd>

                        <dt class="col-sm-3">Title:</dt>
                        <dd class="col-sm-9">{{ $challenge->title }}</dd>

                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{{ $challenge->description ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Learning Unit:</dt>
                        <dd class="col-sm-9">{{ $challenge->learningUnit->title ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Language:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-info">{{ ucfirst($challenge->language) }}</span>
                        </dd>

                        <dt class="col-sm-3">Minimum XP:</dt>
                        <dd class="col-sm-9">{{ $challenge->min_xp }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-{{ $challenge->is_active ? 'success' : 'secondary' }}">
                                {{ $challenge->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Test Cases:</dt>
                        <dd class="col-sm-9">{{ count($challenge->test_cases ?? []) }}</dd>

                        <dt class="col-sm-3">Attempts:</dt>
                        <dd class="col-sm-9">{{ $challenge->attempts->count() ?? 0 }}</dd>
                    </dl>

                    @if($challenge->starter_code)
                        <hr>
                        <h5>Starter Code</h5>
                        <pre class="bg-light p-3"><code>{{ $challenge->starter_code }}</code></pre>
                    @endif

                    @if($challenge->test_cases && count($challenge->test_cases) > 0)
                        <hr>
                        <h5>Test Cases</h5>
                        @foreach($challenge->test_cases as $index => $testCase)
                            <div class="card mb-2">
                                <div class="card-header">
                                    <strong>Test Case {{ $index + 1 }}</strong>
                                </div>
                                <div class="card-body">
                                    @if(!empty($testCase['stdin']))
                                        <div class="mb-2">
                                            <strong>Input:</strong>
                                            <pre class="bg-light p-2 mb-0">{{ $testCase['stdin'] }}</pre>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>Expected Output:</strong>
                                        <pre class="bg-light p-2 mb-0">{{ $testCase['expected_output'] }}</pre>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.challenges.toggle-active', $challenge) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $challenge->is_active ? 'secondary' : 'success' }} btn-block">
                            <i class="fas fa-toggle-{{ $challenge->is_active ? 'on' : 'off' }}"></i> 
                            {{ $challenge->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.challenges.destroy', $challenge) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this challenge? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Challenge
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

