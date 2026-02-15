<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\SubLesson;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSubLessonRequest;
use App\Http\Requests\UpdateSubLessonRequest;
use Illuminate\Support\Facades\DB;

class SubLessonController extends Controller
{
    // ==========================
    // User Methods (Read Only)
    // ==========================

    /**
     * عرض الدروس الفرعية لدرس معين للمستخدم العادي
     * GET /lessons/{lessonId}/sub-lessons
     */
    public function index($lessonId)
    {
        $subLessons = SubLesson::where('lesson_id', $lessonId)
            ->withCount('resources')
            ->orderBy('position')
            ->get(['id', 'description', 'position', 'created_at', 'lesson_id']);
            
        return $this->successResponse($subLessons);
    }

    /**
     * عرض درس فرعي معين للمستخدم العادي
     * GET /lessons/{lessonId}/sub-lessons/{subLessonId}
     */
    public function show($lessonId, $subLessonId)
    {
        $subLesson = SubLesson::where('lesson_id', $lessonId)
            ->with(['resources' => function($query) {
                $query->select(['id', 'title', 'type', 'language', 'link', 'created_at']);
            }])
            ->findOrFail($subLessonId, ['id', 'description', 'position', 'created_at']);
            
        return response()->json(['data' => $subLesson]);
    }

    // ==========================
    // Admin Methods (Full CRUD)
    // ==========================

    /**
     * عرض الدروس الفرعية لدرس معين للمسؤول
     * GET /admin/lessons/{lessonId}/sub-lessons
     */
    public function adminIndex($lessonId)
    {
        $subLessons = SubLesson::where('lesson_id', $lessonId)
            ->with(['resources' => function($q) {
                $q->select('id', 'title', 'type', 'language', 'link', 'sub_lesson_id', 'created_at');
            }])
            ->orderBy('position')
            ->get();
            
        return $this->successResponse($subLessons);
    }

    /**
     * إنشاء درس فرعي جديد
     * POST /admin/lessons/{lessonId}/sub-lessons
     */
    public function store(StoreSubLessonRequest $request, $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        
        $position = $request->position ?? $lesson->subLessons()->max('position') + 1;
        
        $subLesson = $lesson->subLessons()->create([
            'description' => $request->description,
            'position' => $position
        ]);
        
        return $this->successResponse($subLesson, 'تم إنشاء الدرس الفرعي بنجاح', 201);
    }

    /**
     * تحديث درس فرعي
     * PUT /admin/sub-lessons/{subLessonId}
     */
    public function update(UpdateSubLessonRequest $request, $subLessonId)
    {
        $subLesson = SubLesson::findOrFail($subLessonId);
        $subLesson->update($request->validated());
        
        return $this->successResponse($subLesson, 'تم تحديث الدرس الفرعي بنجاح');
    }

    /**
     * إعادة ترتيب الدروس الفرعية
     * PATCH /admin/lessons/{lessonId}/sub-lessons/reorder
     */
    public function reorder(\App\Http\Requests\ReorderSubLessonsRequest $request, $lessonId)
    {
        
        DB::transaction(function () use ($request, $lessonId) {
            foreach ($request->sublesson_ids as $index => $id) {
                SubLesson::where('id', $id)
                    ->where('lesson_id', $lessonId)
                    ->update(['position' => $index + 1]);
            }
        });
        
        return $this->successResponse(null, 'تم إعادة ترتيب الدروس الفرعية بنجاح');
    }

    /**
     * حذف درس فرعي
     * DELETE /admin/sub-lessons/{subLessonId}
     */
    public function destroy($subLessonId)
    {
        $subLesson = SubLesson::findOrFail($subLessonId);
        $subLesson->delete();
        
        return $this->successResponse(null, 'تم حذف الدرس الفرعي بنجاح');
    }
}