<?php

namespace App\Filament\Resources\SubLessonResource\Pages;

use App\Filament\Resources\SubLessonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubLesson extends EditRecord
{
    protected static string $resource = SubLessonResource::class;

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

