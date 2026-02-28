<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderUnitsRequest;
use App\Http\Requests\StoreLearningUnitRequest;
use App\Models\LearningUnit;
use App\Models\Roadmap;
use Illuminate\Support\Facades\DB;


class LearningUnitController extends Controller
{
    // ==========================
    // User Methods (Read Only)
    // ==========================

    public function index($roadmapId)
    {
        // جلب الوحدات مرتبة حسب الموقع
        $units = LearningUnit::where('roadmap_id', $roadmapId)
            ->withCount(['lessons', 'quizzes', 'challenges'])
            ->orderBy('position')
            ->get();

        return $this->successResponse($units);
    }

    public function show($roadmapId, $unitId)
    {
        // التأكد من أن الوحدة تابعة للـ Roadmap المحددة
        $unit = LearningUnit::where('roadmap_id', $roadmapId)
            ->where('id', $unitId)
            ->firstOrFail();

        return $this->successResponse($unit);
    }

    // ==========================
    // Admin Methods (CRUD)
    // ==========================

    public function adminIndex($roadmapId)
    {
        $this->authorize('viewAny', \App\Models\LearningUnit::class);

        // قد يحتاج الأدمن لمعلومات أكثر تفصيلاً (مثل created_at)
        $units = LearningUnit::where('roadmap_id', $roadmapId)
            ->withCount(['lessons', 'quizzes', 'challenges'])
            ->orderBy('position')
            ->get();

        return $this->successResponse($units);
    }

    public function store(StoreLearningUnitRequest $request, $roadmapId)
    {
        $this->authorize('create', \App\Models\LearningUnit::class);

        // التأكد من وجود الـ Roadmap
        $roadmap = Roadmap::findOrFail($roadmapId);

        // حساب الـ Position تلقائياً إذا لم يرسل (آخر عنصر + 1)
        $position = $request->position ?? $roadmap->learningUnits()->max('position') + 1;

        $unit = $roadmap->learningUnits()->create([
            'title' => $request->title,
            'position' => $position,
        ]);

        return $this->successResponse($unit, 'Unit created successfully', 201);
    }

    public function update(StoreLearningUnitRequest $request, $unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);
        $this->authorize('update', $unit);

        $data = $request->validated();
        unset($data['position']); // position changes only via reorder endpoint

        $unit->update($data);
        $unit->refresh();

        return $this->successResponse($unit, 'تم تحديث الوحدة بنجاح');
    }

    public function destroy($unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);
        $this->authorize('delete', $unit);
        $unit->delete();

        return $this->successResponse(null, 'Unit deleted successfully');
    }
    // ==========================
    // New Admin Methods
    // ==========================

    /**
     * Reorder Learning Units via Drag & Drop Logic
     */
   

    public function reorder(ReorderUnitsRequest $request, $roadmapId)
    {
        $validated = $request->validated();
        $unitId = (int) $validated['unit_id'];
        $newPosition = (int) $validated['new_position'];

        // Verify unit exists AND belongs to this roadmap
        $unit = LearningUnit::where('id', $unitId)
            ->where('roadmap_id', $roadmapId)
            ->first();

        if (!$unit) {
            return $this->errorResponse(
                'الوحدة غير موجودة في هذا المسار',
                null,
                404
            );
        }

        $this->authorize('reorder', $unit);

        $result = DB::transaction(function () use ($unit, $roadmapId, $newPosition) {
            // Step 1: Normalize positions to contiguous 1..N (fixes gaps & duplicates)
            // Use a temporary offset to avoid unique constraint violations during normalization
            $units = LearningUnit::where('roadmap_id', $roadmapId)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            $maxOffset = $units->count() + 1000;
            foreach ($units as $index => $u) {
                LearningUnit::where('id', $u->id)->update(['position' => $maxOffset + $index]);
            }
            foreach ($units as $index => $u) {
                LearningUnit::where('id', $u->id)->update(['position' => $index + 1]);
            }

            // Step 2: Refresh the unit's position after normalization
            $unit->refresh();
            $oldPosition = $unit->position;

            // Step 3: Clamp newPosition to valid range 1..maxPosition
            $maxPosition = $units->count();
            $newPosition = max(1, min($newPosition, $maxPosition));

            // If position unchanged after clamping, skip
            if ($oldPosition === $newPosition) {
                return [
                    'old_position' => $oldPosition,
                    'new_position' => $newPosition,
                ];
            }

            // Step 4: Temporarily move the target unit out of the way
            LearningUnit::where('id', $unit->id)->update(['position' => $maxPosition + 1]);

            // Step 5: Shift surrounding units
            if ($newPosition > $oldPosition) {
                // تحريك لأسفل: الوحدات بين (old+1) و new تتحرك للأعلى
                LearningUnit::where('roadmap_id', $roadmapId)
                    ->whereBetween('position', [$oldPosition + 1, $newPosition])
                    ->decrement('position');
            } else {
                // تحريك لأعلى: الوحدات بين new و (old-1) تتحرك للأسفل
                LearningUnit::where('roadmap_id', $roadmapId)
                    ->whereBetween('position', [$newPosition, $oldPosition - 1])
                    ->increment('position');
            }

            // Step 6: Place the moved unit at the new position
            LearningUnit::where('id', $unit->id)->update(['position' => $newPosition]);

            return [
                'old_position' => $oldPosition,
                'new_position' => $newPosition,
            ];
        });

        // Return all units in their new order
        $units = LearningUnit::where('roadmap_id', $roadmapId)
            ->orderBy('position')
            ->get(['id', 'title', 'position', 'is_active', 'unit_type']);

        return $this->successResponse([
            'roadmap_id' => (int) $roadmapId,
            'moved_unit_id' => $unit->id,
            'old_position' => $result['old_position'],
            'new_position' => $result['new_position'],
            'updated_count' => $units->count(),
            'units' => $units,
        ], 'تم إعادة الترتيب بنجاح');
    }

    /**
     * Toggle Unit Active Status (Activate/Deactivate)
     */
    public function toggleActive($unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);
        $this->authorize('toggleActive', $unit);

        // قلب القيمة الحالية بشكل آمن
        $unit->is_active = !(bool) $unit->is_active;
        $unit->save();

        return $this->successResponse([
            'unit_id' => $unit->id,
            'is_active' => $unit->is_active,
            'title' => $unit->title,
        ], $unit->is_active ? 'تم تفعيل الوحدة بنجاح' : 'تم تعطيل الوحدة بنجاح');
    }
}