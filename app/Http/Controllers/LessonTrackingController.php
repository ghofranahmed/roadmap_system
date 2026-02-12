<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonTracking;
use App\Models\RoadmapEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonTrackingController extends Controller
{
    /**
     * تسجيل فتح درس
     * POST /lessons/{lessonId}/track/open
     */
    public function open(Request $request, $lessonId)
    {
        $userId = Auth::id();
        $user = Auth::user();
        
        // التحقق من وجود الدرس ونشاطه
        $lesson = Lesson::where('is_active', true)
            ->with('learningUnit.roadmap')
            ->findOrFail($lessonId);
        
        // الحصول على الـ Roadmap من خلال العلاقة
        $roadmap = $lesson->learningUnit->roadmap;
        
        // التحقق من أن المستخدم مشترك في المسار - استخدام الاستعلام المباشر
        $isEnrolled = RoadmapEnrollment::where('user_id', $userId)
            ->where('roadmap_id', $roadmap->id)
            ->exists();
        
        if (!$isEnrolled) {
            return response()->json([
                'message' => 'يجب الاشتراك في المسار أولاً'
            ], 403);
        }
        
        // البحث عن تتبع موجود أو إنشاء جديد
        $tracking = LessonTracking::firstOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $lessonId
            ],
            [
                'opened_at' => now(),
                'status' => 'in_progress',
                'progress' => 0
            ]
        );
        
        // إذا كان التتبع موجوداً بالفعل، نحدث وقت الفتح فقط
        if (!$tracking->wasRecentlyCreated) {
            $tracking->update([
                'opened_at' => now(),
                'updated_at' => now()
            ]);
            $message = 'تم تحديث وقت فتح الدرس';
        } else {
            $message = 'تم بدء الدرس بنجاح';
        }
        
        // تحديث آخر نشاط للمستخدم
        User::where('id', $userId)->update(['last_active_at' => now()]);
        
        return response()->json([
            'message' => $message,
            'data' => [
                'tracking_id' => $tracking->id,
                'lesson_id' => $lessonId,
                'title' => $lesson->title,
                'status' => $tracking->status,
                'opened_at' => $tracking->opened_at,
                'progress' => $tracking->progress
            ]
        ]);
    }
    
    /**
     * تسجيل إكمال درس
     * POST /lessons/{lessonId}/track/complete
     */
    public function complete(Request $request, $lessonId)
    {
        $userId = Auth::id();
        
        // التحقق من وجود الدرس
        $lesson = Lesson::where('is_active', true)->findOrFail($lessonId);
        
        // البحث عن تتبع الدرس
        $tracking = LessonTracking::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
        
        if (!$tracking) {
            return response()->json([
                'message' => 'يجب فتح الدرس أولاً'
            ], 400);
        }
        
        // حساب الوقت المستغرق
        $timeSpent = $tracking->opened_at 
            ? now()->diffInMinutes($tracking->opened_at) 
            : 0;
        
        // تحديث التتبع
        $tracking->update([
            'completed_at' => now(),
            'status' => 'completed',
            'progress' => 100,
            'time_spent_minutes' => $timeSpent,
            'updated_at' => now()
        ]);
        
        // تحديث آخر نشاط للمستخدم
        User::where('id', $userId)->update(['last_active_at' => now()]);
        
        return response()->json([
            'message' => 'تم إكمال الدرس بنجاح',
            'data' => [
                'lesson_id' => $lessonId,
                'title' => $lesson->title,
                'status' => 'completed',
                'completed_at' => $tracking->completed_at,
                'time_spent_minutes' => $timeSpent,
                'progress' => 100
            ]
        ]);
    }
    
    /**
     * تحديث تقدم درس (تقدم جزئي)
     * PATCH /lessons/{lessonId}/track/progress
     */
    public function updateProgress(Request $request, $lessonId)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100'
        ]);
        
        $userId = Auth::id();
        
        $tracking = LessonTracking::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
        
        if (!$tracking) {
            return response()->json([
                'message' => 'يجب فتح الدرس أولاً'
            ], 400);
        }
        
        $tracking->update([
            'progress' => $request->progress,
            'status' => $request->progress == 100 ? 'completed' : 'in_progress',
            'updated_at' => now()
        ]);
        
        if ($request->progress == 100 && !$tracking->completed_at) {
            $tracking->update(['completed_at' => now()]);
        }
        
        // تحديث آخر نشاط للمستخدم
        User::where('id', $userId)->update(['last_active_at' => now()]);
        
        return response()->json([
            'message' => 'تم تحديث التقدم',
            'data' => [
                'lesson_id' => $lessonId,
                'progress' => $tracking->progress,
                'status' => $tracking->status,
                'updated_at' => $tracking->updated_at
            ]
        ]);
    }
    
    /**
     * الحصول على تتبع درس معين للمستخدم الحالي
     * GET /lessons/{lessonId}/track
     */
    public function show($lessonId)
    {
        $userId = Auth::id();
        
        $tracking = LessonTracking::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->with('lesson')
            ->first();
        
        if (!$tracking) {
            return response()->json([
                'message' => 'لم يتم البدء في هذا الدرس بعد',
                'data' => null
            ], 404);
        }
        
        return response()->json(['data' => $tracking]);
    }
    
    /**
     * الحصول على جميع دروس المستخدم مع حالة التتبع
     * GET /me/lessons/tracking
     */
    public function userLessons(Request $request)
    {
        $userId = Auth::id();
        
        $query = LessonTracking::where('user_id', $userId)
            ->with(['lesson' => function($query) {
                $query->select(['id', 'title', 'learning_unit_id'])
                    ->with(['learningUnit' => function($q) {
                        $q->select(['id', 'title', 'roadmap_id'])
                            ->with(['roadmap' => function($r) {
                                $r->select(['id', 'title']);
                            }]);
                    }]);
            }])
            ->orderBy('updated_at', 'desc');
        
        // فلترة حسب الحالة إذا تم إرسالها
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // فلترة حسب المسار إذا تم إرسالها
        if ($request->has('roadmap_id')) {
            $query->whereHas('lesson.learningUnit.roadmap', function($q) use ($request) {
                $q->where('id', $request->roadmap_id);
            });
        }
        
        $trackings = $query->paginate($request->per_page ?? 20);
        
        return response()->json(['data' => $trackings]);
    }
    
    /**
     * إحصائيات تقدم المستخدم
     * GET /me/lessons/stats
     */
    public function userStats()
    {
        $userId = Auth::id();
        
        $stats = DB::table('lesson_trackings')
            ->select(
                DB::raw('COUNT(*) as total_lessons'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons'),
                DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_lessons'),
                DB::raw('SUM(CASE WHEN status = "not_started" THEN 1 ELSE 0 END) as not_started_lessons'),
                DB::raw('AVG(progress) as average_progress'),
                DB::raw('SUM(time_spent_minutes) as total_time_minutes')
            )
            ->where('user_id', $userId)
            ->first();
        
        return response()->json([
            'data' => [
                'total_lessons' => $stats->total_lessons ?? 0,
                'completed_lessons' => $stats->completed_lessons ?? 0,
                'in_progress_lessons' => $stats->in_progress_lessons ?? 0,
                'not_started_lessons' => $stats->not_started_lessons ?? 0,
                'average_progress' => round($stats->average_progress ?? 0, 2),
                'total_time_minutes' => $stats->total_time_minutes ?? 0,
                'total_time_hours' => round(($stats->total_time_minutes ?? 0) / 60, 1),
                'completion_rate' => $stats->total_lessons > 0 
                    ? round(($stats->completed_lessons / $stats->total_lessons) * 100, 2) 
                    : 0
            ]
        ]);
    }
    
    /**
     * إعادة تعيين تتبع درس
     * DELETE /lessons/{lessonId}/track/reset
     */
    public function reset($lessonId)
    {
        $userId = Auth::id();
        
        $tracking = LessonTracking::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
        
        if (!$tracking) {
            return response()->json([
                'message' => 'لا يوجد تتبع لهذا الدرس'
            ], 404);
        }
        
        $tracking->update([
            'progress' => 0,
            'status' => 'not_started',
            'opened_at' => null,
            'completed_at' => null,
            'time_spent_minutes' => 0,
            'updated_at' => now()
        ]);
        
        return response()->json([
            'message' => 'تم إعادة تعيين تتبع الدرس',
            'data' => [
                'lesson_id' => $lessonId,
                'progress' => 0,
                'status' => 'not_started'
            ]
        ]);
    }
}