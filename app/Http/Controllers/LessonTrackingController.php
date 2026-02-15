<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonTracking;
use App\Models\RoadmapEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonTrackingController extends Controller
{
    /**
     * POST /lessons/{lessonId}/track/open
     * مجرد تسجيل آخر وقت فتح/تفاعل مع الدرس
     */
    public function open(Request $request, $lessonId)
    {
        $userId = Auth::id();

        $lesson = Lesson::where('is_active', true)
            ->with('learningUnit.roadmap')
            ->findOrFail($lessonId);

        $roadmapId = $lesson->learningUnit->roadmap_id;

        $isEnrolled = RoadmapEnrollment::where('user_id', $userId)
            ->where('roadmap_id', $roadmapId)
            ->exists();

        if (!$isEnrolled) {
            return $this->errorResponse('يجب الاشتراك في المسار أولاً', null, 403);
        }

        $tracking = LessonTracking::firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['is_complete' => false, 'last_updated_at' => now()]
        );

        // لو موجود، نحدّث last_updated_at فقط
        if (!$tracking->wasRecentlyCreated) {
            $tracking->update(['last_updated_at' => now()]);
        }

        return $this->successResponse([
            'lesson_id' => $lessonId,
            'is_complete' => (bool)$tracking->is_complete,
            'last_updated_at' => $tracking->last_updated_at,
        ], 'تم تسجيل فتح الدرس');
    }

    /**
     * POST /lessons/{lessonId}/track/complete
     * تسجيل أن الدرس اكتمل
     */
    public function complete(Request $request, $lessonId)
    {
        $userId = Auth::id();

        $lesson = Lesson::where('is_active', true)
            ->with('learningUnit')
            ->findOrFail($lessonId);

        $roadmapId = $lesson->learningUnit->roadmap_id;

        $isEnrolled = RoadmapEnrollment::where('user_id', $userId)
            ->where('roadmap_id', $roadmapId)
            ->exists();

        if (!$isEnrolled) {
            return $this->errorResponse('يجب الاشتراك في المسار أولاً', null, 403);
        }

        $tracking = LessonTracking::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['is_complete' => true, 'last_updated_at' => now()]
        );

        return $this->successResponse([
            'lesson_id' => $lessonId,
            'is_complete' => true,
            'last_updated_at' => $tracking->last_updated_at,
        ], 'تم إكمال الدرس بنجاح');
    }

    /**
     * GET /lessons/{lessonId}/track
     * إرجاع حالة الدرس للمستخدم الحالي
     */
    public function show($lessonId)
    {
        $userId = Auth::id();

        $tracking = LessonTracking::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();

        return $this->successResponse($tracking ? [
            'lesson_id' => (int)$lessonId,
            'is_complete' => (bool)$tracking->is_complete,
            'last_updated_at' => $tracking->last_updated_at,
        ] : null);
    }

    /**
     * DELETE /lessons/{lessonId}/track/reset
     * إعادة ضبط الدرس (يرجع غير مكتمل)
     */
    public function reset($lessonId)
    {
        $userId = Auth::id();

        $tracking = LessonTracking::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();

        if (!$tracking) {
            return $this->errorResponse('لا يوجد تتبع لهذا الدرس', null, 404);
        }

        $tracking->update([
            'is_complete' => false,
            'last_updated_at' => now()
        ]);

        return $this->successResponse([
            'lesson_id' => (int)$lessonId,
            'is_complete' => false,
            'last_updated_at' => $tracking->last_updated_at,
        ], 'تم إعادة تعيين تتبع الدرس');
    }

    /**
     * GET /me/lessons/tracking
     * كل تتبع المستخدم
     */
    public function userLessons(Request $request)
    {
        $userId = Auth::id();

        $trackings = LessonTracking::where('user_id', $userId)
            ->with(['lesson:id,title,learning_unit_id', 'lesson.learningUnit:id,title,roadmap_id'])
            ->orderByDesc('last_updated_at')
            ->paginate($request->get('per_page', 20));

        return $this->paginatedResponse($trackings, 'Tracking data retrieved successfully');
    }

    /**
     * GET /me/lessons/stats
     */
    public function userStats()
    {
        $userId = Auth::id();

        $total = LessonTracking::where('user_id', $userId)->count();
        $completed = LessonTracking::where('user_id', $userId)->where('is_complete', true)->count();

        return $this->successResponse([
            'total_tracked' => $total,
            'completed' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
        ]);
    }
}
