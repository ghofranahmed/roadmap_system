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
| Reminders: Send motivational reminders daily at 10:00 AM.
|   php artisan reminders:send
|
| To activate the scheduler, add to your server crontab:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
| Or on Windows Task Scheduler, run every minute:
|   php artisan schedule:run
|
*/

Schedule::command('reminders:send')->dailyAt('10:00');
