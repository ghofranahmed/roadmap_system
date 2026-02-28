<?php

namespace App\Http\Controllers;

use App\Models\LearningUnit;
use App\Models\Lesson;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Http\Requests\ReorderLessonsRequest;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    // ==========================
    // User Methods (Read Only)
    // ==========================

    /**
     * عرض دروس وحدة تعلم معينة للمستخدم العادي
     * GET /units/{unitId}/lessons
     */
    public function index($learningUnitId)
    {
        $lessons = Lesson::where('learning_unit_id', $learningUnitId)
            ->where('is_active', true)
            ->withCount('subLessons')
            ->orderBy('position')
            ->get(['id', 'title', 'position', 'description', 'created_at', 'learning_unit_id']);

        return $this->successResponse($lessons);
    }

    /**
     * عرض درس معين مع تفاصيله للمستخدم العادي
     * GET /lessons/{lessonId}
     */
    public function show($lessonId)
    {
        $lesson = Lesson::where('id', $lessonId)
            ->where('is_active', true)
            ->with(['subLessons' => function ($query) {
                $query->orderBy('position');
            }])
            ->firstOrFail(['id', 'learning_unit_id', 'title', 'description', 'position', 'is_active', 'created_at']);

        return $this->successResponse($lesson);
    }

    // ==========================
    // Admin Methods (Full CRUD)
    // ==========================

    /**
     * عرض جميع دروس وحدة تعلم معينة للمسؤول
     * GET /admin/units/{unitId}/lessons
     */
    public function adminIndex($learningUnitId)
    {
        $this->authorize('viewAny', Lesson::class);

        $lessons = Lesson::where('learning_unit_id', $learningUnitId)
            ->withCount('subLessons')
            ->orderBy('position')
            ->get();

        return $this->successResponse($lessons);
    }

    /**
     * إنشاء درس جديد
     * POST /admin/units/{unitId}/lessons
     */
    public function store(StoreLessonRequest $request, $unitId)
    {
        $this->authorize('create', Lesson::class);

        $learningUnit = LearningUnit::findOrFail($unitId);

        $maxPosition = (int) $learningUnit->lessons()->max('position');
        $position = $request->position ?? ($maxPosition + 1);

        $lesson = $learningUnit->lessons()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'position'    => $position,
            'is_active'   => $request->is_active ?? true,
        ]);

        return $this->successResponse($lesson, 'تم إنشاء الدرس بنجاح', 201);
    }

    /**
     * تحديث درس
     * PUT /admin/lessons/{lessonId}
     */
    public function update(UpdateLessonRequest $request, $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $this->authorize('update', $lesson);

        $data = $request->validated();
        unset($data['position']); // position changes only via reorder endpoint

        $lesson->update($data);
        $lesson->refresh();

        return $this->successResponse($lesson, 'تم تحديث الدرس بنجاح');
    }

    /**
     * إعادة ترتيب الدروس
     * PATCH /admin/units/{unitId}/lessons/reorder
     */
    public function reorder(ReorderLessonsRequest $request, $unitId)
    {
        $validated = $request->validated();
        $lessonIds = $validated['lesson_ids'];

        // Verify ALL lesson_ids belong to this learning unit
        $count = Lesson::where('learning_unit_id', $unitId)
            ->whereIn('id', $lessonIds)
            ->count();

        if ($count !== count($lessonIds)) {
            return $this->errorResponse(
                'بعض الدروس لا تنتمي لهذه الوحدة التعليمية',
                null,
                422
            );
        }

        // Authorize reorder based on the first lesson in the list (they all belong to same unit)
        $firstLesson = Lesson::where('learning_unit_id', $unitId)
            ->where('id', $lessonIds[0] ?? null)
            ->first();
        if ($firstLesson) {
            $this->authorize('reorder', $firstLesson);
        }

        DB::transaction(function () use ($lessonIds, $unitId) {
            // Phase 1: Set temporary negative positions to avoid unique constraint conflicts
            foreach ($lessonIds as $index => $id) {
                Lesson::where('id', $id)
                    ->where('learning_unit_id', $unitId)
                    ->update(['position' => -($index + 1)]);
            }

            // Phase 2: Set final contiguous positions 1..N
            foreach ($lessonIds as $index => $id) {
                Lesson::where('id', $id)
                    ->where('learning_unit_id', $unitId)
                    ->update(['position' => $index + 1]);
            }
        });

        $lessons = Lesson::where('learning_unit_id', $unitId)
            ->orderBy('position')
            ->get(['id', 'title', 'position', 'is_active']);

        return $this->successResponse([
            'learning_unit_id' => (int) $unitId,
            'updated_count'    => count($lessonIds),
            'lessons'          => $lessons,
        ], 'تم إعادة ترتيب الدروس بنجاح');
    }

    /**
     * تفعيل/تعطيل درس
     * PATCH /admin/lessons/{lessonId}/toggle-active
     */
    public function toggleActive($lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $this->authorize('toggleActive', $lesson);
        $lesson->is_active = !(bool) $lesson->is_active;
        $lesson->save();

        return $this->successResponse([
            'id'        => $lesson->id,
            'title'     => $lesson->title,
            'is_active' => $lesson->is_active,
        ], $lesson->is_active ? 'تم تفعيل الدرس بنجاح' : 'تم تعطيل الدرس بنجاح');
    }

    /**
     * حذف درس
     * DELETE /admin/lessons/{lessonId}
     */
    public function destroy($lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $this->authorize('delete', $lesson);
        $unitId = $lesson->learning_unit_id;
        $lesson->delete();

        // Normalize remaining positions to 1..N within the same unit
        $remaining = Lesson::where('learning_unit_id', $unitId)
            ->orderBy('position')
            ->get();

        foreach ($remaining as $index => $l) {
            if ($l->position !== $index + 1) {
                Lesson::where('id', $l->id)->update(['position' => $index + 1]);
            }
        }

        return $this->successResponse(null, 'تم حذف الدرس بنجاح');
    }
}
