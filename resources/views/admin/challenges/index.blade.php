@extends('adminlte::page')

@section('title', 'Challenges')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Challenges</h1>
        <a href="{{ route('admin.challenges.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Challenge
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
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Challenges</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.challenges.index') }}" class="form-inline">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    @if(isset($learningUnits) && $learningUnits->count() > 0)
                        <select name="learning_unit_id" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                            <option value="">All Learning Units</option>
                            @foreach($learningUnits as $unit)
                                <option value="{{ $unit->id }}" {{ (string) $unit->id === request('learning_unit_id') ? 'selected' : '' }}>
                                    {{ $unit->title }} ({{ $unit->roadmap->title ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <select name="language" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Languages</option>
                        <option value="javascript" {{ request('language') == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                        <option value="python" {{ request('language') == 'python' ? 'selected' : '' }}>Python</option>
                        <option value="java" {{ request('language') == 'java' ? 'selected' : '' }}>Java</option>
                        <option value="c" {{ request('language') == 'c' ? 'selected' : '' }}>C</option>
                        <option value="cpp" {{ request('language') == 'cpp' ? 'selected' : '' }}>C++</option>
                    </select>
                    <select name="is_active" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Learning Unit</th>
                        <th>Language</th>
                        <th>Min XP</th>
                        <th>Test Cases</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($challenges as $challenge)
                        <tr>
                            <td>{{ $challenge->id }}</td>
                            <td>{{ Str::limit($challenge->title, 50) }}</td>
                            <td>{{ $challenge->learningUnit->title ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ ucfirst($challenge->language) }}
                                </span>
                            </td>
                            <td>{{ $challenge->min_xp }}</td>
                            <td>{{ count($challenge->test_cases ?? []) }}</td>
                            <td>
                                <span class="badge badge-{{ $challenge->is_active ? 'success' : 'secondary' }}">
                                    {{ $challenge->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $challenge->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.challenges.show', $challenge) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.challenges.edit', $challenge) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.challenges.toggle-active', $challenge) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $challenge->is_active ? 'secondary' : 'success' }}">
                                            <i class="fas fa-toggle-{{ $challenge->is_active ? 'on' : 'off' }}"></i> {{ $challenge->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.challenges.destroy', $challenge) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this challenge?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No challenges found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $challenges->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

