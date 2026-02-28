<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateChatbotSessionRequest;
use App\Http\Requests\SendChatbotMessageRequest;
use App\Models\ChatbotSession;
use App\Models\ChatbotSetting;
use App\Services\Chatbot\ChatbotReplyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(private ChatbotReplyService $replyService)
    {
    }

    /**
     * List the authenticated user's chatbot sessions.
     * GET /chatbot/sessions
     */
    public function index(Request $request): JsonResponse
    {
        $sessions = ChatbotSession::forUser($request->user()->id)
            ->orderByDesc('last_activity_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($sessions, 'Sessions retrieved successfully');
    }

    /**
     * Create a new chatbot session.
     * POST /chatbot/sessions
     */
    public function store(CreateChatbotSessionRequest $request): JsonResponse
    {
        $session = ChatbotSession::create([
            'user_id' => $request->user()->id,
            'title'   => $request->validated()['title'] ?? null,
        ]);

        return $this->successResponse($session, 'Session created successfully', 201);
    }

    /**
     * List messages for a specific session (conversation history).
     * GET /chatbot/sessions/{id}/messages
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $session = $this->resolveUserSession($id, $request->user()->id);

        $messages = $session->messages()
            ->orderBy('created_at')
            ->paginate($request->get('per_page', 30));

        return $this->paginatedResponse($messages, 'Messages retrieved successfully');
    }

    /**
     * Send a message to a specific session.
     * POST /chatbot/sessions/{id}/messages
     */
    public function storeMessage(SendChatbotMessageRequest $request, int $id): JsonResponse
    {
        $session = $this->resolveUserSession($id, $request->user()->id);

        return $this->processMessage($session, $request->validated()['message']);
    }

    /**
     * Mobile-friendly: send a message, auto-create session if session_id is missing.
     * POST /chatbot/messages
     */
    public function sendMessage(SendChatbotMessageRequest $request): JsonResponse
    {
        $data   = $request->validated();
        $userId = $request->user()->id;

        if (!empty($data['session_id'])) {
            $session = $this->resolveUserSession($data['session_id'], $userId);
        } else {
            // Auto-create session with title from first 50 chars of message
            $session = ChatbotSession::create([
                'user_id' => $userId,
                'title'   => mb_substr($data['message'], 0, 50),
            ]);
        }

        return $this->processMessage($session, $data['message']);
    }

    /**
     * Delete a chatbot session (and all its messages via cascade).
     * DELETE /chatbot/sessions/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $session = $this->resolveUserSession($id, $request->user()->id);
        $session->delete();

        return $this->successResponse(null, 'Session deleted successfully');
    }

    // ─── Private Helpers ───

    /**
     * Core logic: store user message → generate reply → store assistant message → return both.
     */
    private function processMessage(ChatbotSession $session, string $message): JsonResponse
    {
        // Check if chatbot is enabled
        $settings = ChatbotSetting::getSettings();
        if (!$settings->is_enabled) {
            // Store user message even if disabled
            $userMessage = $session->messages()->create([
                'role' => 'user',
                'body' => $message,
            ]);
            
            // Store disabled message response
            $assistantMessage = $session->messages()->create([
                'role' => 'assistant',
                'body' => 'Smart Teacher is temporarily disabled by admin. Please try again later.',
                'tokens_used' => null,
            ]);

            $session->update(['last_activity_at' => now()]);

            return $this->successResponse([
                'session'           => $session->only(['id', 'title', 'last_activity_at']),
                'user_message'      => $userMessage,
                'assistant_message' => $assistantMessage,
            ], 'Chatbot is currently disabled', 200);
        }

        // Store user message
        $userMessage = $session->messages()->create([
            'role' => 'user',
            'body' => $message,
        ]);

        // Generate assistant reply via service layer
        $result = $this->replyService->generateReply($session, $message);

        // Store assistant message
        $assistantMessage = $session->messages()->create([
            'role'       => 'assistant',
            'body'       => $result['reply'],
            'tokens_used' => $result['tokens_used'],
        ]);

        // Update session activity
        $session->update(['last_activity_at' => now()]);

        return $this->successResponse([
            'session'           => $session->only(['id', 'title', 'last_activity_at']),
            'user_message'      => $userMessage,
            'assistant_message' => $assistantMessage,
        ], 'Reply generated successfully', 201);
    }

    /**
     * Find session by ID and verify ownership. Returns session or aborts with 403/404.
     */
    private function resolveUserSession(int $sessionId, int $userId): ChatbotSession
    {
        $session = ChatbotSession::find($sessionId);

        if (!$session) {
            abort(response()->json([
                'success' => false,
                'message' => 'Session not found.',
            ], 404));
        }

        if ($session->user_id !== $userId) {
            abort(response()->json([
                'success' => false,
                'message' => 'You can only access your own sessions.',
            ], 403));
        }

        return $session;
    }
}

