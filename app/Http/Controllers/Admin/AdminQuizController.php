<?php
namespace App\Http\Controllers\Admin; // تأكد من وجود \Admin

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class AdminQuizController extends Controller
{
    public function index() { return Quiz::all(); }

    public function store(Request $request) {
        return Quiz::create($request->validate([
            'learning_unit_id' => 'required|exists:learning_units,id',
            'is_active' => 'boolean',
            'max_xp' => 'integer',
            'min_xp' => 'integer',
        ]));
    }

    public function show($id) { return Quiz::with('questions')->findOrFail($id); }

    public function update(Request $request, $id) {
        $quiz = Quiz::findOrFail($id);
        $quiz->update($request->all());
        return $quiz;
    }

    public function destroy($id) {
        Quiz::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}