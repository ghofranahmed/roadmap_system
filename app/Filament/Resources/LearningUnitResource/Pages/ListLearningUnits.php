<?php

namespace App\Filament\Resources\LearningUnitResource\Pages;

use App\Filament\Resources\LearningUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningUnits extends ListRecords
{
    protected static string $resource = LearningUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

