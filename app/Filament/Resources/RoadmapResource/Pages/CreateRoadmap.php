<?php

namespace App\Filament\Resources\RoadmapResource\Pages;

use App\Filament\Resources\RoadmapResource;
use App\Models\Roadmap;
use Filament\Resources\Pages\CreateRecord;

class CreateRoadmap extends CreateRecord
{
    protected static string $resource = RoadmapResource::class;

    protected function afterCreate(): void
    {
        // Create chat room for the roadmap
        $roadmap = $this->record;
        $roadmap->chatRoom()->create([
            'name' => "Chat Room - {$roadmap->title}",
            'is_active' => true,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

