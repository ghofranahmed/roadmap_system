<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LearningUnit;
use Illuminate\Http\Request;

class LessonWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lesson::with('learningUnit', 'subLessons');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('learning_unit_id')) {
            $query->where('learning_unit_id', $request->integer('learning_unit_id'));
        }

        if ($request->filled('is_active')) {
            $isActive = filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        $lessons = $query->orderBy('learning_unit_id')
            ->orderBy('position')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $learningUnits = LearningUnit::with('roadmap')->orderBy('title')->get();

        return view('admin.lessons.index', compact('lessons', 'learningUnits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $learningUnits = LearningUnit::with('roadmap')->orderBy('title')->get();
        return view('admin.lessons.create', compact('learningUnits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'learning_unit_id' => 'required|exists:learning_units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        try {
            $learningUnit = LearningUnit::findOrFail($validated['learning_unit_id']);
            $maxPosition = (int) $learningUnit->lessons()->max('position');
            $position = $validated['position'] ?? ($maxPosition + 1);

            $lesson = $learningUnit->lessons()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'position' => $position,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return redirect()->route('admin.lessons.index')
                ->with('success', 'Lesson created successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create lesson: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        $lesson->load('learningUnit', 'subLessons');
        return view('admin.lessons.show', compact('lesson'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        return view('admin.lessons.edit', compact('lesson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $lesson->update($validated);
            
            return redirect()->route('admin.lessons.index')
                ->with('success', 'Lesson updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update lesson: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        try {
            $lesson->delete();
            
            return redirect()->route('admin.lessons.index')
                ->with('success', 'Lesson deleted successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete lesson: ' . $e->getMessage()]);
        }
    }

    /**
     * Reorder lessons.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:lessons,id',
        ]);

        try {
            foreach ($validated['lesson_ids'] as $index => $lessonId) {
                Lesson::where('id', $lessonId)->update(['position' => $index + 1]);
            }
            
            return back()->with('success', 'Lessons reordered successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to reorder lessons: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle active status of the lesson.
     */
    public function toggleActive(Lesson $lesson)
    {
        try {
            $lesson->is_active = !$lesson->is_active;
            $lesson->save();
            
            return back()->with('success', 
                'Lesson ' . ($lesson->is_active ? 'activated' : 'deactivated') . ' successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to toggle lesson status: ' . $e->getMessage()]);
        }
    }
}

