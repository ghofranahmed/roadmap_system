<?php

namespace App\Services\Chatbot;

class DummyProvider implements LLMProviderInterface
{
    /**
     * Rule-based reply engine (works offline, used for development & fallback).
     */
    public function chat(array $context, string $message, array $metadata = []): array
    {
        $lower = mb_strtolower($message);

        // Roadmap / progress intent
        $roadmapKeywords = [
            'next', 'roadmap', 'learn', 'progress', 'what should', 'where am i',
            'how far', 'my course', 'start with',
            'التالي', 'خريطة', 'تعلم', 'تقدم', 'ماذا أتعلم', 'وين وصلت',
        ];

        foreach ($roadmapKeywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return [
                    'reply'      => $this->buildRoadmapReply($metadata),
                    'tokens_used' => null,
                ];
            }
        }

        // Explanation / help intent
        $helpKeywords = ['explain', 'help', 'how to', 'what is', 'difference between',
                         'شرح', 'ساعدني', 'كيف', 'ما هو', 'الفرق بين'];

        foreach ($helpKeywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return [
                    'reply'      => "That's a great question! While I'm currently in basic mode, here's what I suggest:\n\n"
                                  . "1. Check the lesson resources in your current roadmap — they often cover this topic.\n"
                                  . "2. Try breaking the problem into smaller parts and tackle each one.\n"
                                  . "3. Practice with the challenges available in your learning unit.\n\n"
                                  . "Would you like me to check your current progress and suggest a specific lesson?",
                    'tokens_used' => null,
                ];
            }
        }

        // Default friendly fallback
        return [
            'reply'      => "I'm your Smart Teacher assistant! I can help you with:\n\n"
                          . "- Checking your learning progress (try: \"What should I learn next?\")\n"
                          . "- Answering programming questions\n"
                          . "- Guiding you through your roadmap\n\n"
                          . "What would you like to know?",
            'tokens_used' => null,
        ];
    }

    /**
     * Build a personalized reply using the student's enrollment & progress data.
     */
    private function buildRoadmapReply(array $metadata): string
    {
        $enrollments = $metadata['enrollments'] ?? [];
        $nextLessons = $metadata['next_lessons'] ?? [];

        if (empty($enrollments)) {
            return "You're not enrolled in any roadmaps yet. "
                 . "I recommend browsing the available roadmaps and enrolling in one that matches your interests. "
                 . "Once enrolled, I can guide you through your learning journey!";
        }

        $reply = "Here's your current learning status:\n\n";

        foreach ($enrollments as $enrollment) {
            $title     = $enrollment['roadmap_title'];
            $completed = $enrollment['completed_lessons'];
            $total     = $enrollment['total_lessons'];
            $level     = $enrollment['level'];

            $reply .= "📚 {$title} ({$level}): {$completed}/{$total} lessons completed\n";

            $roadmapId = $enrollment['roadmap_id'];
            if (isset($nextLessons[$roadmapId])) {
                $next = $nextLessons[$roadmapId];
                $reply .= "👉 Next: \"{$next['lesson_title']}\" in unit \"{$next['unit_title']}\"\n";
            }
            $reply .= "\n";
        }

        $reply .= "Keep going! Consistency is the key to mastering programming. 🚀";

        return $reply;
    }
}

