<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEnrolled
{public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    $roadmapId = null;

    // 1. إذا كان الرابط يحتوي على unitId (مثل دروس الوحدة)
    if ($request->route('unitId')) {
        $unit = \App\Models\LearningUnit::find($request->route('unitId'));
        $roadmapId = $unit?->roadmap_id;
    } 
    // 2. إذا كان الرابط يحتوي على lessonId (مثل عرض درس أو تتبع)
    elseif ($request->route('lessonId')) {
        $lesson = \App\Models\Lesson::with('learningUnit')->find($request->route('lessonId'));
        $roadmapId = $lesson?->learningUnit?->roadmap_id;
    }
    // 3. إذا كان الرابط يحتوي على subLessonId
    elseif ($request->route('subLessonId')) {
        $subLesson = \App\Models\SubLesson::with('lesson.learningUnit')->find($request->route('subLessonId'));
        $roadmapId = $subLesson?->lesson?->learningUnit?->roadmap_id;
    }

    // التحقق النهائي
    if (!$roadmapId || !$user || !$user->hasEnrolled($roadmapId)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: You are not enrolled in the roadmap associated with this content.'
        ], 403);
    }

    return $next($request);
}
}