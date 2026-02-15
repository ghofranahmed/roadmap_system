<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitChallengeAttemptRequest;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\LearningUnit;
use App\Models\RoadmapEnrollment;
use App\Services\Compiler\CompilerServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChallengeController extends Controller
{
    public function __construct(private CompilerServiceInterface $compiler) {}

    // GET /units/{unitId}/challenges
   public function index(Request $request, int $unitId)
{
    $unit = LearningUnit::findOrFail($unitId);

    if ($unit->unit_type !== 'challenge') {
        return response()->json(['success' => false, 'message' => 'This unit is not a challenge unit'], 422);
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
        return response()->json(['success' => true, 'xp_points' => $xp, 'challenge' => null]);
    }

    // ✅ unlock by ChallengePolicy
    $challenge->is_unlocked = $request->user()->can('view', $challenge);

    return response()->json([
        'success' => true,
        'xp_points' => $xp,
        'challenge' => $challenge,
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

        return response()->json([
            'success' => true,
            'attempt' => $attempt
        ], 201);
    }

    // PUT /challenge-attempts/{challengeAttemptId}/submit
    public function submitAttempt(SubmitChallengeAttemptRequest $request, int $challengeAttemptId)
    {
        $attempt = ChallengeAttempt::with('challenge.learningUnit')->findOrFail($challengeAttemptId);
        $this->authorize('update', $attempt);

        $challenge = $attempt->challenge;

        // ✅ نخلي اسم الحقل زي ما عندك
        $code = $request->input('code');
        $testCases = $challenge->test_cases ?? [];

        $allPassed = true;
        $details = [];

        foreach ($testCases as $i => $case) {
            $stdin = $case['stdin'] ?? '';
            $expected = $case['expected_output'] ?? '';

            $res = $this->compiler->execute($code, $challenge->language, $stdin);
            $output = $res['output'] ?? '';

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

        DB::transaction(function () use ($attempt, $code, $details, $allPassed) {
            $attempt->submitted_code = $code;
            $attempt->execution_output = json_encode($details, JSON_UNESCAPED_UNICODE);
            $attempt->passed = $allPassed;
            $attempt->save();

            // ❌ لا XP هنا (حسب شرطك)
        });

        return response()->json([
            'success' => true,
            'passed' => $allPassed,
            'attempt' => $attempt->fresh(),
            'details' => $details,
        ]);
    }

    // GET /challenge-attempts/{challengeAttemptId}
    public function showAttempt(Request $request, int $challengeAttemptId)
    {
        $attempt = ChallengeAttempt::with('challenge')->findOrFail($challengeAttemptId);
        $this->authorize('view', $attempt);

        return response()->json([
            'success' => true,
            'attempt' => $attempt
        ]);
    }

    // GET /challenges/{challengeId}/my-attempts
    public function myAttempts(Request $request, int $challengeId)
    {
        $userId = $request->user()->id;

        $attempts = ChallengeAttempt::where('challenge_id', $challengeId)
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'attempts' => $attempts
        ]);
    }
}
