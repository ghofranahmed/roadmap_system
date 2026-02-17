<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Models\Announcement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishDueAnnouncements extends Command
{
    protected $signature = 'announcements:publish-due';

    protected $description = 'Publish scheduled announcements whose publish_at time has passed and notify target users';

    public function handle(): int
    {
        $dueAnnouncements = Announcement::dueForPublishing()->get();

        if ($dueAnnouncements->isEmpty()) {
            $this->info('No scheduled announcements due for publishing.');
            return self::SUCCESS;
        }

        $publishedCount = 0;

        foreach ($dueAnnouncements as $announcement) {
            try {
                DB::transaction(function () use ($announcement) {
                    $announcement->update(['status' => 'published']);

                    // Send notifications to target users
                    AdminAnnouncementController::sendNotificationsToTargetUsers($announcement);
                });

                $publishedCount++;
                $this->info("Published: [{$announcement->id}] {$announcement->title}");
            } catch (\Throwable $e) {
                Log::error("Failed to publish announcement #{$announcement->id}: {$e->getMessage()}");
                $this->error("Failed to publish announcement #{$announcement->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Published {$publishedCount} announcement(s).");

        return self::SUCCESS;
    }
}

