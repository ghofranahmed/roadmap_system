<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\LearningUnit;
use Illuminate\Http\Request;

class ChallengeWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Challenge::with('learningUnit.roadmap');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('learning_unit_id')) {
            $query->where('learning_unit_id', $request->integer('learning_unit_id'));
        }

        if ($request->filled('language')) {
            $query->where('language', $request->get('language'));
        }

        if ($request->filled('is_active')) {
            $isActive = filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        $challenges = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $learningUnits = LearningUnit::with('roadmap')->orderBy('title')->get();

        return view('admin.challenges.index', compact('challenges', 'learningUnits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $learningUnits = LearningUnit::with('roadmap')->orderBy('title')->get();
        return view('admin.challenges.create', compact('learningUnits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'learning_unit_id' => 'required|exists:learning_units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_xp' => 'required|integer|min:0',
            'language' => 'required|in:javascript,python,java,c,cpp',
            'starter_code' => 'nullable|string',
            'test_cases' => 'required|array|min:1',
            'test_cases.*.stdin' => 'nullable|string',
            'test_cases.*.expected_output' => 'required|string',
            'is_active' => 'boolean',
        ]);

        try {
            $challenge = Challenge::create($validated);

            return redirect()->route('admin.challenges.index')
                ->with('success', 'Challenge created successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create challenge: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Challenge $challenge)
    {
        $challenge->load('learningUnit.roadmap', 'attempts');
        return view('admin.challenges.show', compact('challenge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Challenge $challenge)
    {
        $learningUnits = LearningUnit::with('roadmap')->orderBy('title')->get();
        return view('admin.challenges.edit', compact('challenge', 'learningUnits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Challenge $challenge)
    {
        $validated = $request->validate([
            'learning_unit_id' => 'required|exists:learning_units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_xp' => 'required|integer|min:0',
            'language' => 'required|in:javascript,python,java,c,cpp',
            'starter_code' => 'nullable|string',
            'test_cases' => 'required|array|min:1',
            'test_cases.*.stdin' => 'nullable|string',
            'test_cases.*.expected_output' => 'required|string',
            'is_active' => 'boolean',
        ]);

        try {
            $challenge->update($validated);
            
            return redirect()->route('admin.challenges.index')
                ->with('success', 'Challenge updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update challenge: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Challenge $challenge)
    {
        try {
            $challenge->delete();
            
            return redirect()->route('admin.challenges.index')
                ->with('success', 'Challenge deleted successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete challenge: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle active status of the challenge.
     */
    public function toggleActive(Challenge $challenge)
    {
        try {
            $challenge->is_active = !$challenge->is_active;
            $challenge->save();
            
            return back()->with('success', 
                'Challenge ' . ($challenge->is_active ? 'activated' : 'deactivated') . ' successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to toggle challenge status: ' . $e->getMessage()]);
        }
    }
}

