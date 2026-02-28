<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotSetting;
use App\Models\ChatbotSession;
use App\Models\ChatbotMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmartTeacherController extends Controller
{
    /**
     * Ensure only Normal Admin can access Smart Teacher management.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || !$user->isNormalAdmin()) {
                abort(403, 'Unauthorized. Normal Admin access required.');
            }
            return $next($request);
        });
    }

    /**
     * Display Smart Teacher settings form and basic stats.
     * GET /admin/smart-teacher
     */
    public function index()
    {
        $settings = ChatbotSetting::getSettings();
        
        // Get basic stats
        $totalSessions = ChatbotSession::count();
        $totalMessages = ChatbotMessage::count();
        $activeSessions = ChatbotSession::where('last_activity_at', '>=', now()->subDays(7))->count();
        
        return view('admin.smart-teacher.index', compact('settings', 'totalSessions', 'totalMessages', 'activeSessions'));
    }

    /**
     * Update Smart Teacher settings.
     * POST /admin/smart-teacher
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:openai,groq,gemini,dummy',
            'model_name' => 'nullable|string|max:255',
            'temperature' => 'required|numeric|min:0|max:2',
            'max_tokens' => 'required|integer|min:1|max:10000',
            'max_context_messages' => 'required|integer|min:1|max:100',
            'request_timeout' => 'required|integer|min:1|max:300',
            'is_enabled' => 'boolean',
            'system_prompt_template' => 'nullable|string|max:5000',
        ]);

        $settings = ChatbotSetting::getSettings();
        $validated['updated_by'] = $request->user()->id;
        $validated['is_enabled'] = $request->has('is_enabled');
        
        $settings->update($validated);

        return redirect()
            ->route('admin.smart-teacher.index')
            ->with('success', 'Smart Teacher settings updated successfully.');
    }

    /**
     * Display chatbot logs (last N sessions/messages).
     * GET /admin/smart-teacher/logs
     */
    public function logs(Request $request)
    {
        $perPage = $request->get('per_page', 30);
        
        $sessions = ChatbotSession::with(['user:id,username,email', 'messages'])
            ->withCount('messages')
            ->orderByDesc('last_activity_at')
            ->paginate($perPage);

        return view('admin.smart-teacher.logs', compact('sessions'));
    }

    /**
     * Show a specific chatbot session with all messages.
     * GET /admin/smart-teacher/sessions/{id}
     */
    public function showSession($id)
    {
        $session = ChatbotSession::with(['user:id,username,email', 'messages'])
            ->findOrFail($id);

        return view('admin.smart-teacher.show-session', compact('session'));
    }
}
