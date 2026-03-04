<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoadmapResource extends JsonResource
{
      
    public function toArray(Request $request): array
    {
        // Get enrollment map from request attributes (set by controller)
        $enrollmentMap = $request->attributes->get('enrollment_map', []);
        $isEnrolled = isset($enrollmentMap[$this->id]);
        $enrollmentStatus = $isEnrolled ? $enrollmentMap[$this->id] : null;
        
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'level' => $this->level,
            'level_arabic' => $this->getArabicLevel(),
            'is_active' => (bool)$this->is_active,
            'is_enrolled' => $isEnrolled,
            'status' => $enrollmentStatus,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // العلاقات (سيتم تحميلها عند الطلب فقط)
           // 'enrollments_count' => $this->whenCounted('enrollments'),
            //'learning_units_count' => $this->whenCounted('learningUnits'),
            //'chat_room' => new ChatRoomResource($this->whenLoaded('chatRoom')),
            
            // روابط إضافية
            //'links' => [
              /*  'self' => route('roadmaps.show', $this->id),
                'enrollments' => route('roadmaps.enrollments', $this->id),
                'learning_units' => route('roadmaps.learningUnits', $this->id),
            ],*/
        ];
    }

    /**
     * إضافة بيانات إضافية للاستجابة
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0.0',
                'api_version' => 'v1',
            ],
        ];
    }
}
