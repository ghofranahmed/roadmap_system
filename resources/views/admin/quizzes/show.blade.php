@extends('adminlte::page')

@section('title', 'Quiz Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quiz Details</h1>
        <div>
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.quizzes.index') }}" class="btn btn-default">
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
                    <h3 class="card-title">{{ $quiz->title ?? 'Quiz #' . $quiz->id }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $quiz->id }}</dd>

                        <dt class="col-sm-3">Title:</dt>
                        <dd class="col-sm-9">{{ $quiz->title ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Learning Unit:</dt>
                        <dd class="col-sm-9">{{ $quiz->learningUnit->title ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Minimum XP:</dt>
                        <dd class="col-sm-9">{{ $quiz->min_xp }}</dd>

                        <dt class="col-sm-3">Maximum XP:</dt>
                        <dd class="col-sm-9">{{ $quiz->max_xp }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-{{ $quiz->is_active ? 'success' : 'secondary' }}">
                                {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Questions:</dt>
                        <dd class="col-sm-9">{{ $quiz->questions->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Attempts:</dt>
                        <dd class="col-sm-9">{{ $quiz->attempts->count() ?? 0 }}</dd>

                        <dt class="col-sm-3">Created At:</dt>
                        <dd class="col-sm-9">{{ $quiz->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Updated At:</dt>
                        <dd class="col-sm-9">{{ $quiz->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>

            @if($quiz->questions && $quiz->questions->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Questions</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Question</th>
                                    <th>XP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quiz->questions as $question)
                                    <tr>
                                        <td>{{ $question->order }}</td>
                                        <td>{{ Str::limit($question->question_text, 100) }}</td>
                                        <td>{{ $question->question_xp ?? 0 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.quiz-questions.create', ['quiz' => $quiz->id]) }}" class="btn btn-success btn-block mb-2">
                        <i class="fas fa-plus"></i> Add Question
                    </a>
                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this quiz? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Quiz
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

