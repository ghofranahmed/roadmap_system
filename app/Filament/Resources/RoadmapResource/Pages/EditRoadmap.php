<?php

namespace App\Filament\Resources\RoadmapResource\Pages;

use App\Filament\Resources\RoadmapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoadmap extends EditRecord
{
    protected static string $resource = RoadmapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

