<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
|
| Announcements: Publish scheduled announcements every minute.
|   php artisan announcements:publish-due
|
| Reminders: Send motivational reminders daily at 9:00 AM.
|   php artisan reminders:send
|
| To activate the scheduler, add to your server crontab:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
| Or on Windows Task Scheduler, run every minute:
|   php artisan schedule:run
|
*/

Schedule::command('announcements:publish-due')->everyMinute();
Schedule::command('reminders:send')->dailyAt('09:00');
