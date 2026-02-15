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
            return response()->json(['message' => 'يجب الاشتراك في المسار أولاً'], 403);
        }

        $tracking = LessonTracking::firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['is_complete' => false, 'last_updated_at' => now()]
        );

        // لو موجود، نحدّث last_updated_at فقط
        if (!$tracking->wasRecentlyCreated) {
            $tracking->update(['last_updated_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل فتح الدرس',
            'data' => [
                'lesson_id' => $lessonId,
                'is_complete' => (bool)$tracking->is_complete,
                'last_updated_at' => $tracking->last_updated_at,
            ]
        ]);
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
            return response()->json(['message' => 'يجب الاشتراك في المسار أولاً'], 403);
        }

        $tracking = LessonTracking::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['is_complete' => true, 'last_updated_at' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إكمال الدرس بنجاح',
            'data' => [
                'lesson_id' => $lessonId,
                'is_complete' => true,
                'last_updated_at' => $tracking->last_updated_at,
            ]
        ]);
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

        return response()->json([
            'success' => true,
            'data' => $tracking ? [
                'lesson_id' => (int)$lessonId,
                'is_complete' => (bool)$tracking->is_complete,
                'last_updated_at' => $tracking->last_updated_at,
            ] : null
        ]);
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
            return response()->json(['message' => 'لا يوجد تتبع لهذا الدرس'], 404);
        }

        $tracking->update([
            'is_complete' => false,
            'last_updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة تعيين تتبع الدرس',
            'data' => [
                'lesson_id' => (int)$lessonId,
                'is_complete' => false,
                'last_updated_at' => $tracking->last_updated_at,
            ]
        ]);
    }

    /**
     * GET /me/lessons/tracking
     * كل تتبع المستخدم
     */
    public function userLessons(Request $request)
    {
        $userId = Auth::id();

        $trackings = LessonTracking::where('user_id', $userId)
            ->with(['lesson:id,title,learning_unit_id'])
            ->orderByDesc('last_updated_at')
            ->paginate($request->per_page ?? 20);

        return response()->json(['success' => true, 'data' => $trackings]);
    }

    /**
     * GET /me/lessons/stats
     */
    public function userStats()
    {
        $userId = Auth::id();

        $total = LessonTracking::where('user_id', $userId)->count();
        $completed = LessonTracking::where('user_id', $userId)->where('is_complete', true)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_tracked' => $total,
                'completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
            ]
        ]);
    }
}
