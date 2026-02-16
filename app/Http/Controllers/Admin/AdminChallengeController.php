<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminChallengeRequest;
use App\Models\Challenge;
use App\Models\LearningUnit;

class AdminChallengeController extends Controller
{
    /**
     * Constructor - Defense in depth: ensure only tech_admin role
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->isTechAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Technical admin role required.',
                ], 403);
            }
            return $next($request);
        });
    }

    // ✅ (Unit has only ONE challenge)
    public function index(int $unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);

        if ($unit->unit_type !== 'challenge') {
            return $this->errorResponse('Unit type must be challenge', null, 422);
        }

        $challenge = Challenge::where('learning_unit_id', $unitId)->first();

        return $this->successResponse($challenge);
    }

    // ✅ updateOrCreate because unique(learning_unit_id)
    public function store(AdminChallengeRequest $request, int $unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);

        if ($unit->unit_type !== 'challenge') {
            return $this->errorResponse('Unit type must be challenge', null, 422);
        }

        $data = $request->validated();

        $challenge = Challenge::updateOrCreate(
            ['learning_unit_id' => $unitId],
            $data
        );

        return $this->successResponse($challenge, 'Challenge created successfully', 201);
    }

    public function show(int $challengeId)
    {
        $challenge = Challenge::with('learningUnit')->findOrFail($challengeId);
        return $this->successResponse($challenge);
    }

    public function update(AdminChallengeRequest $request, int $challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        $challenge->update($request->validated());

        return $this->successResponse($challenge);
    }

    public function destroy(int $challengeId)
    {
        Challenge::whereKey($challengeId)->delete();
        return $this->successResponse(null, 'Challenge deleted successfully');
    }

    public function toggleActive(int $challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        $challenge->is_active = !$challenge->is_active;
        $challenge->save();

        return $this->successResponse($challenge);
    }
}
