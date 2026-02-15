<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class AdminQuizQuestionController extends Controller
{
    public function index($quizId)
    {
        $questions = QuizQuestion::where('quiz_id', $quizId)
            ->orderBy('order')
            ->get();
        
        return $this->successResponse($questions);
    }

    public function store(\App\Http\Requests\StoreQuizQuestionRequest $request, $quizId)
    {
        $data = $request->validated();
        $data['quiz_id'] = $quizId;

        $question = QuizQuestion::create($data);

        return $this->successResponse($question, 'Question created successfully', 201);
    }

    public function update(\App\Http\Requests\UpdateQuizQuestionRequest $request, $questionId)
    {
        $question = QuizQuestion::findOrFail($questionId);
        $question->update($request->validated());

        return $this->successResponse($question, 'Question updated successfully');
    }

    public function destroy($questionId)
    {
        QuizQuestion::findOrFail($questionId)->delete();
        return $this->successResponse(null, 'Question deleted successfully');
    }
}
