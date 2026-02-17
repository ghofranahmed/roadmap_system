<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatModeration;
use App\Models\Roadmap;
use App\Models\RoadmapEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminChatModerationController extends Controller
{
    /**
     * POST /admin/roadmaps/{roadmap}/chat/mute
     */
    public function mute(Request $request, $roadmapId)
    {
        try {
            $validated = $request->validate([
                'user_id'     => 'required|integer|exists:users,id',
                'reason'      => 'nullable|string|max:500',
                'muted_until' => 'nullable|date|after:now',
            ]);

            $chatRoom = $this->getChatRoom($roadmapId);
            if ($chatRoom instanceof \Illuminate\Http\JsonResponse) {
                return $chatRoom;
            }

            $targetUser = User::findOrFail($validated['user_id']);

            // Prevent muting other admins
            if (in_array($targetUser->role, ['admin', 'tech_admin'])) {
                return $this->errorResponse('Cannot mute an admin user.', null, 403);
            }

            $moderation = ChatModeration::updateOrCreate(
                [
                    'chat_room_id' => $chatRoom->id,
                    'user_id'      => $targetUser->id,
                    'type'         => 'mute',
                ],
                [
                    'muted_until' => $validated['muted_until'] ?? null,
                    'reason'      => $validated['reason'] ?? null,
                    'created_by'  => $request->user()->id,
                ]
            );

            return $this->successResponse($moderation, 'User muted successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Resource not found.', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to mute user.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * POST /admin/roadmaps/{roadmap}/chat/unmute
     */
    public function unmute(Request $request, $roadmapId)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $chatRoom = $this->getChatRoom($roadmapId);
            if ($chatRoom instanceof \Illuminate\Http\JsonResponse) {
                return $chatRoom;
            }

            $deleted = ChatModeration::where('chat_room_id', $chatRoom->id)
                ->where('user_id', $validated['user_id'])
                ->where('type', 'mute')
                ->delete();

            if (!$deleted) {
                return $this->errorResponse('User is not muted in this chat room.', null, 404);
            }

            return $this->successResponse(null, 'User unmuted successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to unmute user.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * POST /admin/roadmaps/{roadmap}/chat/ban
     */
    public function ban(Request $request, $roadmapId)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'reason'  => 'nullable|string|max:500',
            ]);

            $chatRoom = $this->getChatRoom($roadmapId);
            if ($chatRoom instanceof \Illuminate\Http\JsonResponse) {
                return $chatRoom;
            }

            $targetUser = User::findOrFail($validated['user_id']);

            // Prevent banning other admins
            if (in_array($targetUser->role, ['admin', 'tech_admin'])) {
                return $this->errorResponse('Cannot ban an admin user.', null, 403);
            }

            $moderation = ChatModeration::updateOrCreate(
                [
                    'chat_room_id' => $chatRoom->id,
                    'user_id'      => $targetUser->id,
                    'type'         => 'ban',
                ],
                [
                    'reason'     => $validated['reason'] ?? null,
                    'created_by' => $request->user()->id,
                ]
            );

            return $this->successResponse($moderation, 'User banned from chat successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Resource not found.', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to ban user.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * POST /admin/roadmaps/{roadmap}/chat/unban
     */
    public function unban(Request $request, $roadmapId)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $chatRoom = $this->getChatRoom($roadmapId);
            if ($chatRoom instanceof \Illuminate\Http\JsonResponse) {
                return $chatRoom;
            }

            $deleted = ChatModeration::where('chat_room_id', $chatRoom->id)
                ->where('user_id', $validated['user_id'])
                ->where('type', 'ban')
                ->delete();

            if (!$deleted) {
                return $this->errorResponse('User is not banned in this chat room.', null, 404);
            }

            return $this->successResponse(null, 'User unbanned from chat successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to unban user.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * GET /admin/roadmaps/{roadmap}/chat/members
     */
    public function members(Request $request, $roadmapId)
    {
        try {
            $roadmap = Roadmap::findOrFail($roadmapId);
            $chatRoom = $roadmap->chatRoom;

            if (!$chatRoom) {
                return $this->errorResponse('Chat room not found for this roadmap.', null, 404);
            }

            // Get enrolled users
            $enrollments = RoadmapEnrollment::where('roadmap_id', $roadmap->id)
                ->with('user:id,username,email,profile_picture,role')
                ->paginate($request->get('per_page', 30));

            // Get all moderations for this room
            $moderations = ChatModeration::where('chat_room_id', $chatRoom->id)
                ->get()
                ->groupBy('user_id');

            $members = collect($enrollments->items())->map(function ($enrollment) use ($moderations) {
                $userModerations = $moderations->get($enrollment->user_id, collect());

                $isMuted = $userModerations->where('type', 'mute')
                    ->filter(function ($m) {
                        return is_null($m->muted_until) || $m->muted_until->isFuture();
                    })->isNotEmpty();

                $isBanned = $userModerations->where('type', 'ban')->isNotEmpty();

                return [
                    'user'        => $enrollment->user,
                    'enrolled_at' => $enrollment->created_at,
                    'status'      => $enrollment->status,
                    'is_muted'    => $isMuted,
                    'is_banned'   => $isBanned,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Chat members retrieved successfully',
                'data'    => $members->values(),
                'meta'    => [
                    'current_page' => $enrollments->currentPage(),
                    'last_page'    => $enrollments->lastPage(),
                    'per_page'     => $enrollments->perPage(),
                    'total'        => $enrollments->total(),
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Roadmap not found.', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve members.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /* ───── Private Helper ───── */

    private function getChatRoom($roadmapId)
    {
        $roadmap = Roadmap::find($roadmapId);

        if (!$roadmap) {
            return $this->errorResponse('Roadmap not found.', null, 404);
        }

        $chatRoom = $roadmap->chatRoom;

        if (!$chatRoom) {
            return $this->errorResponse('Chat room not found for this roadmap.', null, 404);
        }

        return $chatRoom;
    }
}

