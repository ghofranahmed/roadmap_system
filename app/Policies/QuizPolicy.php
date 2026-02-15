<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quiz;
use App\Models\LearningUnit;
use App\Models\RoadmapEnrollment;
use Illuminate\Support\Facades\DB;

class QuizPolicy
{
    public function view(User $user, Quiz $quiz): bool
    {
        if (!$quiz->is_active) return false;

        $unit = LearningUnit::find($quiz->learning_unit_id);
        if (!$unit || !$unit->is_active || $unit->unit_type !== 'quiz') return false;

        // لازم enrolled
        $enrollment = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $unit->roadmap_id)
            ->first();

        if (!$enrollment) return false;

        // كل الدروس السابقة (كـ LearningUnits type lesson) لازم تكون مكتملة
        $prevLessonIds = DB::table('learning_units as lu')
            ->join('lessons as l', 'l.learning_unit_id', '=', 'lu.id')
            ->where('lu.roadmap_id', $unit->roadmap_id)
            ->where('lu.is_active', 1)
            ->where('lu.unit_type', 'lesson')
            ->where('lu.position', '<', $unit->position)
            ->pluck('l.id');

        if ($prevLessonIds->isEmpty()) return true;

        $completedCount = DB::table('lesson_trackings')
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $prevLessonIds)
            ->where('is_complete', 1)
            ->count();

        return $completedCount === $prevLessonIds->count();
    }

    public function attempt(User $user, Quiz $quiz): bool
    {
        return $this->view($user, $quiz);
    }

    public function manage(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
