<?php

namespace App\Filament\Resources\LearningUnitResource\Pages;

use App\Filament\Resources\LearningUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningUnit extends CreateRecord
{
    protected static string $resource = LearningUnitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

