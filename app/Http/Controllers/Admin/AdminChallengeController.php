<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminChallengeRequest;
use App\Models\Challenge;
use App\Models\LearningUnit;

class AdminChallengeController extends Controller
{
    // ✅ (Unit has only ONE challenge)
    public function index(int $unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);

        if ($unit->unit_type !== 'challenge') {
            return response()->json(['success' => false, 'message' => 'Unit type must be challenge'], 422);
        }

        $challenge = Challenge::where('learning_unit_id', $unitId)->first();

        return response()->json(['success' => true, 'data' => $challenge]);
    }

    // ✅ updateOrCreate because unique(learning_unit_id)
    public function store(AdminChallengeRequest $request, int $unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);

        if ($unit->unit_type !== 'challenge') {
            return response()->json(['success' => false, 'message' => 'Unit type must be challenge'], 422);
        }

        $data = $request->validated();

        $challenge = Challenge::updateOrCreate(
            ['learning_unit_id' => $unitId],
            $data
        );

        return response()->json(['success' => true, 'challenge' => $challenge], 201);
    }

    public function show(int $challengeId)
    {
        $challenge = Challenge::with('learningUnit')->findOrFail($challengeId);
        return response()->json(['success' => true, 'challenge' => $challenge]);
    }

    public function update(AdminChallengeRequest $request, int $challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        $challenge->update($request->validated());

        return response()->json(['success' => true, 'challenge' => $challenge]);
    }

    public function destroy(int $challengeId)
    {
        Challenge::whereKey($challengeId)->delete();
        return response()->json(['success' => true]);
    }

    public function toggleActive(int $challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        $challenge->is_active = !$challenge->is_active;
        $challenge->save();

        return response()->json(['success' => true, 'challenge' => $challenge]);
    }
}
