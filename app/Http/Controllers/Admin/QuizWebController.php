<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\LearningUnit;
use Illuminate\Http\Request;

class QuizWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Quiz::with('learningUnit.roadmap', 'questions');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('learning_unit_id')) {
            $query->where('learning_unit_id', $request->integer('learning_unit_id'));
        }

        if ($request->filled('is_active')) {
            $isActive = filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        $quizzes = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $learningUnits = LearningUnit::with('roadmap')->orderBy('title')->get();

        return view('admin.quizzes.index', compact('quizzes', 'learningUnits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $learningUnits = LearningUnit::with('roadmap')->orderBy('title')->get();
        return view('admin.quizzes.create', compact('learningUnits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'learning_unit_id' => 'required|exists:learning_units,id',
            'title' => 'nullable|string|max:255',
            'min_xp' => 'required|integer|min:0',
            'max_xp' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Additional validation: min_xp must be <= max_xp
        if ($validated['min_xp'] > $validated['max_xp']) {
            return back()->withInput()
                ->withErrors(['min_xp' => 'Minimum XP must be less than or equal to Maximum XP.']);
        }

        try {
            $quiz = Quiz::create($validated);

            return redirect()->route('admin.quizzes.index')
                ->with('success', 'Quiz created successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create quiz: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        $quiz->load('learningUnit.roadmap', 'questions');
        return view('admin.quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quiz $quiz)
    {
        $learningUnits = LearningUnit::with('roadmap')->orderBy('title')->get();
        return view('admin.quizzes.edit', compact('quiz', 'learningUnits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'learning_unit_id' => 'required|exists:learning_units,id',
            'title' => 'nullable|string|max:255',
            'min_xp' => 'required|integer|min:0',
            'max_xp' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Additional validation: min_xp must be <= max_xp
        if ($validated['min_xp'] > $validated['max_xp']) {
            return back()->withInput()
                ->withErrors(['min_xp' => 'Minimum XP must be less than or equal to Maximum XP.']);
        }

        try {
            $quiz->update($validated);
            
            return redirect()->route('admin.quizzes.index')
                ->with('success', 'Quiz updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update quiz: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        try {
            $quiz->delete();
            
            return redirect()->route('admin.quizzes.index')
                ->with('success', 'Quiz deleted successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete quiz: ' . $e->getMessage()]);
        }
    }
}

