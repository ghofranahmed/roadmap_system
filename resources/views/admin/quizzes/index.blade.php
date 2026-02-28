@extends('adminlte::page')

@section('title', 'Quizzes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quizzes</h1>
        <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Quiz
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
            <h3 class="card-title">All Quizzes</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.quizzes.index') }}" class="form-inline">
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
                        <th>Min XP</th>
                        <th>Max XP</th>
                        <th>Questions</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $quiz)
                        <tr>
                            <td>{{ $quiz->id }}</td>
                            <td>{{ $quiz->title ?? 'N/A' }}</td>
                            <td>{{ $quiz->learningUnit->title ?? 'N/A' }}</td>
                            <td>{{ $quiz->min_xp }}</td>
                            <td>{{ $quiz->max_xp }}</td>
                            <td>{{ $quiz->questions->count() ?? 0 }}</td>
                            <td>
                                <span class="badge badge-{{ $quiz->is_active ? 'success' : 'secondary' }}">
                                    {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $quiz->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quiz?');">
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
                            <td colspan="9" class="text-center">No quizzes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $quizzes->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

