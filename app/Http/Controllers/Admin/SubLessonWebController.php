<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubLesson;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubLessonWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SubLesson::with('lesson.learningUnit.roadmap', 'resources');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('description', 'like', "%{$search}%");
        }

        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->integer('lesson_id'));
        }

        $subLessons = $query->orderBy('lesson_id')
            ->orderBy('position')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $lessons = Lesson::with('learningUnit.roadmap')->orderBy('id')->get();

        return view('admin.sub-lessons.index', compact('subLessons', 'lessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lessons = Lesson::with('learningUnit.roadmap')->orderBy('id')->get();
        return view('admin.sub-lessons.create', compact('lessons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'description' => 'required|string',
            'position' => 'nullable|integer|min:1',
        ]);

        try {
            $lesson = Lesson::findOrFail($validated['lesson_id']);
            $maxPosition = (int) $lesson->subLessons()->max('position');
            $position = $validated['position'] ?? ($maxPosition + 1);

            $subLesson = $lesson->subLessons()->create([
                'description' => $validated['description'],
                'position' => $position,
            ]);

            return redirect()->route('admin.sub-lessons.index')
                ->with('success', 'Sub-lesson created successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create sub-lesson: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubLesson $subLesson)
    {
        $subLesson->load('lesson.learningUnit.roadmap', 'resources');
        return view('admin.sub-lessons.show', compact('subLesson'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubLesson $subLesson)
    {
        return view('admin.sub-lessons.edit', compact('subLesson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubLesson $subLesson)
    {
        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        try {
            $subLesson->update($validated);
            
            return redirect()->route('admin.sub-lessons.index')
                ->with('success', 'Sub-lesson updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update sub-lesson: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubLesson $subLesson)
    {
        try {
            $lessonId = $subLesson->lesson_id;
            $subLesson->delete();

            // Normalize remaining positions
            $remaining = SubLesson::where('lesson_id', $lessonId)
                ->orderBy('position')
                ->get();

            foreach ($remaining as $index => $sl) {
                if ($sl->position !== $index + 1) {
                    SubLesson::where('id', $sl->id)->update(['position' => $index + 1]);
                }
            }
            
            return redirect()->route('admin.sub-lessons.index')
                ->with('success', 'Sub-lesson deleted successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete sub-lesson: ' . $e->getMessage()]);
        }
    }

    /**
     * Reorder sub-lessons.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'sublesson_ids' => 'required|array',
            'sublesson_ids.*' => 'exists:sub_lessons,id',
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $sublessonIds = $validated['sublesson_ids'];
                $lessonId = $validated['lesson_id'];

                // Phase 1: Set temporary negative positions
                foreach ($sublessonIds as $index => $id) {
                    SubLesson::where('id', $id)
                        ->where('lesson_id', $lessonId)
                        ->update(['position' => -($index + 1)]);
                }

                // Phase 2: Set final contiguous positions 1..N
                foreach ($sublessonIds as $index => $id) {
                    SubLesson::where('id', $id)
                        ->where('lesson_id', $lessonId)
                        ->update(['position' => $index + 1]);
                }
            });
            
            return back()->with('success', 'Sub-lessons reordered successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to reorder sub-lessons: ' . $e->getMessage()]);
        }
    }
}

