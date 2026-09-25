<?php

namespace App\Support;

use App\Enums\Platform;

/**
 * What the platforms do and don't share, for the copy on the home and about pages.
 * Everything here follows social.enabled_platforms, so a parked platform disappears
 * from the lists instead of promising numbers nobody can connect.
 */
class PlatformFacts
{
    /**
     * Metrics a verified profile can show, with the platforms that supply them.
     *
     * @return list<array{what: string, where: string, whereLong: string, icon: string}>
     */
    public static function metrics(): array
    {
        $yt = Platform::YouTube;
        $ig = Platform::Instagram;
        $x = Platform::X;

        $metrics = [
            ['Median and average views', [$yt, $ig, $x], '<path d="M3 15l4-5 3 3 4-6 3 4"/><path d="M3 17h14"/>'],
            ['How much of a video gets watched', [$yt], '<rect x="2.5" y="4.5" width="15" height="11" rx="2"/><path d="M8.5 8v4l3.5-2z"/>'],
            ['Average watch time', [$yt, $ig], '<circle cx="10" cy="10" r="7"/><path d="M10 6.5V10l2.5 1.5"/>'],
            ['How fast views come in', [$yt], '<path d="M3 12l5-6 4 4 5-6"/><path d="M13 4h4v4"/>'],
            ['Reach, and how many viewers are new', [$ig], '<circle cx="7" cy="8" r="2.5"/><circle cx="14" cy="8" r="2.5"/><path d="M2.5 16c0-2.5 2-4 4.5-4s4.5 1.5 4.5 4M11.5 12c2.5 0 4.5 1.5 4.5 4"/>'],
            ['Audience age, gender and country', [$yt, $ig], '<circle cx="10" cy="10" r="7"/><path d="M3 10h14M10 3c2.5 2.5 2.5 11.5 0 14M10 3c-2.5 2.5-2.5 11.5 0 14"/>'],
            ['Saves, shares and comments', [$yt, $ig, $x], '<path d="M5 3h10v14l-5-3-5 3z"/>'],
            ['Profile visits and link clicks', [$ig, $x], '<path d="M8.5 11.5l3-3M7 13l-1.5 1.5a2.5 2.5 0 0 1-3.5-3.5L4 9.5M13 7l1.5-1.5a2.5 2.5 0 0 1 3.5 3.5L16 10.5"/>'],
            ['How regularly they post', [$yt, $ig, $x], '<path d="M4 6.5h12M4 10h12M4 13.5h7"/>'],
        ];

        return collect($metrics)
            ->map(fn (array $m) => [
                'what' => $m[0],
                'platforms' => array_values(array_filter($m[1], fn (Platform $p) => $p->isEnabled())),
                'icon' => $m[2],
            ])
            ->reject(fn (array $m) => $m['platforms'] === [])
            ->map(fn (array $m) => [
                'what' => $m['what'],
                'where' => collect($m['platforms'])->map(fn (Platform $p) => $p->short())->join(' · '),
                'whereLong' => collect($m['platforms'])->map(fn (Platform $p) => $p->label())->join(' · '),
                'icon' => $m['icon'],
            ])
            ->values()
            ->all();
    }

    /** The numbers each platform keeps to itself, as one sentence fragment. */
    public static function notShared(): string
    {
        return collect([
            Platform::YouTube->value => 'impressions on YouTube',
            Platform::Instagram->value => 'second-by-second watch data on Instagram',
            Platform::X->value => 'audience details on X',
        ])
            ->filter(fn (string $phrase, string $platform) => Platform::from($platform)->isEnabled())
            ->join(', ', ' and ');
    }
}
