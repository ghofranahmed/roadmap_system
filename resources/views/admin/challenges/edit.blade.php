@extends('adminlte::page')

@section('title', 'Edit Challenge')

@section('content_header')
    <h1>Edit Challenge</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Challenge #{{ $challenge->id }}</h3>
        </div>
        <form action="{{ route('admin.challenges.update', $challenge) }}" method="POST" id="challenge-form">
            @csrf
            @method('PUT')
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
                    <label for="learning_unit_id">Learning Unit <span class="text-danger">*</span></label>
                    <select class="form-control @error('learning_unit_id') is-invalid @enderror" 
                            id="learning_unit_id" 
                            name="learning_unit_id" 
                            required>
                        <option value="">Select Learning Unit</option>
                        @foreach($learningUnits as $unit)
                            <option value="{{ $unit->id }}" {{ old('learning_unit_id', $challenge->learning_unit_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->title }} ({{ $unit->roadmap->title ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('learning_unit_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('title') is-invalid @enderror" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $challenge->title) }}" 
                           required 
                           maxlength="255">
                    @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="4">{{ old('description', $challenge->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="min_xp">Minimum XP <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('min_xp') is-invalid @enderror" 
                                   id="min_xp" 
                                   name="min_xp" 
                                   value="{{ old('min_xp', $challenge->min_xp) }}" 
                                   min="0"
                                   required>
                            @error('min_xp')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="language">Language <span class="text-danger">*</span></label>
                            <select class="form-control @error('language') is-invalid @enderror" 
                                    id="language" 
                                    name="language" 
                                    required>
                                <option value="">Select Language</option>
                                <option value="javascript" {{ old('language', $challenge->language) == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                                <option value="python" {{ old('language', $challenge->language) == 'python' ? 'selected' : '' }}>Python</option>
                                <option value="java" {{ old('language', $challenge->language) == 'java' ? 'selected' : '' }}>Java</option>
                                <option value="c" {{ old('language', $challenge->language) == 'c' ? 'selected' : '' }}>C</option>
                                <option value="cpp" {{ old('language', $challenge->language) == 'cpp' ? 'selected' : '' }}>C++</option>
                            </select>
                            @error('language')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="starter_code">Starter Code</label>
                    <textarea class="form-control @error('starter_code') is-invalid @enderror" 
                              id="starter_code" 
                              name="starter_code" 
                              rows="8">{{ old('starter_code', $challenge->starter_code) }}</textarea>
                    @error('starter_code')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Test Cases <span class="text-danger">*</span> (Minimum 1)</label>
                    <div id="test-cases-container">
                        @php
                            $testCases = old('test_cases', $challenge->test_cases ?? []);
                        @endphp
                        @foreach($testCases as $index => $testCase)
                            <div class="card mb-3 test-case-item">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Test Case {{ $index + 1 }}</strong>
                                    <button type="button" class="btn btn-sm btn-danger remove-test-case" {{ count($testCases) <= 1 ? 'disabled' : '' }}>
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Standard Input (stdin)</label>
                                        <textarea class="form-control" 
                                                  name="test_cases[{{ $index }}][stdin]" 
                                                  rows="2">{{ $testCase['stdin'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Expected Output <span class="text-danger">*</span></label>
                                        <textarea class="form-control" 
                                                  name="test_cases[{{ $index }}][expected_output]" 
                                                  rows="3" 
                                                  required>{{ $testCase['expected_output'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="add-test-case">
                        <i class="fas fa-plus"></i> Add Test Case
                    </button>
                    @error('test_cases')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input @error('is_active') is-invalid @enderror" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $challenge->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                    @error('is_active')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Challenge
                </button>
                <a href="{{ route('admin.challenges.index') }}" class="btn btn-default">
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
            const testCasesContainer = document.getElementById('test-cases-container');
            const addTestCaseBtn = document.getElementById('add-test-case');
            let testCaseIndex = testCasesContainer.querySelectorAll('.test-case-item').length;

            // Add test case
            addTestCaseBtn.addEventListener('click', function() {
                const newTestCase = document.createElement('div');
                newTestCase.className = 'card mb-3 test-case-item';
                newTestCase.innerHTML = `
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Test Case ${testCaseIndex + 1}</strong>
                        <button type="button" class="btn btn-sm btn-danger remove-test-case">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Standard Input (stdin)</label>
                            <textarea class="form-control" 
                                      name="test_cases[${testCaseIndex}][stdin]" 
                                      rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Expected Output <span class="text-danger">*</span></label>
                            <textarea class="form-control" 
                                      name="test_cases[${testCaseIndex}][expected_output]" 
                                      rows="3" 
                                      required></textarea>
                        </div>
                    </div>
                `;
                testCasesContainer.appendChild(newTestCase);
                testCaseIndex++;
                updateRemoveButtons();
                updateTestCaseNumbers();
            });

            // Remove test case
            testCasesContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-test-case')) {
                    const testCaseItem = e.target.closest('.test-case-item');
                    testCaseItem.remove();
                    updateRemoveButtons();
                    updateTestCaseNumbers();
                }
            });

            function updateRemoveButtons() {
                const testCaseItems = testCasesContainer.querySelectorAll('.test-case-item');
                testCaseItems.forEach(item => {
                    const removeBtn = item.querySelector('.remove-test-case');
                    removeBtn.disabled = testCaseItems.length <= 1;
                });
            }

            function updateTestCaseNumbers() {
                const testCaseItems = testCasesContainer.querySelectorAll('.test-case-item');
                testCaseItems.forEach((item, index) => {
                    const header = item.querySelector('.card-header strong');
                    header.textContent = `Test Case ${index + 1}`;
                });
            }

            // Initialize
            updateRemoveButtons();
        });
    </script>
@stop

