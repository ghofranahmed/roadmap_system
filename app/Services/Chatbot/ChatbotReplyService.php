<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotSession;
use App\Models\ChatbotSetting;
use App\Models\Lesson;
use App\Models\LessonTracking;
use App\Models\RoadmapEnrollment;
use Illuminate\Support\Facades\Log;

class ChatbotReplyService
{
    private LLMProviderInterface $provider;

    public function __construct(LLMProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Generate an assistant reply for the given session and user message.
     *
     * @return array ['reply' => string, 'tokens_used' => int|null]
     */
    public function generateReply(ChatbotSession $session, string $userMessage): array
    {
        // Check if chatbot is enabled (from DB settings)
        $settings = ChatbotSetting::getSettings();
        if (!$settings->is_enabled) {
            return [
                'reply' => 'Smart Teacher is temporarily disabled by admin. Please try again later.',
                'tokens_used' => null,
            ];
        }

        // 1. Build conversation context (last N messages)
        // Use DB setting if available, otherwise fallback to config
        $limit = $settings->max_context_messages ?? config('services.chatbot.max_context_messages', 10);
        $context = $session->messages()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get(['role', 'body'])
            ->reverse()
            ->values()
            ->map(fn ($m) => ['role' => $m->role, 'body' => $m->body])
            ->toArray();

        // 2. Build metadata from student's real enrollment/progress data
        $metadata = $this->buildMetadata($session->user_id);

        // 3. Build system prompt and attach to metadata
        $metadata['system_prompt'] = $this->buildSystemPrompt($metadata, $settings);
        
        // 4. Call provider (STRICT: no fallback - let exceptions propagate)
        // If provider fails, the exception will be caught by Laravel's exception handler
        // and returned as a proper API error response.
        return $this->provider->chat($context, $userMessage, $metadata);
    }

    /**
     * Query the student's active enrollments and find their next incomplete lesson.
     */
    private function buildMetadata(int $userId): array
    {
        $enrollments = RoadmapEnrollment::where('user_id', $userId)
            ->where('status', 'active')
            ->with('roadmap:id,title,level')
            ->get();

        if ($enrollments->isEmpty()) {
            return ['enrollments' => [], 'next_lessons' => []];
        }

        $completedLessonIds = LessonTracking::where('user_id', $userId)
            ->where('is_complete', true)
            ->pluck('lesson_id');

        $enrollmentData = [];
        $nextLessons    = [];

        foreach ($enrollments as $enrollment) {
            $roadmap = $enrollment->roadmap;
            if (!$roadmap) {
                continue;
            }

            // Count total active lessons in this roadmap
            $totalLessons = Lesson::whereHas('learningUnit', function ($q) use ($roadmap) {
                $q->where('roadmap_id', $roadmap->id);
            })->where('is_active', true)->count();

            // Count completed lessons
            $completedCount = LessonTracking::where('user_id', $userId)
                ->where('is_complete', true)
                ->whereHas('lesson.learningUnit', function ($q) use ($roadmap) {
                    $q->where('roadmap_id', $roadmap->id);
                })->count();

            $enrollmentData[] = [
                'roadmap_id'       => $roadmap->id,
                'roadmap_title'    => $roadmap->title,
                'level'            => $roadmap->level,
                'completed_lessons' => $completedCount,
                'total_lessons'    => $totalLessons,
            ];

            // Find next incomplete lesson (ordered by unit position, then lesson position)
            $nextLesson = Lesson::select(
                    'lessons.id',
                    'lessons.title',
                    'lessons.position',
                    'learning_units.title as unit_title',
                    'learning_units.position as unit_position'
                )
                ->join('learning_units', 'lessons.learning_unit_id', '=', 'learning_units.id')
                ->where('learning_units.roadmap_id', $roadmap->id)
                ->where('lessons.is_active', true)
                ->whereNotIn('lessons.id', $completedLessonIds)
                ->orderBy('learning_units.position')
                ->orderBy('lessons.position')
                ->first();

            if ($nextLesson) {
                $nextLessons[$roadmap->id] = [
                    'lesson_title' => $nextLesson->title,
                    'unit_title'   => $nextLesson->unit_title,
                ];
            }
        }

        return [
            'enrollments'  => $enrollmentData,
            'next_lessons' => $nextLessons,
        ];
    }

    /**
     * Build the system prompt that tells the AI how to behave, enriched with student data.
     */
    private function buildSystemPrompt(array $metadata, ChatbotSetting $settings): string
    {
        // Use custom template if provided, otherwise use default
        if (!empty($settings->system_prompt_template)) {
            $prompt = $settings->system_prompt_template;
        } else {
            $prompt  = "You are \"Smart Teacher\", a friendly and knowledgeable programming tutor "
                     . "inside a roadmap-based learning platform. Help students learn programming, "
                     . "answer questions, and guide their learning journey.\n\n";
            $prompt .= "Rules:\n";
            $prompt .= "- Be concise (2-4 short paragraphs max).\n";
            $prompt .= "- Use simple language appropriate for the student's level.\n";
            $prompt .= "- When explaining code, use short examples.\n";
            $prompt .= "- Be encouraging and supportive.\n";
            $prompt .= "- Answer in the same language the student uses (Arabic or English).\n";
            $prompt .= "- Do not discuss topics unrelated to programming and technology.\n";
        }

        $enrollments = $metadata['enrollments'] ?? [];
        $nextLessons = $metadata['next_lessons'] ?? [];

        if (!empty($enrollments)) {
            $prompt .= "\nStudent's current status:\n";
            foreach ($enrollments as $e) {
                $prompt .= "- Enrolled in \"{$e['roadmap_title']}\" ({$e['level']} level), "
                         . "completed {$e['completed_lessons']}/{$e['total_lessons']} lessons.\n";
            }

            if (!empty($nextLessons)) {
                $prompt .= "\nNext recommended lessons:\n";
                foreach ($nextLessons as $lesson) {
                    $prompt .= "- \"{$lesson['lesson_title']}\" in unit \"{$lesson['unit_title']}\"\n";
                }
            }
        }

        return $prompt;
    }
}

