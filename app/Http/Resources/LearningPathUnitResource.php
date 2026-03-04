<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class LearningPathUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entity = $this->getEntityData();
        $tracking = $this->getLessonTracking($request);
        $latestAttempt = $this->getLatestAttempt($request);
        $locked = $this->getUnitLocked($request);
        $completed = $this->getUnitCompleted($request);

        $response = [
            'id' => $this->id,
            'position' => $this->position,
            'unit_type' => $this->unit_type,
            'label' => $this->getLabel(),
            'entity' => $entity,
            'is_locked' => $locked,
            'is_completed' => $completed,
        ];

        if ($this->unit_type === 'lesson') {
            $response['tracking'] = $tracking;
        }

        if (in_array($this->unit_type, ['quiz', 'challenge'], true)) {
            $response['latest_attempt'] = $latestAttempt;
        }

        return $response;
    }
    
    /**
     * Get entity data based on unit_type
     */
    private function getEntityData(): ?array
    {
        if ($this->unit_type === 'lesson' && $this->relationLoaded('lesson') && $this->lesson) {
            return [
                'id' => $this->lesson->id,
                'type' => 'lesson',
                'title' => $this->lesson->title,
                'description' => $this->lesson->description,
                'position' => $this->lesson->position,
                'is_active' => (bool) $this->lesson->is_active,
            ];
        }
        
        if ($this->unit_type === 'quiz' && $this->relationLoaded('quiz') && $this->quiz) {
            return [
                'id' => $this->quiz->id,
                'type' => 'quiz',
                'title' => $this->quiz->title ?? $this->title, // Fallback to unit title if quiz title is null
                'min_xp' => (int) $this->quiz->min_xp,
                'max_xp' => (int) $this->quiz->max_xp,
                'is_active' => (bool) $this->quiz->is_active,
            ];
        }
        
        if ($this->unit_type === 'challenge' && $this->relationLoaded('challenge') && $this->challenge) {
            return [
                'id' => $this->challenge->id,
                'type' => 'challenge',
                'title' => $this->challenge->title,
                'description' => $this->challenge->description,
                'min_xp' => (int) $this->challenge->min_xp,
                'is_active' => (bool) $this->challenge->is_active,
            ];
        }
        
        return null;
    }
    
    /**
     * Get label for display
     */
    private function getLabel(): string
    {
        $entity = $this->getEntityData();
        return $entity['title'] ?? $this->title ?? 'Untitled';
    }

    private function getLessonTracking(Request $request): array
    {
        if ($this->unit_type !== 'lesson' || !$this->lesson) {
            return [
                'exists' => false,
                'is_complete' => false,
                'last_updated_at' => null,
            ];
        }

        $trackingMap = $request->attributes->get('lesson_tracking_by_lesson_id');
        $tracking = $trackingMap?->get($this->lesson->id);
        $lastUpdatedAt = $tracking?->last_updated_at
            ? Carbon::parse($tracking->last_updated_at)->toISOString()
            : null;

        return [
            'exists' => (bool) $tracking,
            'is_complete' => (bool) ($tracking?->is_complete ?? false),
            'last_updated_at' => $lastUpdatedAt,
        ];
    }

    private function getLatestAttempt(Request $request): ?array
    {
        if ($this->unit_type === 'quiz' && $this->quiz) {
            $attempt = $request->attributes
                ->get('latest_quiz_attempt_by_quiz_id')
                ?->get($this->quiz->id);

            if (!$attempt) {
                return null;
            }

            return [
                'id' => $attempt->id,
                'passed' => (bool) $attempt->passed,
                'score' => (int) $attempt->score,
                'created_at' => $attempt->created_at?->toISOString(),
                'updated_at' => $attempt->updated_at?->toISOString(),
            ];
        }

        if ($this->unit_type === 'challenge' && $this->challenge) {
            $attempt = $request->attributes
                ->get('latest_challenge_attempt_by_challenge_id')
                ?->get($this->challenge->id);

            if (!$attempt) {
                return null;
            }

            return [
                'id' => $attempt->id,
                'passed' => (bool) $attempt->passed,
                'created_at' => $attempt->created_at?->toISOString(),
                'updated_at' => $attempt->updated_at?->toISOString(),
            ];
        }

        return null;
    }

    private function getUnitLocked(Request $request): bool
    {
        $unitLockMap = $request->attributes->get('unit_lock_map', []);
        return (bool) ($unitLockMap[$this->id] ?? true);
    }

    private function getUnitCompleted(Request $request): bool
    {
        $unitCompletionMap = $request->attributes->get('unit_completion_map', []);
        return (bool) ($unitCompletionMap[$this->id] ?? false);
    }
}

