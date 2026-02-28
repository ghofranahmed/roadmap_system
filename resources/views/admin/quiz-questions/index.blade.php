@extends('adminlte::page')

@section('title', 'Quiz Questions')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quiz Questions</h1>
        <a href="{{ route('admin.quiz-questions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Quiz Question
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
            <h3 class="card-title">All Quiz Questions</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.quiz-questions.index') }}" class="form-inline">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    @if(isset($quizzes) && $quizzes->count() > 0)
                        <select name="quiz_id" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                            <option value="">All Quizzes</option>
                            @foreach($quizzes as $quiz)
                                <option value="{{ $quiz->id }}" {{ (string) $quiz->id === request('quiz_id') ? 'selected' : '' }}>
                                    {{ $quiz->title ?? 'Quiz #' . $quiz->id }} ({{ $quiz->learningUnit->title ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Question</th>
                        <th>Quiz</th>
                        <th>Options</th>
                        <th>Correct Answer</th>
                        <th>XP</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $question)
                        <tr>
                            <td>{{ $question->id }}</td>
                            <td>{{ Str::limit($question->question_text, 80) }}</td>
                            <td>{{ $question->quiz->title ?? 'Quiz #' . $question->quiz_id }}</td>
                            <td>{{ count($question->options ?? []) }} options</td>
                            <td>{{ Str::limit($question->correct_answer, 50) }}</td>
                            <td>{{ $question->question_xp ?? 0 }}</td>
                            <td>{{ $question->order }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.quiz-questions.show', $question) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.quiz-questions.edit', $question) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.quiz-questions.destroy', $question) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quiz question?');">
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
                            <td colspan="8" class="text-center">No quiz questions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $questions->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

