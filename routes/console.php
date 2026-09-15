<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('social:sync-due')->hourly()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->daily();

// No Supervisor on the host? Let cron start a short-lived worker every minute instead.
// It drains the queue (or stops after 55 s) and exits; the next minute starts a fresh one.
if (config('queue.worker_via_scheduler')) {
    Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=2 --timeout=300')
        ->everyMinute()
        ->withoutOverlapping()
        ->runInBackground();
}
