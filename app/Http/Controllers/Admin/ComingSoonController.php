<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComingSoonController extends Controller
{
    /**
     * Show "Coming Soon" page for unimplemented admin features
     */
    public function show(Request $request, $feature = null)
    {
        $user = $request->user();
        $routeName = $request->route()->getName();

        // Enforce role-based access for placeholder routes
        if ($this->isNormalAdminFeature($routeName)) {
            if (!$user || !$user->isNormalAdmin()) {
                abort(403, 'Unauthorized. Admin access required.');
            }
        } elseif ($this->isTechAdminFeature($routeName)) {
            if (!$user || !$user->isTechAdmin()) {
                abort(403, 'Unauthorized. Technical admin access required.');
            }
        } else {
            // Fallback: do not expose unknown features
            abort(403, 'Unauthorized.');
        }

        $featureName = $this->getFeatureName($routeName, $feature);
        
        return view('admin.coming-soon', [
            'feature' => $featureName,
            'backUrl' => route('admin.dashboard'),
        ]);
    }

    /**
     * Determine if the route belongs to a Normal Admin feature
     */
    private function isNormalAdminFeature(string $routeName): bool
    {
        return in_array($routeName, [
            'admin.users.index',
            'admin.users.show',
            'admin.chat-moderation.index',
        ]);
    }

    /**
     * Determine if the route belongs to a Technical Admin feature
     */
    private function isTechAdminFeature(string $routeName): bool
    {
        return in_array($routeName, [
            'admin.roadmaps.index',
            'admin.roadmaps.create',
            'admin.roadmaps.show',
            'admin.roadmaps.edit',
            'admin.learning-units.index',
            'admin.learning-units.create',
            'admin.lessons.index',
            'admin.sub-lessons.index',
            'admin.resources.index',
            'admin.quizzes.index',
            'admin.quizzes.create',
            'admin.quiz-questions.index',
            'admin.challenges.index',
        ]);
    }
    
    /**
     * Extract feature name from route name
     */
    private function getFeatureName($routeName, $fallback = null)
    {
        if ($fallback) {
            return ucfirst(str_replace('-', ' ', $fallback));
        }
        
        // Extract feature from route name like "admin.roadmaps.index"
        $parts = explode('.', $routeName);
        if (count($parts) >= 2) {
            $feature = $parts[1]; // e.g., "roadmaps", "users", "quizzes"
            return ucfirst(str_replace('-', ' ', $feature)) . ' Management';
        }
        
        return 'This Feature';
    }
}

