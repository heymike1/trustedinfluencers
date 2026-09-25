<?php

namespace Database\Factories;

use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SponsorSlot>
 */
class SponsorSlotFactory extends Factory
{
    protected $model = SponsorSlot::class;

    public function definition(): array
    {
        // A card that is up, in the first spot nobody has taken.
        $spot = Sponsorship::parseSpot(Sponsorship::openSpots()->first()) ?? ['left', 1];

        return [
            'name' => $this->faker->unique()->company(),
            'tagline' => $this->faker->catchPhrase(),
            'url' => 'https://'.$this->faker->domainName(),
            'tint' => 'blue',
            'side' => $spot[0],
            'position' => $spot[1],
            'status' => SponsorSlot::LIVE,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(Sponsorship::days()),
            'buyer_email' => $this->faker->safeEmail(),
            'paid_at' => now()->subDay(),
            'amount' => Sponsorship::amount(),
            'currency' => Sponsorship::currency(),
        ];
    }

    /**
     * A batch is built before any of it is saved, so several rows can pick the same spot. Once a
     * row exists we know what is really taken, and anything doubled up moves over.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (SponsorSlot $slot) {
            if ($slot->position === null) {
                return;
            }

            $taken = SponsorSlot::holdingASpot()
                ->where('id', '!=', $slot->id)
                ->where('side', $slot->side)
                ->where('position', $slot->position)
                ->exists();

            if ($taken) {
                $spot = Sponsorship::parseSpot(Sponsorship::openSpots()->first());
                $slot->forceFill(['side' => $spot[0] ?? $slot->side, 'position' => $spot[1] ?? null])->save();
            }
        });
    }

    /** Paid while everything was full: no spot yet. */
    public function queued(): static
    {
        return $this->state(fn () => [
            'status' => SponsorSlot::PAID,
            'position' => null,
            'queued_at' => now(),
            'starts_at' => null,
            'ends_at' => null,
            'name' => null,
            'tagline' => null,
            'url' => null,
        ]);
    }

    /** A checkout that is still open and holding its spot. */
    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => SponsorSlot::PENDING,
            'reserved_until' => now()->addMinutes(Sponsorship::holdMinutes()),
            'paid_at' => null,
            'starts_at' => null,
            'ends_at' => null,
            'name' => null,
            'tagline' => null,
            'url' => null,
        ]);
    }
}
