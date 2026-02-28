<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatModeration;
use App\Models\Roadmap;
use App\Models\RoadmapEnrollment;
use App\Models\User;
use Illuminate\Http\Request;

class ChatModerationController extends Controller
{
    /**
     * Ensure only Normal Admin can access chat moderation.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || !$user->isNormalAdmin()) {
                abort(403, 'Unauthorized. Admin access required.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of roadmaps with chat rooms.
     * GET /admin/chat-moderation
     */
    public function index(Request $request)
    {
        // Get all roadmaps that have chat rooms
        $roadmaps = Roadmap::whereHas('chatRoom')
            ->with('chatRoom')
            ->withCount('enrollments')
            ->orderBy('title')
            ->paginate(15);

        return view('admin.chat-moderation.index', compact('roadmaps'));
    }

    /**
     * Display members of a specific roadmap's chat room.
     * GET /admin/chat-moderation/roadmaps/{roadmap}/members
     */
    public function members(Request $request, Roadmap $roadmap)
    {
        $chatRoom = $roadmap->chatRoom;

        if (!$chatRoom) {
            return redirect()
                ->route('admin.chat-moderation.index')
                ->with('error', 'Chat room not found for this roadmap.');
        }

        // Get enrolled users (members) - only include enrollments with existing users
        $enrollments = RoadmapEnrollment::where('roadmap_id', $roadmap->id)
            ->whereHas('user')
            ->with('user:id,username,email,profile_picture,role')
            ->paginate(30);

        // Get all moderations for this room
        $moderations = ChatModeration::where('chat_room_id', $chatRoom->id)
            ->with('moderator:id,username')
            ->get()
            ->groupBy('user_id');

        // Build member data with moderation status
        // Filter out any enrollments where user is still null (defensive check)
        $members = collect($enrollments->items())
            ->filter(function ($enrollment) {
                return $enrollment->user !== null;
            })
            ->map(function ($enrollment) use ($moderations) {
                $userModerations = $moderations->get($enrollment->user_id, collect());

                $isMuted = $userModerations->where('type', 'mute')
                    ->filter(function ($m) {
                        return is_null($m->muted_until) || $m->muted_until->isFuture();
                    })->isNotEmpty();

                $isBanned = $userModerations->where('type', 'ban')->isNotEmpty();

                $muteRecord = $userModerations->where('type', 'mute')->first();
                $banRecord = $userModerations->where('type', 'ban')->first();

                return [
                    'enrollment' => $enrollment,
                    'user' => $enrollment->user,
                    'is_muted' => $isMuted,
                    'is_banned' => $isBanned,
                    'mute_record' => $muteRecord,
                    'ban_record' => $banRecord,
                ];
            });

        return view('admin.chat-moderation.members', compact('roadmap', 'chatRoom', 'members', 'enrollments'));
    }

    /**
     * Mute a user in a chat room.
     * POST /admin/chat-moderation/roadmaps/{roadmap}/mute
     */
    public function mute(Request $request, Roadmap $roadmap)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'reason' => 'nullable|string|max:500',
            'muted_until' => 'nullable|date|after:now',
        ]);

        $chatRoom = $roadmap->chatRoom;

        if (!$chatRoom) {
            return redirect()
                ->route('admin.chat-moderation.members', $roadmap)
                ->with('error', 'Chat room not found for this roadmap.');
        }

        $targetUser = User::findOrFail($validated['user_id']);

        // Prevent muting other admins
        if (in_array($targetUser->role, ['admin', 'tech_admin'])) {
            return redirect()
                ->route('admin.chat-moderation.members', $roadmap)
                ->with('error', 'Cannot mute an admin user.');
        }

        ChatModeration::updateOrCreate(
            [
                'chat_room_id' => $chatRoom->id,
                'user_id' => $targetUser->id,
                'type' => 'mute',
            ],
            [
                'muted_until' => $validated['muted_until'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'created_by' => $request->user()->id,
            ]
        );

        return redirect()
            ->route('admin.chat-moderation.members', $roadmap)
            ->with('success', 'User muted successfully.');
    }

    /**
     * Unmute a user in a chat room.
     * POST /admin/chat-moderation/roadmaps/{roadmap}/unmute
     */
    public function unmute(Request $request, Roadmap $roadmap)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $chatRoom = $roadmap->chatRoom;

        if (!$chatRoom) {
            return redirect()
                ->route('admin.chat-moderation.members', $roadmap)
                ->with('error', 'Chat room not found for this roadmap.');
        }

        $deleted = ChatModeration::where('chat_room_id', $chatRoom->id)
            ->where('user_id', $validated['user_id'])
            ->where('type', 'mute')
            ->delete();

        if (!$deleted) {
            return redirect()
                ->route('admin.chat-moderation.members', $roadmap)
                ->with('error', 'User is not muted in this chat room.');
        }

        return redirect()
            ->route('admin.chat-moderation.members', $roadmap)
            ->with('success', 'User unmuted successfully.');
    }

    /**
     * Ban a user from a chat room.
     * POST /admin/chat-moderation/roadmaps/{roadmap}/ban
     */
    public function ban(Request $request, Roadmap $roadmap)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $chatRoom = $roadmap->chatRoom;

        if (!$chatRoom) {
            return redirect()
                ->route('admin.chat-moderation.members', $roadmap)
                ->with('error', 'Chat room not found for this roadmap.');
        }

        $targetUser = User::findOrFail($validated['user_id']);

        // Prevent banning other admins
        if (in_array($targetUser->role, ['admin', 'tech_admin'])) {
            return redirect()
                ->route('admin.chat-moderation.members', $roadmap)
                ->with('error', 'Cannot ban an admin user.');
        }

        ChatModeration::updateOrCreate(
            [
                'chat_room_id' => $chatRoom->id,
                'user_id' => $targetUser->id,
                'type' => 'ban',
            ],
            [
                'reason' => $validated['reason'] ?? null,
                'created_by' => $request->user()->id,
            ]
        );

        return redirect()
            ->route('admin.chat-moderation.members', $roadmap)
            ->with('success', 'User banned from chat successfully.');
    }

    /**
     * Unban a user from a chat room.
     * POST /admin/chat-moderation/roadmaps/{roadmap}/unban
     */
    public function unban(Request $request, Roadmap $roadmap)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $chatRoom = $roadmap->chatRoom;

        if (!$chatRoom) {
            return redirect()
                ->route('admin.chat-moderation.members', $roadmap)
                ->with('error', 'Chat room not found for this roadmap.');
        }

        $deleted = ChatModeration::where('chat_room_id', $chatRoom->id)
            ->where('user_id', $validated['user_id'])
            ->where('type', 'ban')
            ->delete();

        if (!$deleted) {
            return redirect()
                ->route('admin.chat-moderation.members', $roadmap)
                ->with('error', 'User is not banned in this chat room.');
        }

        return redirect()
            ->route('admin.chat-moderation.members', $roadmap)
            ->with('success', 'User unbanned from chat successfully.');
    }
}

