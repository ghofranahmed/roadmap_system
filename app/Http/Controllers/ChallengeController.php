<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitChallengeAttemptRequest;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\LearningUnit;
use App\Models\RoadmapEnrollment;
use App\Services\Compiler\CompilerServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChallengeController extends Controller
{
    use ApiResponse;

    public function __construct(private CompilerServiceInterface $compiler) {}

    /**
     * Get lock information for a challenge
     */
    private function getLockInfo(Challenge $challenge, int $userXp): array
    {
        $requiredXp = (int)$challenge->min_xp;
        $isLocked = $userXp < $requiredXp;
        $missingXp = max(0, $requiredXp - $userXp);

        return [
            'is_locked' => $isLocked,
            'required_xp' => $requiredXp,
            'user_xp' => $userXp,
            'missing_xp' => $missingXp,
        ];
    }

    /**
     * Get enrollment XP for user in challenge's roadmap
     */
    private function getUserXp(Challenge $challenge, int $userId): int
    {
        $unit = $challenge->learningUnit;
        if (!$unit) return 0;

        $enrollment = RoadmapEnrollment::where('user_id', $userId)
            ->where('roadmap_id', $unit->roadmap_id)
            ->first();

        return (int)($enrollment?->xp_points ?? 0);
    }

    /**
     * Normalize output for comparison (Option 2: stdout vs expected_output)
     * - Convert \r\n to \n
     * - Trim whitespace
     */
    private function normalizeOutput(string $output): string
    {
        // Normalize newlines: \r\n → \n
        $output = str_replace("\r\n", "\n", $output);
        // Trim whitespace (including trailing newlines)
        return trim($output);
    }

    // GET /units/{unitId}/challenges
    public function index(Request $request, int $unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);

        if ($unit->unit_type !== 'challenge') {
            return $this->errorResponse('This unit is not a challenge unit', null, 422);
        }

        $userId = $request->user()->id;

        $enrollment = RoadmapEnrollment::where('user_id', $userId)
            ->where('roadmap_id', $unit->roadmap_id)
            ->first();

        $userXp = (int)($enrollment?->xp_points ?? 0);

        $challenge = Challenge::where('learning_unit_id', $unitId)
            ->where('is_active', true)
            ->first();

        if (!$challenge) {
            return $this->successResponse([
                'challenge' => null,
                'user_xp' => $userXp,
            ]);
        }

        // Get lock information
        $lockInfo = $this->getLockInfo($challenge, $userXp);
        $isLocked = $lockInfo['is_locked'];

        // Build challenge data - exclude sensitive fields if locked
        $challengeData = [
            'id' => $challenge->id,
            'learning_unit_id' => $challenge->learning_unit_id,
            'title' => $challenge->title,
            'description' => $challenge->description,
            'language' => $challenge->language,
            'min_xp' => (int)$challenge->min_xp,
            'is_active' => $challenge->is_active,
        ];

        // Only include starter_code if unlocked
        if (!$isLocked) {
            $challengeData['starter_code'] = $challenge->starter_code;
        }
        // test_cases are never included (hidden in model)

        // Add lock information
        $challengeData = array_merge($challengeData, $lockInfo);

        return $this->successResponse([
            'challenge' => $challengeData,
        ]);
    }

    /**
     * GET /challenges/{challengeId}
     * Direct challenge details endpoint
     */
    public function show(Request $request, int $challengeId)
    {
        $challenge = Challenge::with('learningUnit')->findOrFail($challengeId);

        // Enforce access via policy (checks XP lock)
        if (!$request->user()->can('view', $challenge)) {
            $userXp = $this->getUserXp($challenge, $request->user()->id);
            $lockInfo = $this->getLockInfo($challenge, $userXp);

            return $this->errorResponse(
                'Challenge is locked. You need more XP to unlock this challenge.',
                $lockInfo,
                403
            );
        }

        $userXp = $this->getUserXp($challenge, $request->user()->id);
        $lockInfo = $this->getLockInfo($challenge, $userXp);
        $isLocked = $lockInfo['is_locked'];

        // Build challenge data
        $challengeData = [
            'id' => $challenge->id,
            'learning_unit_id' => $challenge->learning_unit_id,
            'title' => $challenge->title,
            'description' => $challenge->description,
            'language' => $challenge->language,
            'min_xp' => (int)$challenge->min_xp,
            'is_active' => $challenge->is_active,
        ];

        // Only include starter_code if unlocked
        if (!$isLocked) {
            $challengeData['starter_code'] = $challenge->starter_code;
        }
        // test_cases are never included (hidden in model)

        // Add lock information
        $challengeData = array_merge($challengeData, $lockInfo);

        return $this->successResponse($challengeData);
    }


    // POST /challenges/{challengeId}/attempts
    public function startAttempt(Request $request, int $challengeId)
    {
        $challenge = Challenge::with('learningUnit')->findOrFail($challengeId);

        // Check XP lock before authorization
        $userXp = $this->getUserXp($challenge, $request->user()->id);
        $lockInfo = $this->getLockInfo($challenge, $userXp);

        if ($lockInfo['is_locked']) {
            return $this->errorResponse(
                'Challenge is locked. You need more XP to unlock this challenge.',
                $lockInfo,
                403
            );
        }

        // Check other authorization requirements (active challenge, etc.)
        $this->authorize('create', [ChallengeAttempt::class, $challenge]);

        $userId = $request->user()->id;

        // Retake logic: If there's an active attempt, mark it as abandoned
        $activeAttempts = ChallengeAttempt::where('challenge_id', $challenge->id)
            ->where('user_id', $userId)
            ->whereNull('execution_output')
            ->get();

        if ($activeAttempts->isNotEmpty()) {
            // Mark all active attempts as abandoned
            foreach ($activeAttempts as $activeAttempt) {
                $activeAttempt->execution_output = json_encode([
                    'status' => 'abandoned',
                    'message' => 'Attempt was abandoned when user started a new attempt',
                ]);
                $activeAttempt->save();
            }
        }

        // Create new attempt
        $attempt = ChallengeAttempt::create([
            'challenge_id' => $challenge->id,
            'user_id' => $userId,
            'submitted_code' => $challenge->starter_code ?? '',
            'execution_output' => null,
            'passed' => false,
        ]);

        return $this->successResponse($attempt, 'Attempt started successfully', 201);
    }

    // PUT /challenge-attempts/{challengeAttemptId}/submit
    public function submitAttempt(SubmitChallengeAttemptRequest $request, int $challengeAttemptId)
    {
        $attempt = ChallengeAttempt::with('challenge.learningUnit')->findOrFail($challengeAttemptId);
        $challenge = $attempt->challenge;

        // Check XP lock before authorization
        $userXp = $this->getUserXp($challenge, $request->user()->id);
        $lockInfo = $this->getLockInfo($challenge, $userXp);

        if ($lockInfo['is_locked']) {
            return $this->errorResponse(
                'Challenge is locked. You need more XP to unlock this challenge.',
                $lockInfo,
                403
            );
        }

        // Check other authorization requirements (user owns attempt, not already submitted, etc.)
        $this->authorize('update', $attempt);

        // Double-check: prevent resubmit if already passed
        if ($attempt->passed) {
            return $this->errorResponse('This attempt has already been passed and cannot be resubmitted', null, 403);
        }

        $code = $request->input('code');
        $testCases = $challenge->test_cases ?? [];

        $allPassed = true;
        $details = [];
        $maxOutputLength = 10000; // Limit output size to prevent abuse

        // Option 2: stdout vs expected_output (no stdin usage)
        foreach ($testCases as $i => $case) {
            $expected = $case['expected_output'] ?? '';

            // Always use empty stdin (Option 2: ignore stdin completely)
            $res = $this->compiler->execute($code, $challenge->language, '');
            $output = $res['output'] ?? '';

            // Sanitize output: limit size
            if (strlen($output) > $maxOutputLength) {
                $output = substr($output, 0, $maxOutputLength) . '... (truncated)';
            }

            // Normalize both outputs before comparison (Option 2 normalization)
            $normalizedOutput = $this->normalizeOutput($output);
            $normalizedExpected = $this->normalizeOutput($expected);

            // Compare normalized outputs
            $ok = ($res['success'] ?? false) && ($normalizedOutput === $normalizedExpected);

            $details[] = [
                'case' => $i + 1,
                'passed' => $ok,
                'output' => $output, // Return actual output (not normalized) for display
                'expected_output' => $expected, // Return expected for UI feedback
                'error' => $res['error'] ?? null,
            ];

            if (!$ok) $allPassed = false;
        }

        // Sanitize execution_output JSON
        $executionOutput = json_encode($details, JSON_UNESCAPED_UNICODE);
        if (strlen($executionOutput) > 50000) {
            $executionOutput = json_encode([
                'error' => 'Output too large',
                'truncated' => true,
            ]);
        }

        DB::transaction(function () use ($attempt, $code, $executionOutput, $allPassed) {
            $attempt->submitted_code = $code;
            $attempt->execution_output = $executionOutput;
            $attempt->passed = $allPassed;
            $attempt->save();
        });

        return $this->successResponse([
            'passed' => $allPassed,
            'attempt' => $attempt->fresh(),
            'details' => $details,
        ], 'Attempt submitted successfully');
    }

    // GET /challenge-attempts/{challengeAttemptId}
    public function showAttempt(Request $request, int $challengeAttemptId)
    {
        $attempt = ChallengeAttempt::with('challenge')->findOrFail($challengeAttemptId);
        $this->authorize('view', $attempt);

        return $this->successResponse($attempt);
    }

    // GET /challenges/{challengeId}/my-attempts
    public function myAttempts(Request $request, int $challengeId)
    {
        $userId = $request->user()->id;

        $attempts = ChallengeAttempt::where('challenge_id', $challengeId)
            ->where('user_id', $userId)
            ->with('challenge:id,title,language,min_xp')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($attempts, 'Attempts retrieved successfully');
    }
}
