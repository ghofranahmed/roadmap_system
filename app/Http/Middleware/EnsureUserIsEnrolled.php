<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEnrolled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $roadmapId = $this->extractRoadmapId($request);

        if (!$roadmapId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not determine roadmap from the request.',
            ], 400);
        }

        // التحقق من الاشتراك
        if (!$user->hasEnrolled($roadmapId)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in the roadmap associated with this content.',
            ], 403);
        }

        return $next($request);
    }

    /**
     * استخراج roadmap_id من الرابط.
     */
    private function extractRoadmapId(Request $request): ?int
    {
        // 1. عن طريق roadmapId نفسه
        if ($roadmapId = $request->route('roadmapId')) {
            return (int) $roadmapId;
        }

        // 2. عن طريق unitId
        if ($unitId = $request->route('unitId')) {
            $unit = \App\Models\LearningUnit::find($unitId);
            return $unit?->roadmap_id;
        }

        // 3. عن طريق lessonId
        if ($lessonId = $request->route('lessonId')) {
            $lesson = \App\Models\Lesson::with('learningUnit')->find($lessonId);
            return $lesson?->learningUnit?->roadmap_id;
        }

        // 4. عن طريق subLessonId
        if ($subLessonId = $request->route('subLessonId')) {
            $subLesson = \App\Models\SubLesson::with('lesson.learningUnit')->find($subLessonId);
            return $subLesson?->lesson?->learningUnit?->roadmap_id;
        }

        // 5. عن طريق quizId (نظام الاختبارات)
        if ($quizId = $request->route('quizId')) {
            $quiz = \App\Models\Quiz::with('learningUnit')->find($quizId);
            return $quiz?->learningUnit?->roadmap_id;
        }

        // 6. عن طريق attemptId (محاولة اختبار)
        if ($attemptId = $request->route('attemptId')) {
            $attempt = \App\Models\QuizAttempt::with('quiz.learningUnit')->find($attemptId);
            return $attempt?->quiz?->learningUnit?->roadmap_id;
        }

        // ===== الحالات الجديدة للتحديات =====

        // 7. عن طريق challenge (model binding) - استخدم النموذج مباشرة
        if ($challenge = $request->route('challenge')) {
            // تأكد من أن $challenge هو نموذج Challenge
            if ($challenge instanceof \App\Models\Challenge) {
                return $challenge->learningUnit?->roadmap_id;
            }
        }

        // 8. عن طريق attempt (model binding) - لمحاولات التحدي
        if ($attempt = $request->route('attempt')) {
            if ($attempt instanceof \App\Models\ChallengeAttempt) {
                return $attempt->challenge?->learningUnit?->roadmap_id;
            }
        }

      // challengeId
if ($challengeId = $request->route('challengeId')) {
    $challenge = \App\Models\Challenge::with('learningUnit')->find($challengeId);
    return $challenge?->learningUnit?->roadmap_id;
}

// challengeAttemptId
if ($challengeAttemptId = $request->route('challengeAttemptId')) {
    $attempt = \App\Models\ChallengeAttempt::with('challenge.learningUnit')->find($challengeAttemptId);
    return $attempt?->challenge?->learningUnit?->roadmap_id;
}


        return null;
    }
}