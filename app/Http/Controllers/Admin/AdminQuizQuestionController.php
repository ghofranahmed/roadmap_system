<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class AdminQuizQuestionController extends Controller
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

    public function index($quizId)
    {
        $this->authorize('viewAny', QuizQuestion::class);

        $questions = QuizQuestion::where('quiz_id', $quizId)
            ->orderBy('order')
            ->get();
        
        return $this->successResponse($questions);
    }

    public function store(\App\Http\Requests\StoreQuizQuestionRequest $request, $quizId)
    {
        $this->authorize('create', QuizQuestion::class);

        $data = $request->validated();
        $data['quiz_id'] = $quizId;

        $question = QuizQuestion::create($data);

        return $this->successResponse($question, 'Question created successfully', 201);
    }

    public function update(\App\Http\Requests\UpdateQuizQuestionRequest $request, $questionId)
    {
        $question = QuizQuestion::findOrFail($questionId);
        $this->authorize('update', $question);
        $question->update($request->validated());

        return $this->successResponse($question, 'Question updated successfully');
    }

    public function destroy($questionId)
    {
        $question = QuizQuestion::findOrFail($questionId);
        $this->authorize('delete', $question);
        $question->delete();
        return $this->successResponse(null, 'Question deleted successfully');
    }
}
