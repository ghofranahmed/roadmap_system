<?php

namespace App\Filament\Resources\SubLessonResource\Pages;

use App\Filament\Resources\SubLessonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubLessons extends ListRecords
{
    protected static string $resource = SubLessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

