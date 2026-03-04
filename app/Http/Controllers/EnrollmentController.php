<?php

namespace App\Http\Controllers;

use App\Http\Resources\EnrolledRoadmapResource;
use App\Models\Roadmap;
use App\Models\RoadmapEnrollment;
use App\Services\RoadmapResetService;
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
            return $this->errorResponse(
                'You are already enrolled in this roadmap',
                [
                    'enrollment_id' => $existing->id,
                    'roadmap_id' => $roadmap->id,
                    'status' => $existing->status,
                ],
                409 // HTTP 409 Conflict
            );
        }

        // إنشاء الاشتراك
        try {
            $enrollment = RoadmapEnrollment::create([
                'user_id' => $user->id,
                'roadmap_id' => $roadmap->id,
                'started_at' => now(),
                'status' => 'active',
                'xp_points' => 0,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation (race condition)
            // SQL error code 23000 = Integrity constraint violation
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                // Fetch the existing enrollment that was created by concurrent request
                $existing = RoadmapEnrollment::where('user_id', $user->id)
                    ->where('roadmap_id', $roadmap->id)
                    ->first();
                
                if ($existing) {
                    return $this->errorResponse(
                        'You are already enrolled in this roadmap',
                        [
                            'enrollment_id' => $existing->id,
                            'roadmap_id' => $roadmap->id,
                            'status' => $existing->status,
                        ],
                        409 // HTTP 409 Conflict
                    );
                }
            }
            
            // Re-throw if it's a different database error
            throw $e;
        }

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
     * GET /api/me/enrolled-roadmaps
     * Get enrolled roadmaps in roadmap-first format for mobile "My Account" tab
     */
    public function myEnrolledRoadmaps(Request $request)
    {
        $user = $request->user();

        $enrollments = RoadmapEnrollment::where('user_id', $user->id)
            ->with(['roadmap' => function ($query) {
                $query->select('id', 'title', 'description', 'level', 'is_active');
            }])
            ->orderByDesc('started_at')
            ->get();

        return $this->successResponse(
            EnrolledRoadmapResource::collection($enrollments),
            'Enrolled roadmaps retrieved successfully'
        );
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

    /**
     * POST /api/roadmaps/{roadmapId}/reset-progress
     * Reset all progress for a roadmap enrollment
     */
    public function resetProgress($roadmapId, Request $request, RoadmapResetService $resetService)
    {
        $user = $request->user();

        // Verify enrollment exists
        $enrollment = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $roadmapId)
            ->first();

        if (!$enrollment) {
            return $this->errorResponse('You are not enrolled in this roadmap', null, 404);
        }

        // Perform reset
        $result = $resetService->resetRoadmapProgress($user->id, $roadmapId);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], $result['data'], 422);
        }

        return $this->successResponse($result['data'], $result['message'], 200);
    }

    /**
     * GET /api/v1/me/account
     * Get combined user account data with enrolled roadmaps for Flutter "My Account" screen
     */
    public function myAccount(Request $request)
    {
        $user = $request->user();

        // Format user data with profile_picture_url
        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'profile_picture_url' => $user->profile_picture 
                ? asset('storage/' . $user->profile_picture) 
                : null,
        ];

        // Get enrollments with eager loading to avoid N+1 queries
        // Eager load roadmap and its learningUnits (needed for progress calculation)
        $enrollments = RoadmapEnrollment::where('user_id', $user->id)
            ->with([
                'roadmap' => function ($query) {
                    $query->select('id', 'title', 'description', 'level', 'is_active')
                          ->with('learningUnits:id,roadmap_id,unit_type'); // Eager load to avoid N+1
                }
            ])
            ->orderByDesc('started_at')
            ->get();

        return $this->successResponse([
            'user' => $userData,
            'enrolled_roadmaps' => EnrolledRoadmapResource::collection($enrollments),
        ], 'Account data retrieved successfully');
    }

}
