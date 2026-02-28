@extends('adminlte::page')

@section('title', 'Quiz Question Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quiz Question Details</h1>
        <div>
            <a href="{{ route('admin.quiz-questions.edit', $quizQuestion) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.quiz-questions.index') }}" class="btn btn-default">
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
                    <h3 class="card-title">Question #{{ $quizQuestion->id }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $quizQuestion->id }}</dd>

                        <dt class="col-sm-3">Question Text:</dt>
                        <dd class="col-sm-9">{{ $quizQuestion->question_text }}</dd>

                        <dt class="col-sm-3">Quiz:</dt>
                        <dd class="col-sm-9">{{ $quizQuestion->quiz->title ?? 'Quiz #' . $quizQuestion->quiz_id }}</dd>

                        <dt class="col-sm-3">Options:</dt>
                        <dd class="col-sm-9">
                            <ul class="list-unstyled">
                                @foreach($quizQuestion->options ?? [] as $index => $option)
                                    <li>
                                        <span class="badge badge-{{ $option === $quizQuestion->correct_answer ? 'success' : 'secondary' }}">
                                            {{ $index + 1 }}. {{ $option }}
                                            @if($option === $quizQuestion->correct_answer)
                                                <i class="fas fa-check"></i> Correct
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </dd>

                        <dt class="col-sm-3">Correct Answer:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-success">{{ $quizQuestion->correct_answer }}</span>
                        </dd>

                        <dt class="col-sm-3">Question XP:</dt>
                        <dd class="col-sm-9">{{ $quizQuestion->question_xp ?? 0 }}</dd>

                        <dt class="col-sm-3">Order:</dt>
                        <dd class="col-sm-9">{{ $quizQuestion->order }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.quiz-questions.destroy', $quizQuestion) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this quiz question? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Question
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

