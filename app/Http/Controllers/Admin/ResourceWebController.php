<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\SubLesson;
use Illuminate\Http\Request;

class ResourceWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Resource::with('subLesson.lesson.learningUnit.roadmap');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('link', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sub_lesson_id')) {
            $query->where('sub_lesson_id', $request->integer('sub_lesson_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('language')) {
            $query->where('language', $request->get('language'));
        }

        $resources = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $subLessons = SubLesson::with('lesson.learningUnit.roadmap')->orderBy('id')->get();

        return view('admin.resources.index', compact('resources', 'subLessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subLessons = SubLesson::with('lesson.learningUnit.roadmap')->orderBy('id')->get();
        return view('admin.resources.create', compact('subLessons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_lesson_id' => 'required|exists:sub_lessons,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:book,video,article',
            'language' => 'required|in:ar,en',
            'link' => 'required|url',
        ]);

        try {
            $subLesson = SubLesson::findOrFail($validated['sub_lesson_id']);
            
            $resource = $subLesson->resources()->create([
                'title' => $validated['title'],
                'type' => $validated['type'],
                'language' => $validated['language'],
                'link' => $validated['link'],
            ]);

            return redirect()->route('admin.resources.index')
                ->with('success', 'Resource created successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create resource: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Resource $resource)
    {
        $resource->load('subLesson.lesson.learningUnit.roadmap');
        return view('admin.resources.show', compact('resource'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resource $resource)
    {
        $subLessons = SubLesson::with('lesson.learningUnit.roadmap')->orderBy('id')->get();
        return view('admin.resources.edit', compact('resource', 'subLessons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'sub_lesson_id' => 'required|exists:sub_lessons,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:book,video,article',
            'language' => 'required|in:ar,en',
            'link' => 'required|url',
        ]);

        try {
            $resource->update($validated);
            
            return redirect()->route('admin.resources.index')
                ->with('success', 'Resource updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update resource: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource)
    {
        try {
            $resource->delete();
            
            return redirect()->route('admin.resources.index')
                ->with('success', 'Resource deleted successfully.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete resource: ' . $e->getMessage()]);
        }
    }

    /**
     * Search resources.
     */
    public function search(Request $request)
    {
        $query = Resource::with('subLesson.lesson.learningUnit.roadmap');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('link', 'like', "%{$search}%");
            });
        }

        $resources = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        return view('admin.resources.index', compact('resources'));
    }
}

