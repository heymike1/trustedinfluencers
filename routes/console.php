<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('social:sync-due')->hourly()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->daily();

// The queue worker runs from cron too: every minute a short-lived worker drains the queue
// (imports, emails) and exits. Remove this line if you run a permanent worker under Supervisor.
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=2 --timeout=300')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
