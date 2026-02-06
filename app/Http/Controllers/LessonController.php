<?php

namespace App\Http\Controllers;

use App\Models\LearningUnit;
use App\Models\Lesson;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    // ==========================
    // User Methods (Read Only)
    // ==========================

    /**
     * عرض دروس وحدة تعلم معينة للمستخدم العادي
     * GET /learning-units/{learningUnitId}/lessons
     */
    public function index($learningUnitId)
    {
        $lessons = Lesson::where('learning_unit_id', $learningUnitId)
            ->where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'title', 'position', 'description', 'created_at']);
            
        return response()->json(['data' => $lessons]);
    }

    /**
     * عرض درس معين مع تفاصيله للمستخدم العادي
     * GET /learning-units/{learningUnitId}/lessons/{lessonId}
     */
    public function show($learningUnitId, $lessonId)
    {
        $lesson = Lesson::where('learning_unit_id', $learningUnitId)
            ->where('id', $lessonId)
            ->where('is_active', true)
            ->with(['subLessons' => function($query) {
                $query->orderBy('position');
            }])
            ->firstOrFail(['id', 'title', 'description', 'position', 'created_at']);
            
        return response()->json(['data' => $lesson]);
    }

    // ==========================
    // Admin Methods (Full CRUD)
    // ==========================

    /**
     * عرض جميع دروس وحدة تعلم معينة للمسؤول
     * GET /admin/learning-units/{learningUnitId}/lessons
     */
    public function adminIndex($learningUnitId)
    {
        $lessons = Lesson::where('learning_unit_id', $learningUnitId)
            ->orderBy('position')
            ->get();
            
        return response()->json(['data' => $lessons]);
    }

    /**
     * إنشاء درس جديد
     * POST /admin/learning-units/{learningUnitId}/lessons
     */
    public function store(StoreLessonRequest $request, $learningUnitId)
    {
        $learningUnit = LearningUnit::findOrFail($learningUnitId);
        
        $position = $request->position ?? $learningUnit->lessons()->max('position') + 1;
        
        $lesson = $learningUnit->lessons()->create([
            'title' => $request->title,
            'description' => $request->description,
            'position' => $position,
            'is_active' => $request->is_active ?? true
        ]);
        
        return response()->json([
            'message' => 'تم إنشاء الدرس بنجاح',
            'data' => $lesson
        ], 201);
    }

    /**
     * تحديث درس
     * PUT /admin/lessons/{lessonId}
     */
    public function update(UpdateLessonRequest $request, $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $lesson->update($request->validated());
        
        return response()->json([
            'message' => 'تم تحديث الدرس بنجاح',
            'data' => $lesson
        ]);
    }

    /**
     * إعادة ترتيب الدروس
     * PATCH /admin/learning-units/{learningUnitId}/lessons/reorder
     */
    public function reorder(Request $request, $learningUnitId)
    {
        $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:lessons,id,learning_unit_id,' . $learningUnitId
        ]);
        
        DB::transaction(function () use ($request, $learningUnitId) {
            foreach ($request->lesson_ids as $index => $id) {
                Lesson::where('id', $id)
                    ->where('learning_unit_id', $learningUnitId)
                    ->update(['position' => $index + 1]);
            }
        });
        
        return response()->json(['message' => 'تم إعادة ترتيب الدروس بنجاح']);
    }

    /**
     * تفعيل/تعطيل درس
     * PATCH /admin/lessons/{lessonId}/toggle-active
     */
    public function toggleActive($lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $lesson->update(['is_active' => !$lesson->is_active]);
        
        return response()->json([
            'message' => 'تم تحديث حالة الدرس',
            'data' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'is_active' => $lesson->is_active
            ]
        ]);
    }

    /**
     * حذف درس
     * DELETE /admin/lessons/{lessonId}
     */
    public function destroy($lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $lesson->delete();
        
        return response()->json(['message' => 'تم حذف الدرس بنجاح']);
    }
}