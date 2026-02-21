<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\SubLesson;
use App\Http\Requests\StoreSubLessonRequest;
use App\Http\Requests\UpdateSubLessonRequest;
use App\Http\Requests\ReorderSubLessonsRequest;
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
            ->with(['resources' => function ($query) {
                $query->select(['id', 'title', 'type', 'language', 'link', 'sub_lesson_id', 'created_at']);
            }])
            ->findOrFail($subLessonId, ['id', 'description', 'position', 'created_at', 'lesson_id']);

        return $this->successResponse($subLesson);
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
            ->with(['resources' => function ($q) {
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

        // Safe null handling: if no sub_lessons exist, max() returns null → treat as 0
        $maxPosition = (int) $lesson->subLessons()->max('position');
        $position = $request->position ?? ($maxPosition + 1);

        $subLesson = $lesson->subLessons()->create([
            'description' => $request->description,
            'position'    => $position,
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

        $data = $request->validated();
        unset($data['position']); // position changes only via reorder endpoint

        $subLesson->update($data);
        $subLesson->refresh();

        return $this->successResponse($subLesson, 'تم تحديث الدرس الفرعي بنجاح');
    }

    /**
     * إعادة ترتيب الدروس الفرعية
     * PATCH /admin/lessons/{lessonId}/sub-lessons/reorder
     */
    public function reorder(ReorderSubLessonsRequest $request, $lessonId)
    {
        $validated = $request->validated();
        $sublessonIds = $validated['sublesson_ids'];

        // Verify ALL sublesson_ids belong to this lesson
        $count = SubLesson::where('lesson_id', $lessonId)
            ->whereIn('id', $sublessonIds)
            ->count();

        if ($count !== count($sublessonIds)) {
            return $this->errorResponse(
                'بعض الدروس الفرعية لا تنتمي لهذا الدرس',
                null,
                422
            );
        }

        DB::transaction(function () use ($sublessonIds, $lessonId) {
            // Phase 1: Set temporary negative positions to avoid unique constraint conflicts
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

        $subLessons = SubLesson::where('lesson_id', $lessonId)
            ->orderBy('position')
            ->get(['id', 'description', 'position']);

        return $this->successResponse([
            'lesson_id'     => (int) $lessonId,
            'updated_count' => count($sublessonIds),
            'sub_lessons'   => $subLessons,
        ], 'تم إعادة ترتيب الدروس الفرعية بنجاح');
    }

    /**
     * حذف درس فرعي
     * DELETE /admin/sub-lessons/{subLessonId}
     */
    public function destroy($subLessonId)
    {
        $subLesson = SubLesson::findOrFail($subLessonId);
        $lessonId = $subLesson->lesson_id;
        $subLesson->delete();

        // Normalize remaining positions to 1..N within the same lesson
        $remaining = SubLesson::where('lesson_id', $lessonId)
            ->orderBy('position')
            ->get();

        foreach ($remaining as $index => $sl) {
            if ($sl->position !== $index + 1) {
                SubLesson::where('id', $sl->id)->update(['position' => $index + 1]);
            }
        }

        return $this->successResponse(null, 'تم حذف الدرس الفرعي بنجاح');
    }
}
