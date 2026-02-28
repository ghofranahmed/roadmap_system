<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizQuestionWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = QuizQuestion::with('quiz.learningUnit.roadmap');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('question_text', 'like', "%{$search}%");
        }

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->integer('quiz_id'));
        }

        $questions = $query->orderBy('quiz_id')
            ->orderBy('order')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $quizzes = Quiz::with('learningUnit.roadmap')->orderBy('id')->get();

        return view('admin.quiz-questions.index', compact('questions', 'quizzes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $quizzes = Quiz::with('learningUnit.roadmap')->orderBy('id')->get();
        $selectedQuizId = $request->get('quiz');
        return view('admin.quiz-questions.create', compact('quizzes', 'selectedQuizId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string|max:5000',
            'options' => 'required|array|min:2|max:10',
            'options.*' => 'required|string|max:500',
            'correct_answer' => 'required|string|max:500',
            'question_xp' => 'nullable|integer|min:0|max:100',
            'order' => 'nullable|integer|min:1',
        ]);

        // Validate that correct_answer matches one of the options
        if (!in_array($validated['correct_answer'], $validated['options'])) {
            return back()->withInput()
                ->withErrors(['correct_answer' => 'The correct answer must match one of the options.']);
        }

        try {
            $quiz = Quiz::findOrFail($validated['quiz_id']);
            $maxOrder = (int) $quiz->questions()->max('order');
            $order = $validated['order'] ?? ($maxOrder + 1);

            $question = $quiz->questions()->create([
                'question_text' => $validated['question_text'],
                'options' => $validated['options'],
                'correct_answer' => $validated['correct_answer'],
                'question_xp' => $validated['question_xp'] ?? 0,
                'order' => $order,
            ]);

            return redirect()->route('admin.quiz-questions.index')
                ->with('success', 'Quiz question created successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create quiz question: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(QuizQuestion $quizQuestion)
    {
        $quizQuestion->load('quiz.learningUnit.roadmap');
        return view('admin.quiz-questions.show', compact('quizQuestion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QuizQuestion $quizQuestion)
    {
        $quizzes = Quiz::with('learningUnit.roadmap')->orderBy('id')->get();
        return view('admin.quiz-questions.edit', compact('quizQuestion', 'quizzes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuizQuestion $quizQuestion)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string|max:5000',
            'options' => 'required|array|min:2|max:10',
            'options.*' => 'required|string|max:500',
            'correct_answer' => 'required|string|max:500',
            'question_xp' => 'nullable|integer|min:0|max:100',
            'order' => 'nullable|integer|min:1',
        ]);

        // Validate that correct_answer matches one of the options
        if (!in_array($validated['correct_answer'], $validated['options'])) {
            return back()->withInput()
                ->withErrors(['correct_answer' => 'The correct answer must match one of the options.']);
        }

        try {
            $quizQuestion->update($validated);
            
            return redirect()->route('admin.quiz-questions.index')
                ->with('success', 'Quiz question updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update quiz question: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuizQuestion $quizQuestion)
    {
        try {
            $quizQuestion->delete();
            
            return redirect()->route('admin.quiz-questions.index')
                ->with('success', 'Quiz question deleted successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete quiz question: ' . $e->getMessage()]);
        }
    }
}

