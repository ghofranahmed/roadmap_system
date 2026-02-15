<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class AdminQuizQuestionController extends Controller
{
    public function index($quizId)
    {
        return response()->json([
            'data' => QuizQuestion::where('quiz_id', $quizId)->orderBy('order')->get()
        ]);
    }

    public function store(Request $request, $quizId)
    {
        $data = $request->validate([
            'question_text' => ['required', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'correct_answer' => ['required', 'string'],
            'question_xp' => ['sometimes', 'integer', 'min:0'],
            'order' => ['sometimes', 'integer', 'min:1'],
        ]);

        $data['quiz_id'] = $quizId;

        return response()->json(['data' => QuizQuestion::create($data)], 201);
    }

    public function update(Request $request, $questionId)
    {
        $question = QuizQuestion::findOrFail($questionId);

        $data = $request->validate([
            'question_text' => ['sometimes', 'string'],
            'options' => ['sometimes', 'array', 'min:2'],
            'correct_answer' => ['sometimes', 'string'],
            'question_xp' => ['sometimes', 'integer', 'min:0'],
            'order' => ['sometimes', 'integer', 'min:1'],
        ]);

        $question->update($data);

        return response()->json(['data' => $question]);
    }

    public function destroy($questionId)
    {
        QuizQuestion::findOrFail($questionId)->delete();
        return response()->json(['message' => 'Question Deleted']);
    }
}
