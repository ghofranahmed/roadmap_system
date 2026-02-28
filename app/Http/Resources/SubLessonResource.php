<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubLessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'position' => $this->position,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
        ];
        
        // Include resources if eager loaded or if requested
        if ($this->relationLoaded('resources') || $request->query('include') === 'resources') {
            $data['resources'] = $this->when(
                $this->relationLoaded('resources'),
                $this->resources->map(function ($resource) {
                    return [
                        'id' => $resource->id,
                        'title' => $resource->title,
                        'type' => $resource->type,
                        'language' => $resource->language,
                        'link' => $resource->link,
                        'created_at' => $resource->created_at?->toISOString(),
                    ];
                })
            );
        }
        
        return $data;
    }
}

