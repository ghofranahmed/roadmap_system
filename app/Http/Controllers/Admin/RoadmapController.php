<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Roadmap;
use App\Http\Requests\RoadmapRequest;
use App\Http\Resources\RoadmapResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class RoadmapController extends Controller
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
    
    /**
     * عرض جميع المسارات للمسؤول (مع البيانات الكاملة)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Roadmap::class);

        try {
            $query = Roadmap::query();
            
            // الترشيح المتقدم للمسؤول
            if ($request->has('level')) {
                $query->where('level', $request->level);
            }
            
            if ($request->has('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // ترتيب خاص للمسؤول
            $orderBy = $request->get('order_by', 'created_at');
            $orderDirection = $request->get('order_direction', 'desc');
            $query->orderBy($orderBy, $orderDirection);
            
            // الترقيم
            $perPage = $request->get('per_page', 20);
            $roadmaps = $query->withCount(['enrollments', 'learningUnits'])->paginate($perPage);
            
            return $this->paginatedResponse($roadmaps, 'تم جلب المسارات بنجاح');
                
        } catch (\Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء جلب المسارات',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * إنشاء مسار جديد
     */
    public function store(RoadmapRequest $request)
    {
        $this->authorize('create', Roadmap::class);

        try {
            $roadmap = Roadmap::create($request->validated());
            
            // إنشاء غرفة دردشة تلقائية
            if ($roadmap) {
                $roadmap->chatRoom()->create([
                    'name' => "غرفة دردشة - {$roadmap->title}",
                    'is_active' => true,
                ]);
            }
            
            // مسح الكاش
            Cache::flush();
            
            return $this->successResponse(
                new RoadmapResource($roadmap),
                'تم إنشاء المسار بنجاح',
                Response::HTTP_CREATED
            );
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء إنشاء المسار',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * عرض مسار معين (للمسؤول)
     */
    public function show($id)
    {
        try {
            $roadmap = Roadmap::withCount(['enrollments', 'learningUnits'])
                ->with(['chatRoom', 'learningUnits' => function ($q) {
                    $q->withCount('lessons')->orderBy('position');
                }])
                ->findOrFail($id);

            $this->authorize('view', $roadmap);
            
            return $this->successResponse(new RoadmapResource($roadmap), 'تم جلب المسار بنجاح');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('المسار غير موجود', null, Response::HTTP_NOT_FOUND);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المسار',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * تحديث مسار
     */
    public function update(RoadmapRequest $request, $id)
    {
        try {
            $roadmap = Roadmap::findOrFail($id);
            $this->authorize('update', $roadmap);
            $roadmap->update($request->validated());
            
            // مسح الكاش
            Cache::flush();
            
            return $this->successResponse(new RoadmapResource($roadmap), 'تم تحديث المسار بنجاح');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('المسار غير موجود', null, Response::HTTP_NOT_FOUND);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث المسار',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * حذف مسار
     */
    public function destroy($id)
    {
        try {
            $roadmap = Roadmap::findOrFail($id);
            $this->authorize('delete', $roadmap);
            
            // التحقق من عدم وجود اشتراكات نشطة
            if ($roadmap->enrollments()->where('status', 'active')->exists()) {
                return $this->errorResponse(
                    'لا يمكن حذف المسار لأنه يحتوي على اشتراكات نشطة',
                    null,
                    Response::HTTP_CONFLICT
                );
            }
            
            $roadmap->delete();
            
            // مسح الكاش
            Cache::flush();
            
            return $this->successResponse(null, 'تم حذف المسار بنجاح');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('المسار غير موجود', null, Response::HTTP_NOT_FOUND);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المسار',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * تفعيل/تعطيل مسار
     */
    public function toggleActive($id)
    {
        try {
            $roadmap = Roadmap::findOrFail($id);
            $this->authorize('toggleActive', $roadmap);
            
            $roadmap->update([
                'is_active' => !$roadmap->is_active,
            ]);
            
            // مسح الكاش
            Cache::flush();
            
            $status = $roadmap->is_active ? 'مفعل' : 'معطل';
            
            return $this->successResponse(
                new RoadmapResource($roadmap),
                "تم {$status} المسار بنجاح"
            );
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('المسار غير موجود', null, Response::HTTP_NOT_FOUND);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير حالة المسار',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * الحصول على إحصائيات مسار معين (للمسؤول)
     */
    public function getStats($id)
    {
        try {
            $roadmap = Roadmap::withCount([
                'enrollments',
                'enrollments as active_enrollments_count' => function ($query) {
                    $query->where('status', 'active');
                },
                'enrollments as completed_enrollments_count' => function ($query) {
                    $query->where('status', 'completed');
                },
                'learningUnits',
            ])->findOrFail($id);

            $this->authorize('view', $roadmap);
            
            return $this->successResponse([
                'roadmap_id' => $roadmap->id,
                'roadmap_title' => $roadmap->title,
                'total_enrollments' => $roadmap->enrollments_count,
                'active_enrollments' => $roadmap->active_enrollments_count,
                'completed_enrollments' => $roadmap->completed_enrollments_count,
                'learning_units_count' => $roadmap->learningUnits_count,
                'views_count' => $roadmap->views_count ?? 0,
                'created_at' => $roadmap->created_at,
            ], 'تم جلب الإحصائيات بنجاح');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('المسار غير موجود', null, Response::HTTP_NOT_FOUND);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}