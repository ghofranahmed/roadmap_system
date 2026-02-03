<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use App\Models\RoadmapEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    /**
     * POST /api/roadmaps/{id}/enroll
     * اشتراك المستخدم في Roadmap + يرجع chat_room للمجتمع
     */
    public function enroll($id, Request $request)
    {
        $user = $request->user();

        $roadmap = Roadmap::with('chatRoom')->findOrFail($id);

        // منع الاشتراك في Roadmap غير مفعل
        if (!$roadmap->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'هذا المسار غير متاح حاليا',
            ], 403);
        }

        // منع التكرار
        $existing = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $roadmap->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'أنت مشترك بالفعل في هذا المسار',
                'enrollment' => $existing,
                'chat_room' => $roadmap->chatRoom,
            ]);
        }

        // إنشاء الاشتراك
        $enrollment = RoadmapEnrollment::create([
            'user_id' => $user->id,
            'roadmap_id' => $roadmap->id,
            'started_at' => now(),
            'status' => 'active',
            'xp_points' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الاشتراك في المسار بنجاح',
            'enrollment' => $enrollment,
            'chat_room' => $roadmap->chatRoom, // للـ Community
        ], Response::HTTP_CREATED);
    }

    /**
     * GET /api/me/enrollments
     * قائمة اشتراكات المستخدم
     */
    public function myEnrollments(Request $request)
    {
        $user = $request->user();

        $enrollments = RoadmapEnrollment::where('user_id', $user->id)
            ->with('roadmap:id,title,level,is_active') // عدّل الأعمدة حسب جدولك
            ->orderByDesc('started_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $enrollments,
        ]);
    }

    /**
     * PATCH /api/me/enrollments/{roadmapId}/status
     * تحديث الحالة: active | paused | completed
     */
    public function updateStatus($roadmapId, Request $request)
    {
        $request->validate([
            'status' => 'required|in:active,paused,completed',
        ]);

        $user = $request->user();

        $enrollment = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $roadmapId)
            ->firstOrFail();

        $enrollment->status = $request->status;

        if ($request->status === 'completed') {
            $enrollment->completed_at = now();
        }

        $enrollment->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الاشتراك',
            'data' => $enrollment,
        ]);
    }
    /**
 * DELETE /api/roadmaps/{id}/unenroll
 * إلغاء الاشتراك من Roadmap
 */
public function unenroll($id, Request $request)
{
    $user = $request->user();

    $enrollment = RoadmapEnrollment::where('user_id', $user->id)
        ->where('roadmap_id', $id)
        ->first();

    if (!$enrollment) {
        return response()->json([
            'success' => false,
            'message' => 'أنت غير مشترك في هذا المسار',
        ], 404);
    }

    $enrollment->delete();

    return response()->json([
        'success' => true,
        'message' => 'تم إلغاء الاشتراك بنجاح',
    ]);
}

}
