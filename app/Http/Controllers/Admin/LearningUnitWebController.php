<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningUnit;
use App\Models\Roadmap;
use Illuminate\Http\Request;

class LearningUnitWebController extends Controller
{
    public function __construct()
    {
        // Defense in depth: only technical admins should reach this controller
        $this->middleware(function ($request, $next) {
            $user = $request->user();

            if (! $user || ! $user->isTechAdmin()) {
                abort(403, 'Unauthorized. Technical admin access required.');
            }

            return $next($request);
        });
    }

    /**
     * Display a listing of learning units for technical admin.
     *
     * Optional filters:
     * - roadmap_id
     * - search (by title)
     * - is_active (1/0)
     */
    public function index(Request $request)
    {
        $query = LearningUnit::query()
            ->with('roadmap:id,title')
            ->orderBy('roadmap_id')
            ->orderBy('position');

        if ($request->filled('roadmap_id')) {
            $query->where('roadmap_id', $request->integer('roadmap_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('is_active')) {
            $isActive = filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        $units = $query->paginate($request->get('per_page', 20))->withQueryString();

        $roadmaps = Roadmap::orderBy('title')->get(['id', 'title']);

        return view('admin.learning-units.index', [
            'units' => $units,
            'roadmaps' => $roadmaps,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roadmaps = Roadmap::orderBy('title')->get(['id', 'title']);
        return view('admin.learning-units.create', compact('roadmaps'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'roadmap_id' => 'required|exists:roadmaps,id',
            'title' => 'required|string|max:255',
            'position' => 'nullable|integer|min:1',
            'unit_type' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $roadmap = Roadmap::findOrFail($validated['roadmap_id']);
            $maxPosition = (int) $roadmap->learningUnits()->max('position');
            $position = $validated['position'] ?? ($maxPosition + 1);

            $unit = $roadmap->learningUnits()->create([
                'title' => $validated['title'],
                'position' => $position,
                'unit_type' => $validated['unit_type'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return redirect()->route('admin.learning-units.index')
                ->with('success', 'Learning unit created successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create learning unit: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LearningUnit $learningUnit)
    {
        $learningUnit->load('roadmap', 'lessons', 'quizzes', 'challenges');
        return view('admin.learning-units.show', compact('learningUnit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LearningUnit $learningUnit)
    {
        $roadmaps = Roadmap::orderBy('title')->get(['id', 'title']);
        return view('admin.learning-units.edit', compact('learningUnit', 'roadmaps'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LearningUnit $learningUnit)
    {
        $validated = $request->validate([
            'roadmap_id' => 'required|exists:roadmaps,id',
            'title' => 'required|string|max:255',
            'unit_type' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $learningUnit->update($validated);
            
            return redirect()->route('admin.learning-units.index')
                ->with('success', 'Learning unit updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update learning unit: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningUnit $learningUnit)
    {
        try {
            $learningUnit->delete();
            
            return redirect()->route('admin.learning-units.index')
                ->with('success', 'Learning unit deleted successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete learning unit: ' . $e->getMessage()]);
        }
    }

    /**
     * Reorder learning units.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'exists:learning_units,id',
        ]);

        try {
            foreach ($validated['unit_ids'] as $index => $unitId) {
                LearningUnit::where('id', $unitId)->update(['position' => $index + 1]);
            }
            
            return back()->with('success', 'Learning units reordered successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to reorder learning units: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle active status of the learning unit.
     */
    public function toggleActive(LearningUnit $unit)
    {
        try {
            $unit->is_active = !$unit->is_active;
            $unit->save();
            
            return back()->with('success', 
                'Learning unit ' . ($unit->is_active ? 'activated' : 'deactivated') . ' successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to toggle learning unit status: ' . $e->getMessage()]);
        }
    }
}


