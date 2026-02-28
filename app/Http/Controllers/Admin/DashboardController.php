<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     * Accessible to both Normal Admin and Technical Admin.
     */
    public function index()
    {
        $user = auth()->user();

        // Both admin types can access dashboard
        if (!$user || !$user->isAnyAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        // Prepare dashboard data based on role
        $stats = $this->getDashboardStats($user);

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Get dashboard statistics based on user role.
     */
    private function getDashboardStats($user)
    {
        $stats = [];

        if ($user->isNormalAdmin()) {
            // Normal Admin stats
            $stats = [
                'total_users' => \App\Models\User::where('role', 'user')->count(),
                'total_admins' => \App\Models\User::whereIn('role', ['admin', 'tech_admin'])->count(),
                'active_announcements' => \App\Models\Announcement::where(function ($query) {
                    $query->whereNull('ends_at')
                          ->orWhere('ends_at', '>=', now());
                })->count(),
                'total_announcements' => \App\Models\Announcement::count(),
                'total_notifications' => \App\Models\Notification::count(),
                'unread_notifications' => \App\Models\Notification::whereNull('read_at')->count(),
            ];
        } elseif ($user->isTechAdmin()) {
            // Technical Admin stats
            $stats = [
                'total_roadmaps' => \App\Models\Roadmap::count(),
                'active_roadmaps' => \App\Models\Roadmap::where('is_active', true)->count(),
                'total_learning_units' => \App\Models\LearningUnit::count(),
                'total_lessons' => \App\Models\Lesson::count(),
                'total_quizzes' => \App\Models\Quiz::count(),
                'total_challenges' => \App\Models\Challenge::count(),
            ];
        }

        return $stats;
    }
}

