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
     * إنشاء كونستركتور لإضافة middleware
     */
    public function __construct()
    {
        // تطبيق middleware للمسؤولين فقط على جميع الدوال
        $this->middleware('admin');
    }
    
    /**
     * عرض جميع المسارات للمسؤول (مع البيانات الكاملة)
     */
    public function index(Request $request)
    {
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
            
            return RoadmapResource::collection($roadmaps)
                ->additional([
                    'success' => true,
                    'message' => 'تم جلب المسارات بنجاح',
                ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المسارات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * إنشاء مسار جديد
     */
    public function store(RoadmapRequest $request)
    {
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
            
            return response()->json([
                'success' => true,
                'data' => new RoadmapResource($roadmap),
                'message' => 'تم إنشاء المسار بنجاح',
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء المسار',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                    $q->withCount('lessons')->orderBy('order_index');
                }])
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
            $roadmap->update($request->validated());
            
            // مسح الكاش
            Cache::flush();
            
            return response()->json([
                'success' => true,
                'data' => new RoadmapResource($roadmap),
                'message' => 'تم تحديث المسار بنجاح',
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المسار غير موجود',
            ], Response::HTTP_NOT_FOUND);
            
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
            
            // التحقق من عدم وجود اشتراكات نشطة
            if ($roadmap->enrollments()->where('status', 'active')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف المسار لأنه يحتوي على اشتراكات نشطة',
                ], Response::HTTP_CONFLICT);
            }
            
            $roadmap->delete();
            
            // مسح الكاش
            Cache::flush();
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المسار بنجاح',
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المسار غير موجود',
            ], Response::HTTP_NOT_FOUND);
            
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
            
            $roadmap->update([
                'is_active' => !$roadmap->is_active,
            ]);
            
            // مسح الكاش
            Cache::flush();
            
            $status = $roadmap->is_active ? 'مفعل' : 'معطل';
            
            return response()->json([
                'success' => true,
                'data' => new RoadmapResource($roadmap),
                'message' => "تم {$status} المسار بنجاح",
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المسار غير موجود',
            ], Response::HTTP_NOT_FOUND);
            
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
            
            return response()->json([
                'success' => true,
                'data' => [
                    'roadmap_id' => $roadmap->id,
                    'roadmap_title' => $roadmap->title,
                    'total_enrollments' => $roadmap->enrollments_count,
                    'active_enrollments' => $roadmap->active_enrollments_count,
                    'completed_enrollments' => $roadmap->completed_enrollments_count,
                    'learning_units_count' => $roadmap->learningUnits_count,
                    'views_count' => $roadmap->views_count ?? 0,
                    'created_at' => $roadmap->created_at,
                ],
                'message' => 'تم جلب الإحصائيات بنجاح',
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المسار غير موجود',
            ], Response::HTTP_NOT_FOUND);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}