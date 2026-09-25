<?php

namespace App\Console\Commands;

use App\Actions\Sponsors\RollBookings;
use Illuminate\Console\Command;

class RollSponsorBookings extends Command
{
    protected $signature = 'sponsors:roll';

    protected $description = 'Release expired checkouts, close finished runs and hand free spots to the queue';

    public function handle(RollBookings $roll): int
    {
        $result = $roll->handle();

        $this->info(sprintf(
            '%d hold(s) released, %d run(s) ended, %d booking(s) promoted, %d reminder(s) sent.',
            $result['released'], $result['ended'], $result['promoted'], $result['warned'],
        ));

        return self::SUCCESS;
    }
}
