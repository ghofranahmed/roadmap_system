<?php

namespace App\Filament\Resources\LearningUnitResource\Pages;

use App\Filament\Resources\LearningUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningUnit extends EditRecord
{
    protected static string $resource = LearningUnitResource::class;

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

