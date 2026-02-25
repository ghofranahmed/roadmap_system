<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatModeration;
use App\Models\ChatRoom;
use App\Models\Roadmap;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatMessageController extends Controller
{
    /**
     * GET /roadmaps/{roadmap}/chat/messages
     * Paginated list — enrolled users OR admins
     */
    public function index(Request $request, $roadmapId)
    {
        try {
            $user = $request->user();
            $roadmap = Roadmap::findOrFail($roadmapId);

            // Access: enrolled OR admin/tech_admin
            if (!$this->canAccessChat($user, $roadmap)) {
                return $this->errorResponse(
                    'You must be enrolled in this roadmap to view its chat.',
                    null,
                    403
                );
            }

            $chatRoom = $roadmap->chatRoom;

            if (!$chatRoom || !$chatRoom->is_active) {
                return $this->errorResponse(
                    'Chat room is not available for this roadmap.',
                    null,
                    404
                );
            }

            $messages = ChatMessage::where('chat_room_id', $chatRoom->id)
                ->with('user:id,username,profile_picture')
                ->orderByDesc('created_at')
                ->paginate($request->get('per_page', 30));

            return $this->paginatedResponse($messages, 'Messages retrieved successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Roadmap not found.', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve messages.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * POST /roadmaps/{roadmap}/chat/messages
     * Send message — enrolled, not muted/banned
     */
    public function store(Request $request, $roadmapId)
    {
        try {
            $user = $request->user();
            $roadmap = Roadmap::findOrFail($roadmapId);

            // Must be enrolled
            if (!$user->hasEnrolled($roadmap->id)) {
                return $this->errorResponse(
                    'You must be enrolled in this roadmap to send messages.',
                    null,
                    403
                );
            }

            $chatRoom = $roadmap->chatRoom;

            if (!$chatRoom || !$chatRoom->is_active) {
                return $this->errorResponse(
                    'Chat room is not available for this roadmap.',
                    null,
                    404
                );
            }

            // Check mute / ban
            $moderationCheck = $this->checkModeration($chatRoom->id, $user->id);
            if ($moderationCheck) {
                return $moderationCheck;
            }

            $validated = $request->validate([
                'content' => 'required|string|max:2000',
            ]);

            $message = ChatMessage::create([
                'chat_room_id' => $chatRoom->id,
                'user_id'      => $user->id,
                'content'      => $validated['content'],
                'sent_at'      => now(),
            ]);

            $message->load('user:id,username,profile_picture');

            return $this->successResponse($message, 'Message sent successfully', 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Roadmap not found.', null, 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to send message.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * PATCH /chat/messages/{message}
     * Edit message — owner or admin
     */
    public function update(Request $request, $messageId)
    {
        try {
            $user = $request->user();
            $message = ChatMessage::findOrFail($messageId);

            // Policy check
            if ($user->id !== $message->user_id && !in_array($user->role, ['admin', 'tech_admin'])) {
                return $this->errorResponse(
                    'You are not authorized to edit this message.',
                    null,
                    403
                );
            }

            $validated = $request->validate([
                'content' => 'required|string|max:2000',
            ]);

            $message->update([
                'content'   => $validated['content'],
                'edited_at' => now(),
            ]);

            $message->load('user:id,username,profile_picture');

            return $this->successResponse($message, 'Message updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Message not found.', null, 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update message.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * DELETE /chat/messages/{message}
     * Delete message (soft) — owner or admin
     */
    public function destroy(Request $request, $messageId)
    {
        try {
            $user = $request->user();
            $message = ChatMessage::findOrFail($messageId);

            // Policy check
            if ($user->id !== $message->user_id && !in_array($user->role, ['admin', 'tech_admin'])) {
                return $this->errorResponse(
                    'You are not authorized to delete this message.',
                    null,
                    403
                );
            }

            $message->delete(); // soft delete

            return $this->successResponse(null, 'Message deleted successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Message not found.', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete message.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /* ───── Private Helpers ───── */

    private function canAccessChat($user, Roadmap $roadmap): bool
    {
        // Admins can always view chat
        if (in_array($user->role, ['admin', 'tech_admin'])) {
            return true;
        }

        return $user->hasEnrolled($roadmap->id);
    }

    private function checkModeration(int $chatRoomId, int $userId)
    {
        // Check ban
        $ban = ChatModeration::where('chat_room_id', $chatRoomId)
            ->where('user_id', $userId)
            ->where('type', 'ban')
            ->first();

        if ($ban) {
            return $this->errorResponse(
                'You are banned from this chat room.',
                ['reason' => $ban->reason],
                403
            );
        }

        // Check active mute (no muted_until OR muted_until still in the future)
        $mute = ChatModeration::where('chat_room_id', $chatRoomId)
            ->where('user_id', $userId)
            ->where('type', 'mute')
            ->where(function ($q) {
                $q->whereNull('muted_until')
                  ->orWhere('muted_until', '>', now());
            })
            ->first();

        if ($mute) {
            $until = $mute->muted_until
                ? $mute->muted_until->toDateTimeString()
                : 'indefinitely';

            return $this->errorResponse(
                "You are muted in this chat room until {$until}.",
                [
                    'reason'      => $mute->reason,
                    'muted_until' => $mute->muted_until,
                ],
                403
            );
        }

        return null; // no restriction
    }

    /**
     * GET /community/{chatRoomId}/messages
     * Get messages by chat room ID (alternative to roadmap-scoped endpoint)
     */
    public function indexByRoom(Request $request, $chatRoomId)
    {
        try {
            $user = $request->user();
            $chatRoom = ChatRoom::with('roadmap')->findOrFail($chatRoomId);

            if (!$chatRoom->is_active) {
                return $this->errorResponse(
                    'Chat room is not available.',
                    null,
                    404
                );
            }

            // Access: enrolled OR admin/tech_admin
            if (!$this->canAccessChat($user, $chatRoom->roadmap)) {
                return $this->errorResponse(
                    'You must be enrolled in this roadmap to view its chat.',
                    null,
                    403
                );
            }

            $messages = ChatMessage::where('chat_room_id', $chatRoom->id)
                ->with('user:id,username,profile_picture')
                ->orderByDesc('created_at')
                ->paginate($request->get('per_page', 30));

            return $this->paginatedResponse($messages, 'Messages retrieved successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Chat room not found.', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve messages.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * POST /community/{chatRoomId}/messages
     * Send message by chat room ID (alternative to roadmap-scoped endpoint)
     */
    public function storeByRoom(Request $request, $chatRoomId)
    {
        try {
            $user = $request->user();
            $chatRoom = ChatRoom::with('roadmap')->findOrFail($chatRoomId);

            if (!$chatRoom->is_active) {
                return $this->errorResponse(
                    'Chat room is not available.',
                    null,
                    404
                );
            }

            // Must be enrolled
            if (!$user->hasEnrolled($chatRoom->roadmap->id)) {
                return $this->errorResponse(
                    'You must be enrolled in this roadmap to send messages.',
                    null,
                    403
                );
            }

            // Check mute / ban
            $moderationCheck = $this->checkModeration($chatRoom->id, $user->id);
            if ($moderationCheck) {
                return $moderationCheck;
            }

            $validated = $request->validate([
                'content' => 'required|string|max:2000',
            ]);

            $message = ChatMessage::create([
                'chat_room_id' => $chatRoom->id,
                'user_id'      => $user->id,
                'content'      => $validated['content'],
                'sent_at'      => now(),
            ]);

            $message->load('user:id,username,profile_picture');

            return $this->successResponse($message, 'Message sent successfully', 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Chat room not found.', null, 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to send message.',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

