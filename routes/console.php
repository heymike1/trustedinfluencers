<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('social:sync-due')->hourly()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->daily();
