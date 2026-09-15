<?php

namespace App\Social\Fake;

use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Small seeded RNG wrapper so generated data is reproducible.
 */
final class FakeRandom
{
    private Randomizer $randomizer;

    public function __construct(int $seed)
    {
        $this->randomizer = new Randomizer(new Mt19937($seed));
    }

    public function between(int $min, int $max): int
    {
        return $this->randomizer->getInt($min, $max);
    }

    public function float(float $min, float $max): float
    {
        return $min + ($max - $min) * ($this->randomizer->getInt(0, 1_000_000) / 1_000_000);
    }
}
