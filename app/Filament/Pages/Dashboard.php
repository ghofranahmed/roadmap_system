<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

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

