<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use Illuminate\Http\Request;

class AdminQuizController extends Controller
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

    public function index()
    {
        $this->authorize('viewAny', Quiz::class);

        $quizzes = Quiz::with(['learningUnit:id,title,roadmap_id', 'questions'])
            ->withCount('questions')
            ->paginate(request()->get('per_page', 15));
        return $this->paginatedResponse($quizzes, 'Quizzes retrieved successfully');
    }

    public function store(StoreQuizRequest $request)
    {
        $this->authorize('create', Quiz::class);

        $quiz = Quiz::create($request->validated());
        return $this->successResponse($quiz, 'Quiz created successfully', 201);
    }

    public function show($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $this->authorize('view', $quiz);
        return $this->successResponse($quiz);
    }

    public function update(UpdateQuizRequest $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $this->authorize('update', $quiz);
        $quiz->update($request->validated());
        return $this->successResponse($quiz, 'Quiz updated successfully');
    }

    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $this->authorize('delete', $quiz);
        $quiz->delete();
        return $this->successResponse(null, 'Quiz deleted successfully');
    }
}