<?php

namespace App\Http\Controllers;

use App\Models\LearningUnit;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\RoadmapEnrollment;
use App\Http\Requests\CreateQuizAttemptRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * GET /roadmaps/{roadmapId}/quizzes
     * عرض جميع الكويزات الخاصة بخارطة طريق معينة (مجمعة حسب الوحدة)
     */
    public function roadmapIndex(int $roadmapId)
    {
        $units = LearningUnit::where('roadmap_id', $roadmapId)
            ->where('is_active', true)
            ->orderBy('position')
            ->with(['quizzes' => function ($q) {
                $q->where('is_active', true)
                  ->select('id', 'learning_unit_id', 'is_active', 'max_xp', 'min_xp', 'created_at');
            }])
            ->get(['id', 'title', 'position', 'roadmap_id']);

        // Group quizzes under their unit, only include units that have quizzes
        $result = $units
            ->filter(fn ($unit) => $unit->quizzes->isNotEmpty())
            ->values()
            ->map(fn ($unit) => [
                'unit_id'   => $unit->id,
                'unit_title' => $unit->title,
                'position'  => $unit->position,
                'quizzes'   => $unit->quizzes,
            ]);

        return $this->successResponse([
            'roadmap_id'  => (int) $roadmapId,
            'total_quizzes' => $result->sum(fn ($u) => count($u['quizzes'])),
            'units'       => $result,
        ], 'تم جلب الكويزات بنجاح');
    }

    /**
     * GET /units/{unitId}/quizzes
     * يرجع Quiz واحد فقط لأن عندك unique learning_unit_id
     */
    public function index(int $unitId)
    {
        $quiz = Quiz::where('learning_unit_id', $unitId)
            ->where('is_active', true)
            ->with('learningUnit:id,title,roadmap_id')
            ->first();

        return $this->successResponse($quiz);
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
            $q->select('id', 'quiz_id', 'question_text', 'options', 'order', 'question_xp')
              ->orderBy('order');
        }, 'learningUnit:id,title,roadmap_id'])
        ->findOrFail($quizId);

        // ✅ unlock by lessons completion
        $this->authorize('view', $quiz); // QuizPolicy

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'answers' => null,
            'score' => 0,
            'passed' => false,
        ]);

        return $this->successResponse([
            'quiz' => [
                'id' => $quiz->id,
                'learning_unit_id' => $quiz->learning_unit_id,
                'min_xp' => (int)$quiz->min_xp,
                'max_xp' => (int)$quiz->max_xp,
                'questions' => $quiz->questions,
            ],
            'attempt_id' => $attempt->id,
        ], 'Quiz attempt started successfully', 201);
    }

    /**
     * GET /quizzes/{quizId}/details
     * Read-only endpoint to view quiz details with questions (ordered by order)
     * Does NOT create an attempt - use this for viewing quiz before starting
     * Returns: quiz object + questions array (without correct_answer)
     */
    public function details(int $quizId)
    {
        $quiz = Quiz::with(['questions' => function ($q) {
            $q->select('id', 'quiz_id', 'question_text', 'options', 'order', 'question_xp')
              ->orderBy('order');
        }, 'learningUnit:id,title,roadmap_id'])
        ->where('is_active', true)
        ->findOrFail($quizId);

        // ✅ unlock by lessons completion
        $this->authorize('view', $quiz); // QuizPolicy

        // Build response structure
        $response = [
            'quiz' => [
                'id' => $quiz->id,
                'learning_unit_id' => $quiz->learning_unit_id,
                'min_xp' => (int)$quiz->min_xp,
                'max_xp' => (int)$quiz->max_xp,
            ],
            'questions' => $quiz->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'quiz_id' => $question->quiz_id,
                    'question_text' => $question->question_text,
                    'options' => $question->options ?? [], // Already parsed as array via model cast
                    'order' => $question->order,
                    'question_xp' => (int)$question->question_xp,
                ];
            })->values()->toArray(), // Ensure it's always an array, not null
        ];

        return $this->successResponse($response);
    }

    /**
     * POST /quizzes/{quizId}/attempts
     * Create a quiz attempt and optionally submit answers in one call
     * - If answers provided: creates attempt, computes score, and saves everything
     * - If answers NOT provided: creates attempt only (returns attempt_id for later submission)
     * Backend computes score from correct_answer (not exposed to frontend)
     */
    public function createAttempt(CreateQuizAttemptRequest $request, int $quizId)
    {
        $quiz = Quiz::with(['questions' => function ($q) {
            $q->select('id', 'quiz_id', 'question_text', 'options', 'order', 'question_xp', 'correct_answer')
              ->orderBy('order');
        }])
        ->where('is_active', true)
        ->findOrFail($quizId);

        // ✅ unlock by lessons completion
        $this->authorize('view', $quiz); // QuizPolicy

        $studentAnswers = $request->input('answers');
        $hasAnswers = !empty($studentAnswers) && is_array($studentAnswers);

        // Initialize attempt data
        $attemptData = [
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'answers' => $hasAnswers ? $studentAnswers : null,
            'score' => 0,
            'passed' => false,
        ];

        // If answers provided, compute score
        if ($hasAnswers) {
            $score = 0;
            foreach ($quiz->questions as $question) {
                $qid = (string)$question->id;

                if (isset($studentAnswers[$qid]) && $studentAnswers[$qid] == $question->correct_answer) {
                    $score += (int)$question->question_xp;
                }
            }

            $attemptData['score'] = $score;
            $attemptData['passed'] = $score >= (int)$quiz->min_xp;
        }

        // Create attempt and handle XP if answers were submitted
        $attempt = DB::transaction(function () use ($attemptData, $quiz, $hasAnswers) {
            $attempt = QuizAttempt::create($attemptData);

            // If answers were submitted, handle XP points
            if ($hasAnswers) {
                $earnedPoints = min((int)$attempt->score, (int)$quiz->max_xp);

                $unit = LearningUnit::find($quiz->learning_unit_id);
                if ($unit) {
                    $enrollment = RoadmapEnrollment::where('user_id', $attempt->user_id)
                        ->where('roadmap_id', $unit->roadmap_id)
                        ->lockForUpdate()
                        ->first();

                    if ($enrollment) {
                        // Policy A: Last submitted attempt replaces previous credited XP
                        // Find the previous completed attempt (most recent by updated_at, fallback created_at)
                        $previousAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                            ->where('user_id', $attempt->user_id)
                            ->where('id', '!=', $attempt->id)
                            ->whereNotNull('answers')  // Completed attempt = answers IS NOT NULL
                            ->orderByDesc('updated_at')  // Most recent by updated_at
                            ->orderByDesc('created_at')  // Fallback to created_at
                            ->first();

                        // Calculate previous XP using the same formula as new_xp
                        $prevXp = 0;
                        if ($previousAttempt) {
                            $prevXp = min((int)$previousAttempt->score, (int)$quiz->max_xp);
                        }

                        // Calculate delta: new_xp - prev_xp (can be negative)
                        $delta = $earnedPoints - $prevXp;

                        // Update enrollment XP: never allow negative XP
                        $newXp = max(0, $enrollment->xp_points + $delta);
                        $enrollment->xp_points = $newXp;
                        $enrollment->save();
                    }
                }
            }

            return $attempt;
        });

        // Build response
        $response = [
            'attempt' => [
                'id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'user_id' => $attempt->user_id,
                'score' => $attempt->score,
                'passed' => $attempt->passed,
                'created_at' => $attempt->created_at?->toISOString(),
            ],
        ];

        // If answers were submitted, include additional info
        if ($hasAnswers) {
            $earnedPoints = min((int)$attempt->score, (int)$quiz->max_xp);
            $response['earned_points'] = $earnedPoints;
        }

        return $this->successResponse($response, $hasAnswers ? 'Quiz attempt created and submitted successfully' : 'Quiz attempt created successfully', 201);
    }

    /**
     * PUT /quiz-attempts/{attemptId}/submit
     * - يصحح الأسئلة
     * - يحدث attempt (مرة واحدة فقط)
     * - يزيد xp_points في enrollment (فقط من الاختبارات)
     * - نظام "أفضل سكّور": يزيد فقط الفرق لو المستخدم حسّن نتيجته
     */
    public function submitAttempt(\App\Http\Requests\SubmitQuizAttemptRequest $request, int $attemptId)
    {

        $attempt = QuizAttempt::with('quiz.questions')->findOrFail($attemptId);

        // ✅ Idempotency check: prevent resubmitting the same attempt
        if ($attempt->answers !== null || $attempt->score > 0) {
            return $this->errorResponse(
                'This attempt has already been submitted and cannot be resubmitted.',
                null,
                422
            );
        }

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

            // 2) Handle XP reconciliation for quiz retakes (Policy A: Last attempt replaces previous)
            $unit = LearningUnit::find($quiz->learning_unit_id);
            if (!$unit) return;

            $enrollment = RoadmapEnrollment::where('user_id', $attempt->user_id)
                ->where('roadmap_id', $unit->roadmap_id)
                ->lockForUpdate()
                ->first();

            if (!$enrollment) return;

            // Policy A: Last submitted attempt replaces previous credited XP
            // Find the previous completed attempt (most recent by updated_at, fallback created_at)
            $previousAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $attempt->user_id)
                ->where('id', '!=', $attempt->id)
                ->whereNotNull('answers')  // Completed attempt = answers IS NOT NULL
                ->orderByDesc('updated_at')  // Most recent by updated_at
                ->orderByDesc('created_at')  // Fallback to created_at
                ->first();

            // Calculate previous XP using the same formula as new_xp
            $prevXp = 0;
            if ($previousAttempt) {
                $prevXp = min((int)$previousAttempt->score, (int)$quiz->max_xp);
            }

            // Calculate delta: new_xp - prev_xp (can be negative)
            $delta = $earnedPoints - $prevXp;

            // Update enrollment XP: never allow negative XP
            $newXp = max(0, $enrollment->xp_points + $delta);
            $enrollment->xp_points = $newXp;
            $enrollment->save();
        });

        return $this->successResponse([
            'attempt' => $attempt->fresh(),
            'score' => $score,
            'passed' => $passed,
            'earned_points' => $earnedPoints,
        ], 'Quiz attempt submitted successfully');
    }

    /**
     * GET /quiz-attempts/{attemptId}
     */
    public function showAttempt(int $attemptId)
    {
        $attempt = QuizAttempt::with(['quiz.questions', 'quiz.learningUnit:id,title,roadmap_id'])
            ->findOrFail($attemptId);

        $this->authorize('view', $attempt); // QuizAttemptPolicy

        return $this->successResponse($attempt);
    }

    /**
     * GET /quizzes/{quizId}/my-attempts
     */
    public function myAttempts(int $quizId)
    {
        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quizId)
            ->with('quiz:id,learning_unit_id,min_xp,max_xp')
            ->orderByDesc('created_at')
            ->paginate(request()->get('per_page', 15));

        return $this->paginatedResponse($attempts, 'Attempts retrieved successfully');
    }
}
