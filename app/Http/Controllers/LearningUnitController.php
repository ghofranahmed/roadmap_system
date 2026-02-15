<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LearningUnit;
use App\Models\Roadmap;
use App\Http\Requests\StoreLearningUnitRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ReorderUnitsRequest;


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
        // قد يحتاج الأدمن لمعلومات أكثر تفصيلاً (مثل created_at)
        $units = LearningUnit::where('roadmap_id', $roadmapId)
            ->withCount(['lessons', 'quizzes', 'challenges'])
            ->orderBy('position')
            ->get();

        return $this->successResponse($units);
    }

    public function store(StoreLearningUnitRequest $request, $roadmapId)
    {
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
        
        $unit->update($request->validated());

        return $this->successResponse($unit, 'Unit updated successfully');
    }

    public function destroy($unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);
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
        $orderedIds = $request->unit_ids;

        // 1. التحقق من أن جميع الوحدات تابعة لنفس الـ Roadmap الممررة في الرابط
        // نمنع هنا تداخل البيانات بين المسارات المختلفة
        $count = LearningUnit::whereIn('id', $orderedIds)
                    ->where('roadmap_id', $roadmapId)
                    ->count();

        if ($count !== count($orderedIds)) {
            return $this->errorResponse(
                'Validation Error',
                ['unit_ids' => ['One or more units do not belong to this roadmap or duplicates exist.']],
                422
            );
        }

        // 2. تنفيذ التحديث داخل Transaction لضمان سلامة البيانات
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                // الترتيب يبدأ من 1 بدلاً من 0 ليناسب البشر
                LearningUnit::where('id', $id)->update(['position' => $index + 1]);
            }
        });

        return $this->successResponse(null, 'Units reordered successfully');
    }

    /**
     * Toggle Unit Active Status (Activate/Deactivate)
     */
    public function toggleActive($unitId)
    {
        $unit = LearningUnit::findOrFail($unitId);

        // قلب القيمة الحالية
        $unit->update([
            'is_active' => ! $unit->is_active
        ]);

        return $this->successResponse([
            'unit_id' => $unit->id,
            'is_active' => $unit->is_active,
            'title' => $unit->title
        ], 'Unit status updated');
    }
}