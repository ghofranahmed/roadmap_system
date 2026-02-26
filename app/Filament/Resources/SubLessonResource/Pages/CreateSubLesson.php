<?php

namespace App\Filament\Resources\SubLessonResource\Pages;

use App\Filament\Resources\SubLessonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubLesson extends CreateRecord
{
    protected static string $resource = SubLessonResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

