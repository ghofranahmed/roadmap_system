<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;

class Dashboard extends BaseDashboard
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.dashboard';

    public function getHeading(): string
    {
        $user = auth()->user();
        
        if ($user?->isNormalAdmin()) {
            return 'Normal Admin Dashboard';
        } elseif ($user?->isTechAdmin()) {
            return 'Technical Admin Dashboard';
        }

        return 'Admin Dashboard';
    }

    public function getSubheading(): string
    {
        $user = auth()->user();
        
        if ($user?->isNormalAdmin()) {
            return 'Manage users, announcements, and chat moderation';
        } elseif ($user?->isTechAdmin()) {
            return 'Manage all content: roadmaps, units, lessons, quizzes, and challenges';
        }

        return 'Welcome to the admin panel';
    }
}

