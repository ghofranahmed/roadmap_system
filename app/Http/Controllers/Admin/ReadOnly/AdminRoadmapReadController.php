<?php

namespace App\Http\Controllers\Admin\ReadOnly;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoadmapResource;
use App\Models\Roadmap;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminRoadmapReadController extends Controller
{
    /**
     * List all roadmaps for admin panel (read-only).
     */
    public function index(Request $request)
    {
        try {
            $query = Roadmap::query();

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

            $orderBy = $request->get('order_by', 'created_at');
            $orderDirection = $request->get('order_direction', 'desc');
            $query->orderBy($orderBy, $orderDirection);

            $perPage = $request->get('per_page', 20);
            $roadmaps = $query->withCount(['enrollments', 'learningUnits'])->paginate($perPage);

            return $this->paginatedResponse($roadmaps, 'Roadmaps retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve roadmaps',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Show a single roadmap for admin panel (read-only).
     */
    public function show($id)
    {
        try {
            $roadmap = Roadmap::withCount(['enrollments', 'learningUnits'])
                ->with(['chatRoom', 'learningUnits' => function ($q) {
                    $q->withCount('lessons')->orderBy('position');
                }])
                ->findOrFail($id);

            return $this->successResponse(new RoadmapResource($roadmap), 'Roadmap retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Roadmap not found', null, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve roadmap',
                config('app.debug') ? ['error' => $e->getMessage()] : null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

