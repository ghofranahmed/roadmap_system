<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\SubLesson;
use Illuminate\Http\Request;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;

class ResourceController extends Controller
{
    // ==========================
    // User Methods (Read Only)
    // ==========================

    /**
     * عرض مصادر درس فرعي معين للمستخدم العادي
     * GET /sub-lessons/{subLessonId}/resources
     */
    public function index($subLessonId)
    {
        $resources = Resource::where('sub_lesson_id', $subLessonId)
            ->get(['id', 'title', 'type', 'language', 'link', 'created_at']);
        
        return $this->successResponse($resources);
    }

    /**
     * عرض مصدر معين للمستخدم العادي
     * GET /sub-lessons/{subLessonId}/resources/{resourceId}
     */
    public function show($subLessonId, $resourceId)
    {
        $resource = Resource::where('sub_lesson_id', $subLessonId)
            ->findOrFail($resourceId, ['id', 'title', 'type', 'language', 'link', 'created_at']);
            
        return response()->json(['data' => $resource]);
    }

    // ==========================
    // Admin Methods (Full CRUD)
    // ==========================

    /**
     * عرض مصادر درس فرعي معين للمسؤول
     * GET /admin/sub-lessons/{subLessonId}/resources
     */
    public function adminIndex($subLessonId)
    {
        $resources = Resource::where('sub_lesson_id', $subLessonId)->get();
        
        return $this->successResponse($resources);
    }

    /**
     * إنشاء مصدر جديد
     * POST /admin/sub-lessons/{subLessonId}/resources
     */
    public function store(StoreResourceRequest $request, $subLessonId)
    {
        $subLesson = SubLesson::findOrFail($subLessonId);
        
        $resource = $subLesson->resources()->create([
            'title' => $request->title,
            'type' => $request->type,
            'language' => $request->language,
            'link' => $request->link
        ]);
        
        return $this->successResponse($resource, 'تم إنشاء المصدر بنجاح', 201);
    }

    /**
     * تحديث مصدر
     * PUT /admin/resources/{resourceId}
     */
    public function update(UpdateResourceRequest $request, $resourceId)
    {
        $resource = Resource::findOrFail($resourceId);
        $resource->update($request->validated());
        
        return $this->successResponse($resource, 'تم تحديث المصدر بنجاح');
    }

    /**
     * حذف مصدر
     * DELETE /admin/resources/{resourceId}
     */
    public function destroy($resourceId)
    {
        $resource = Resource::findOrFail($resourceId);
        $resource->delete();
        
        return $this->successResponse(null, 'تم حذف المصدر بنجاح');
    }

    /**
     * البحث في المصادر (للمسؤولين فقط)
     * GET /admin/resources/search
     */
    public function search(Request $request)
    {
        $query = Resource::query();
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }
        
        if ($request->has('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }
        
        if ($request->has('sub_lesson_id')) {
            $query->where('sub_lesson_id', $request->sub_lesson_id);
        }
        
        $resources = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));
        
        return $this->paginatedResponse($resources, 'Resources retrieved successfully');
    }
}