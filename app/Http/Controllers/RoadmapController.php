<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexRoadmapRequest;

use App\Http\Requests\SearchRoadmapRequest;
use App\Http\Requests\ShowRoadmapRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\Roadmap;
use App\Http\Resources\RoadmapResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoadmapController extends Controller
{
    /**
     * عرض جميع المسارات
     */
    public function index(IndexRoadmapRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // استخدام الكاش لتحسين الأداء (30 دقيقة)
            $cacheKey = 'roadmaps_' . md5(serialize($validated));
            
            $roadmaps = Cache::remember($cacheKey, 1800, function () use ($validated) {
                return $this->buildRoadmapQuery($validated)->paginate(
                    $validated['per_page'] ?? 10
                );
            });
            
            return RoadmapResource::collection($roadmaps)
                ->additional([
                    'success' => true,
                    'message' => 'تم جلب المسارات بنجاح',
                    'meta' => $this->buildPaginationMeta($roadmaps),
                ]);
                
        } catch (\Exception $e) {
            return $this->handleException($e, 'Roadmap index error', $request->all());
        }
    }
    
    /**
     * البحث الذكي في المسارات
     */
    public function search(SearchRoadmapRequest $request)
    {
        try {
            $validated = $request->validated();
            
            $roadmaps = Roadmap::where('is_active', true)
                ->when(!empty($validated['query']), function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('title', 'like', "%{$validated['query']}%")
                          ->orWhere('description', 'like', "%{$validated['query']}%");
                    });
                })
                ->when(!empty($validated['level']), function ($query) use ($validated) {
                    $query->where('level', $validated['level']);
                })
                ->limit($validated['limit'] ?? 10)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => RoadmapResource::collection($roadmaps),
                'message' => 'تم البحث بنجاح',
                'meta' => [
                    'total_results' => $roadmaps->count(),
                    'search_query' => $validated['query']
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Roadmap search error', $request->all());
        }
    }
    
    /**
     * عرض مسار معين
     */
    public function show($id, ShowRoadmapRequest $request)
    {
        try {
            // التحقق من صحة المعرف
            $this->validateRoadmapId($id);
            
            $validated = $request->validated();
            
            // بناء الاستعلام
            $roadmap = Roadmap::withCount(['enrollments', 'learningUnits'])
                ->when($validated['with_details'] ?? false, function ($query) {
                    $query->with(['learningUnits' => function ($q) {
                        $q->withCount('lessons')->orderBy('order_index');
                    }]);
                })
                ->when($validated['include_content'] ?? false, function ($query) {
                    $query->with(['chatRoom', 'learningUnits.lessons']);
                }, function ($query) {
                    $query->with(['chatRoom']);
                })
                ->findOrFail($id);
            
            
            
            return response()->json([
                'success' => true,
                'data' => new RoadmapResource($roadmap),
                'message' => 'تم جلب المسار بنجاح',
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المسار غير موجود',
            ], Response::HTTP_NOT_FOUND);
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Roadmap show error', [
                'id' => $id,
                'request' => $request->all()
            ]);
        }
    }
    
    /**
     * بناء استعلام المسارات
     */
    private function buildRoadmapQuery(array $params)
    {
        $query = Roadmap::query();
        
        // الترشيح حسب المستوى
        if (isset($params['level'])) {
            $query->where('level', $params['level']);
        }
        
        // الترشيح حسب الحالة
        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }
        
        // البحث في العنوان والوصف
        if (isset($params['search'])) {
            $query->where(function ($q) use ($params) {
                $q->where('title', 'like', "%{$params['search']}%")
                  ->orWhere('description', 'like', "%{$params['search']}%");
            });
        }
        
        // الترتيب
        $orderBy = $params['order_by'] ?? 'created_at';
        $orderDirection = $params['order_direction'] ?? 'desc';
        
        if ($orderBy === 'enrollments_count') {
            $query->withCount('enrollments')->orderBy('enrollments_count', $orderDirection);
        } else {
            $query->orderBy($orderBy, $orderDirection);
        }
        
        return $query;
    }
    
    /**
     * بناء بيانات الترقيم
     */
    private function buildPaginationMeta($paginator)
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
    
    /**
     * التحقق من صحة معرف المسار
     */
    private function validateRoadmapId($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:roadmaps,id',
        ], [
            'id.required' => 'معرف المسار مطلوب',
            'id.integer' => 'معرف المسار يجب أن يكون رقمًا',
            'id.min' => 'معرف المسار غير صالح',
            'id.exists' => 'المسار غير موجود',
        ]);
        
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }
    
    /**
     * معالجة الاستثناءات
     */
    private function handleException(\Exception $e, string $logMessage, array $context = [])
    {
        Log::error($logMessage . ': ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'context' => $context,
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء معالجة الطلب',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function enrollments($id)
{
    $roadmap = Roadmap::with('enrollments.user')->findOrFail($id);

    return response()->json([
        'success' => true,
        'data' => $roadmap->enrollments,
    ]);
}

}