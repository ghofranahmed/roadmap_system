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
            return $this->errorResponse('هذا المسار غير متاح حاليا', null, 403);
        }

        // منع التكرار
        $existing = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $roadmap->id)
            ->first();

        if ($existing) {
            return $this->successResponse([
                'enrollment' => $existing,
                'chat_room' => $roadmap->chatRoom,
            ], 'أنت مشترك بالفعل في هذا المسار');
        }

        // إنشاء الاشتراك
        $enrollment = RoadmapEnrollment::create([
            'user_id' => $user->id,
            'roadmap_id' => $roadmap->id,
            'started_at' => now(),
            'status' => 'active',
            'xp_points' => 0,
        ]);

        return $this->successResponse([
            'enrollment' => $enrollment,
            'chat_room' => $roadmap->chatRoom,
        ], 'تم الاشتراك في المسار بنجاح', 201);
    }

    /**
     * GET /api/me/enrollments
     * قائمة اشتراكات المستخدم
     */
    public function myEnrollments(Request $request)
    {
        $user = $request->user();

        $enrollments = RoadmapEnrollment::where('user_id', $user->id)
            ->with('roadmap:id,title,level,is_active')
            ->orderByDesc('started_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($enrollments, 'Enrollments retrieved successfully');
    }

    /**
     * PATCH /api/me/enrollments/{roadmapId}/status
     * تحديث الحالة: active | paused | completed
     */
    public function updateStatus($roadmapId, \App\Http\Requests\UpdateEnrollmentStatusRequest $request)
    {

        $user = $request->user();

        $enrollment = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $roadmapId)
            ->firstOrFail();

        $enrollment->status = $request->status;

        if ($request->status === 'completed') {
            $enrollment->completed_at = now();
        }

        $enrollment->save();

        return $this->successResponse($enrollment, 'تم تحديث حالة الاشتراك');
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
        return $this->errorResponse('أنت غير مشترك في هذا المسار', null, 404);
    }

    $enrollment->delete();

    return $this->successResponse(null, 'تم إلغاء الاشتراك بنجاح');
}

}
