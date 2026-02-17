<?php

namespace App\Http\Controllers\Admin\ReadOnly;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\LearningUnit;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Resource;
use App\Models\SubLesson;
use Illuminate\Http\Response;

class AdminContentReadController extends Controller
{
    // ──────────────────────────────────────────────
    //  UNITS
    // ──────────────────────────────────────────────

    public function unitsIndex($roadmapId)
    {
        $units = LearningUnit::where('roadmap_id', $roadmapId)
            ->withCount(['lessons', 'quizzes', 'challenges'])
            ->orderBy('position')
            ->get();

        return $this->successResponse($units, 'Units retrieved successfully');
    }

    public function unitShow($unitId)
    {
        $unit = LearningUnit::withCount(['lessons', 'quizzes', 'challenges'])
            ->findOrFail($unitId);

        return $this->successResponse($unit, 'Unit retrieved successfully');
    }

    // ──────────────────────────────────────────────
    //  LESSONS
    // ──────────────────────────────────────────────

    public function lessonsIndex($unitId)
    {
        $lessons = Lesson::where('learning_unit_id', $unitId)
            ->withCount('subLessons')
            ->orderBy('position')
            ->get();

        return $this->successResponse($lessons, 'Lessons retrieved successfully');
    }

    public function lessonShow($lessonId)
    {
        $lesson = Lesson::withCount('subLessons')
            ->with(['subLessons' => fn($q) => $q->orderBy('position')])
            ->findOrFail($lessonId);

        return $this->successResponse($lesson, 'Lesson retrieved successfully');
    }

    // ──────────────────────────────────────────────
    //  SUB-LESSONS
    // ──────────────────────────────────────────────

    public function subLessonsIndex($lessonId)
    {
        $subLessons = SubLesson::where('lesson_id', $lessonId)
            ->with(['resources' => fn($q) => $q->select('id', 'title', 'type', 'language', 'link', 'sub_lesson_id', 'created_at')])
            ->orderBy('position')
            ->get();

        return $this->successResponse($subLessons, 'Sub-lessons retrieved successfully');
    }

    public function subLessonShow($subLessonId)
    {
        $subLesson = SubLesson::with(['resources' => fn($q) => $q->select('id', 'title', 'type', 'language', 'link', 'sub_lesson_id', 'created_at')])
            ->findOrFail($subLessonId);

        return $this->successResponse($subLesson, 'Sub-lesson retrieved successfully');
    }

    // ──────────────────────────────────────────────
    //  RESOURCES
    // ──────────────────────────────────────────────

    public function resourcesIndex($subLessonId)
    {
        $resources = Resource::where('sub_lesson_id', $subLessonId)->get();

        return $this->successResponse($resources, 'Resources retrieved successfully');
    }

    public function resourceShow($resourceId)
    {
        $resource = Resource::findOrFail($resourceId);

        return $this->successResponse($resource, 'Resource retrieved successfully');
    }

    // ──────────────────────────────────────────────
    //  QUIZZES
    // ──────────────────────────────────────────────

    public function quizzesIndex($unitId)
    {
        $quizzes = Quiz::where('learning_unit_id', $unitId)
            ->with('learningUnit:id,title,roadmap_id')
            ->withCount('questions')
            ->get();

        return $this->successResponse($quizzes, 'Quizzes retrieved successfully');
    }

    public function quizShow($quizId)
    {
        $quiz = Quiz::with(['questions', 'learningUnit:id,title,roadmap_id'])
            ->findOrFail($quizId);

        return $this->successResponse($quiz, 'Quiz retrieved successfully');
    }

    // ──────────────────────────────────────────────
    //  CHALLENGES
    // ──────────────────────────────────────────────

    public function challengesIndex($unitId)
    {
        $challenge = Challenge::where('learning_unit_id', $unitId)
            ->with('learningUnit:id,title,roadmap_id')
            ->first();

        return $this->successResponse($challenge, 'Challenge retrieved successfully');
    }

    public function challengeShow($challengeId)
    {
        $challenge = Challenge::with('learningUnit:id,title,roadmap_id')
            ->findOrFail($challengeId);

        return $this->successResponse($challenge, 'Challenge retrieved successfully');
    }
}

