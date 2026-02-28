<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Roadmap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoadmapWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Roadmap::with('learningUnits');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->get('level'));
        }

        if ($request->filled('is_active')) {
            $isActive = filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        $roadmaps = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        return view('admin.roadmaps.index', compact('roadmaps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roadmaps.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'is_active' => 'boolean',
        ]);

        try {
            $roadmap = Roadmap::create($validated);
            
            // Create chat room automatically (matching API behavior)
            if ($roadmap) {
                $roadmap->chatRoom()->create([
                    'name' => "Chat Room - {$roadmap->title}",
                    'is_active' => true,
                ]);
            }
            
            Cache::flush();
            
            return redirect()->route('admin.roadmaps.index')
                ->with('success', 'Roadmap created successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create roadmap: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Roadmap $roadmap)
    {
        $roadmap->load('learningUnits', 'enrollments');
        return view('admin.roadmaps.show', compact('roadmap'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Roadmap $roadmap)
    {
        return view('admin.roadmaps.edit', compact('roadmap'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Roadmap $roadmap)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'is_active' => 'boolean',
        ]);

        try {
            $roadmap->update($validated);
            Cache::flush();
            
            return redirect()->route('admin.roadmaps.index')
                ->with('success', 'Roadmap updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update roadmap: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Roadmap $roadmap)
    {
        try {
            $roadmap->delete();
            Cache::flush();
            
            return redirect()->route('admin.roadmaps.index')
                ->with('success', 'Roadmap deleted successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete roadmap: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle active status of the roadmap.
     */
    public function toggleActive(Roadmap $roadmap)
    {
        try {
            $roadmap->is_active = !$roadmap->is_active;
            $roadmap->save();
            Cache::flush();
            
            return back()->with('success', 
                'Roadmap ' . ($roadmap->is_active ? 'activated' : 'deactivated') . ' successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to toggle roadmap status: ' . $e->getMessage()]);
        }
    }
}

