@extends('adminlte::page')

@section('title', 'Create Quiz Question')

@section('content_header')
    <h1>Create Quiz Question</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Quiz Question</h3>
        </div>
        <form action="{{ route('admin.quiz-questions.store') }}" method="POST" id="quiz-question-form">
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

                <div class="form-group">
                    <label for="quiz_id">Quiz <span class="text-danger">*</span></label>
                    <select class="form-control @error('quiz_id') is-invalid @enderror" 
                            id="quiz_id" 
                            name="quiz_id" 
                            required>
                        <option value="">Select Quiz</option>
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}" {{ old('quiz_id', $selectedQuizId) == $quiz->id ? 'selected' : '' }}>
                                {{ $quiz->title ?? 'Quiz #' . $quiz->id }} ({{ $quiz->learningUnit->title ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('quiz_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="question_text">Question Text <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('question_text') is-invalid @enderror" 
                              id="question_text" 
                              name="question_text" 
                              rows="3" 
                              required 
                              maxlength="5000">{{ old('question_text') }}</textarea>
                    @error('question_text')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Options <span class="text-danger">*</span> (Minimum 2, Maximum 10)</label>
                    <div id="options-container">
                        @if(old('options'))
                            @foreach(old('options') as $index => $option)
                                <div class="input-group mb-2 option-item">
                                    <input type="text" 
                                           class="form-control option-input" 
                                           name="options[]" 
                                           value="{{ $option }}" 
                                           required 
                                           maxlength="500">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger remove-option" {{ count(old('options')) <= 2 ? 'disabled' : '' }}>
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="input-group mb-2 option-item">
                                <input type="text" 
                                       class="form-control option-input" 
                                       name="options[]" 
                                       placeholder="Option 1" 
                                       required 
                                       maxlength="500">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-option" disabled>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="input-group mb-2 option-item">
                                <input type="text" 
                                       class="form-control option-input" 
                                       name="options[]" 
                                       placeholder="Option 2" 
                                       required 
                                       maxlength="500">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-option" disabled>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="add-option">
                        <i class="fas fa-plus"></i> Add Option
                    </button>
                    @error('options')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    @error('options.*')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="correct_answer">Correct Answer <span class="text-danger">*</span></label>
                    <select class="form-control @error('correct_answer') is-invalid @enderror" 
                            id="correct_answer" 
                            name="correct_answer" 
                            required>
                        <option value="">Select Correct Answer</option>
                        @if(old('options'))
                            @foreach(old('options') as $option)
                                <option value="{{ $option }}" {{ old('correct_answer') == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('correct_answer')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">The correct answer must be one of the options above.</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="question_xp">Question XP</label>
                            <input type="number" 
                                   class="form-control @error('question_xp') is-invalid @enderror" 
                                   id="question_xp" 
                                   name="question_xp" 
                                   value="{{ old('question_xp', 0) }}" 
                                   min="0" 
                                   max="100">
                            @error('question_xp')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="order">Order</label>
                            <input type="number" 
                                   class="form-control @error('order') is-invalid @enderror" 
                                   id="order" 
                                   name="order" 
                                   value="{{ old('order') }}" 
                                   min="1">
                            @error('order')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Leave empty to add at the end.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Quiz Question
                </button>
                <a href="{{ route('admin.quiz-questions.index') }}" class="btn btn-default">
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
        document.addEventListener('DOMContentLoaded', function() {
            const optionsContainer = document.getElementById('options-container');
            const addOptionBtn = document.getElementById('add-option');
            const correctAnswerSelect = document.getElementById('correct_answer');

            // Add option
            addOptionBtn.addEventListener('click', function() {
                const optionCount = optionsContainer.querySelectorAll('.option-item').length;
                if (optionCount >= 10) {
                    alert('Maximum 10 options allowed');
                    return;
                }

                const newOption = document.createElement('div');
                newOption.className = 'input-group mb-2 option-item';
                newOption.innerHTML = `
                    <input type="text" 
                           class="form-control option-input" 
                           name="options[]" 
                           placeholder="Option ${optionCount + 1}" 
                           required 
                           maxlength="500">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-option">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                optionsContainer.appendChild(newOption);
                updateRemoveButtons();
                updateCorrectAnswerOptions();
            });

            // Remove option
            optionsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-option')) {
                    const optionItem = e.target.closest('.option-item');
                    optionItem.remove();
                    updateRemoveButtons();
                    updateCorrectAnswerOptions();
                }
            });

            // Update options in correct answer select when options change
            optionsContainer.addEventListener('input', function(e) {
                if (e.target.classList.contains('option-input')) {
                    updateCorrectAnswerOptions();
                }
            });

            function updateRemoveButtons() {
                const optionItems = optionsContainer.querySelectorAll('.option-item');
                optionItems.forEach(item => {
                    const removeBtn = item.querySelector('.remove-option');
                    removeBtn.disabled = optionItems.length <= 2;
                });
            }

            function updateCorrectAnswerOptions() {
                const options = Array.from(optionsContainer.querySelectorAll('.option-input'))
                    .map(input => input.value.trim())
                    .filter(val => val !== '');

                correctAnswerSelect.innerHTML = '<option value="">Select Correct Answer</option>';
                options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option;
                    optionElement.textContent = option;
                    if (correctAnswerSelect.dataset.selectedValue === option) {
                        optionElement.selected = true;
                    }
                    correctAnswerSelect.appendChild(optionElement);
                });
            }

            // Initialize
            updateRemoveButtons();
            if (correctAnswerSelect.value) {
                correctAnswerSelect.dataset.selectedValue = correctAnswerSelect.value;
            }
        });
    </script>
@stop

