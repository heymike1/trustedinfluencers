<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Facade;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function test_cron_runs_the_sync_the_cleanup_and_the_queue_worker(): void
    {
        $this->app->forgetInstance(Schedule::class);
        Facade::clearResolvedInstance(Schedule::class);
        require base_path('routes/console.php');

        $commands = collect(app(Schedule::class)->events())->map(fn ($event) => $event->command)->join("\n");

        $this->assertStringContainsString('social:sync-due', $commands);
        $this->assertStringContainsString('queue:prune-failed', $commands);
        $this->assertStringContainsString('queue:work --stop-when-empty', $commands);
    }
}
