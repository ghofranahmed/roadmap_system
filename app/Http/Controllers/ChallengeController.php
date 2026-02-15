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

        $xp = $enrollment?->xp_points ?? 0;

        $challenge = Challenge::where('learning_unit_id', $unitId)
            ->where('is_active', true)
            ->first();

        if (!$challenge) {
            return $this->successResponse([
                'xp_points' => $xp,
                'challenge' => null,
            ]);
        }

        // Check unlock status via policy
        $challenge->is_unlocked = $request->user()->can('view', $challenge);

        // Hide test cases from client
        $challengeData = $challenge->toArray();
        unset($challengeData['test_cases']);

        return $this->successResponse([
            'xp_points' => $xp,
            'challenge' => $challengeData,
        ]);
    }


    // POST /challenges/{challengeId}/attempts
    public function startAttempt(Request $request, int $challengeId)
    {
        $challenge = Challenge::with('learningUnit')->findOrFail($challengeId);

        $this->authorize('create', [ChallengeAttempt::class, $challenge]);

        $attempt = ChallengeAttempt::create([
            'challenge_id' => $challenge->id,
            'user_id' => $request->user()->id,
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
        $this->authorize('update', $attempt);

        // Double-check: prevent resubmit if already passed
        if ($attempt->passed) {
            return $this->errorResponse('This attempt has already been passed and cannot be resubmitted', null, 403);
        }

        $challenge = $attempt->challenge;

        $code = $request->input('code');
        $testCases = $challenge->test_cases ?? [];

        $allPassed = true;
        $details = [];
        $maxOutputLength = 10000; // Limit output size to prevent abuse

        foreach ($testCases as $i => $case) {
            $stdin = $case['stdin'] ?? '';
            $expected = $case['expected_output'] ?? '';

            $res = $this->compiler->execute($code, $challenge->language, $stdin);
            $output = $res['output'] ?? '';

            // Sanitize output: limit size
            if (strlen($output) > $maxOutputLength) {
                $output = substr($output, 0, $maxOutputLength) . '... (truncated)';
            }

            $ok = ($res['success'] ?? false) && (trim($output) === trim($expected));

            $details[] = [
                'case' => $i + 1,
                'passed' => $ok,
                'output' => $output,
                'expected_output' => $expected,
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
