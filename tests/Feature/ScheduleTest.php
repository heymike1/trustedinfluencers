<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Facade;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function test_the_scheduled_worker_is_only_registered_when_enabled(): void
    {
        $this->assertStringNotContainsString('queue:work', $this->scheduledCommands(false));

        $with = $this->scheduledCommands(true);
        $this->assertStringContainsString('queue:work --stop-when-empty', $with);
        $this->assertStringContainsString('social:sync-due', $with);
    }

    private function scheduledCommands(bool $workerViaScheduler): string
    {
        config(['queue.worker_via_scheduler' => $workerViaScheduler]);
        $this->app->forgetInstance(Schedule::class);
        Facade::clearResolvedInstance(Schedule::class);
        require base_path('routes/console.php');

        return collect(app(Schedule::class)->events())->map(fn ($event) => $event->command)->join("\n");
    }
}
