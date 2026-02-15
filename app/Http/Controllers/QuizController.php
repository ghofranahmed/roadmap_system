<?php

namespace App\Http\Controllers;

use App\Models\LearningUnit;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\RoadmapEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * GET /units/{unitId}/quizzes
     * يرجع Quiz واحد فقط لأن عندك unique learning_unit_id
     */
    public function index(int $unitId)
    {
        $quiz = Quiz::where('learning_unit_id', $unitId)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $quiz
        ]);
    }

    /**
     * GET /quizzes/{quizId}
     * Start attempt:
     * - يتحقق من فتح الكويز (QuizPolicy: كل الدروس السابقة مكتملة)
     * - يرجّع الأسئلة بدون correct_answer
     * - ينشئ attempt فارغ (score=0)
     */
    public function startAttempt(Request $request, int $quizId)
    {
        $quiz = Quiz::with(['questions' => function ($q) {
            $q->select('id', 'quiz_id', 'question_text', 'options', 'order', 'question_xp');
        }])->findOrFail($quizId);

        // ✅ unlock by lessons completion
        $this->authorize('view', $quiz); // QuizPolicy

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'answers' => null,
            'score' => 0,
            'passed' => false,
        ]);

        return response()->json([
            'success' => true,
            'quiz' => [
                'id' => $quiz->id,
                'learning_unit_id' => $quiz->learning_unit_id,
                'min_xp' => (int)$quiz->min_xp, // threshold نجاح (إذا تبيه هكي)
                'max_xp' => (int)$quiz->max_xp, // أقصى نقاط للكويز
                'questions' => $quiz->questions,
            ],
            'attempt_id' => $attempt->id,
        ]);
    }

    /**
     * PUT /quiz-attempts/{attemptId}/submit
     * - يصحح الأسئلة
     * - يحدث attempt (مرة واحدة فقط)
     * - يزيد xp_points في enrollment (فقط من الاختبارات)
     * - نظام "أفضل سكّور": يزيد فقط الفرق لو المستخدم حسّن نتيجته
     */
    public function submitAttempt(Request $request, int $attemptId)
    {
        $request->validate([
            'answers' => ['required', 'array', 'min:1'],
        ]);

        $attempt = QuizAttempt::with('quiz.questions')->findOrFail($attemptId);

        // ✅ attempt belongs to user and not submitted yet
        $this->authorize('update', $attempt); // QuizAttemptPolicy

        $quiz = $attempt->quiz;

        // ✅ safety: ensure quiz is unlocked by lessons completion
        $this->authorize('view', $quiz); // QuizPolicy

        $studentAnswers = $request->input('answers');

        // ====== grading ======
        $score = 0;
        foreach ($quiz->questions as $question) {
            $qid = (string)$question->id;

            if (isset($studentAnswers[$qid]) && $studentAnswers[$qid] == $question->correct_answer) {
                $score += (int)$question->question_xp;
            }
        }

        $passed = $score >= (int)$quiz->min_xp;

        // earned points for roadmap = min(score, quiz.max_xp)
        $earnedPoints = min((int)$score, (int)$quiz->max_xp);

        DB::transaction(function () use ($attempt, $quiz, $studentAnswers, $score, $passed, $earnedPoints) {

            // 1) update attempt
            $attempt->answers = $studentAnswers;
            $attempt->score = $score;
            $attempt->passed = $passed;
            $attempt->save();

            // 2) add XP to enrollment (quizzes only)
            $unit = LearningUnit::find($quiz->learning_unit_id);
            if (!$unit) return;

            $enrollment = RoadmapEnrollment::where('user_id', $attempt->user_id)
                ->where('roadmap_id', $unit->roadmap_id)
                ->lockForUpdate()
                ->first();

            if (!$enrollment) return;

            // best previous score for this quiz (excluding current attempt)
            $prevBestScore = (int) QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $attempt->user_id)
                ->where('id', '!=', $attempt->id)
                ->max('score');

            $prevBestEarned = min($prevBestScore, (int)$quiz->max_xp);

            // only add improvement
            $delta = max(0, $earnedPoints - $prevBestEarned);

            if ($delta > 0) {
                $enrollment->xp_points += $delta;
                $enrollment->save();
            }
        });

        return response()->json([
            'success' => true,
            'attempt' => $attempt->fresh(),
            'score' => $score,
            'passed' => $passed,
            'earned_points' => $earnedPoints,
        ]);
    }

    /**
     * GET /quiz-attempts/{attemptId}
     */
    public function showAttempt(int $attemptId)
    {
        $attempt = QuizAttempt::with('quiz')->findOrFail($attemptId);

        $this->authorize('view', $attempt); // QuizAttemptPolicy

        return response()->json([
            'success' => true,
            'data' => $attempt
        ]);
    }

    /**
     * GET /quizzes/{quizId}/my-attempts
     */
    public function myAttempts(int $quizId)
    {
        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quizId)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attempts
        ]);
    }
}
