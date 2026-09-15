<?php

namespace App\Social\Fake;

use App\Enums\Platform;

/**
 * Curated demo accounts the fake driver knows about. Anything else gets a deterministic
 * profile derived from the handle, so any handle can be "claimed" locally.
 *
 * Performance profiles are expressed per content type:
 *  median_views   typical views for a piece of content
 *  spread         relative variance around the median (0.3 = most items within ±30%)
 *  viral_chance   probability that an item is an outlier
 *  engagement     (likes + comments + shares + saves) / views
 *  view_pct       YouTube: average percentage viewed
 *  avg_watch      YouTube/Reels: average watch time in seconds
 *  reach_ratio    Instagram: reach / views
 *  count          how many items exist
 */
class FakeAccounts
{
    public static function find(Platform $platform, string $handle): ?array
    {
        $account = self::all()[$platform->value.':'.strtolower($handle)] ?? null;

        return $account ? $account + ['platform' => $platform, 'handle' => strtolower($handle)] : null;
    }

    /** @return array<string, array> */
    public static function all(): array
    {
        return [
            // --- YouTube --------------------------------------------------------------
            'youtube:johnsmith' => [
                'display_name' => 'John Smith',
                'followers' => 124_000,
                'bio' => 'Personal finance without the hype. Index funds, tax, and the occasional spreadsheet.',
                'audience' => [
                    'age' => ['18-24' => 14, '25-34' => 41, '35-44' => 27, '45-54' => 12, '55-64' => 4, '65+' => 2],
                    'gender' => ['male' => 71, 'female' => 29],
                    'countries' => ['GB' => 38, 'US' => 24, 'CA' => 7, 'AU' => 6, 'DE' => 4, 'IE' => 3, 'NL' => 2],
                    'devices' => ['mobile' => 58, 'desktop' => 31, 'tv' => 8, 'tablet' => 3],
                ],
                'content' => [
                    'video' => ['count' => 26, 'median_views' => 73_000, 'spread' => 0.28, 'viral_chance' => 0.04, 'engagement' => 0.046, 'view_pct' => 46, 'avg_watch' => 374],
                    'short' => ['count' => 8, 'median_views' => 28_000, 'spread' => 0.5, 'viral_chance' => 0.1, 'engagement' => 0.052, 'view_pct' => 88, 'avg_watch' => 31],
                ],
            ],
            'youtube:priyaexplains' => [
                'display_name' => 'Priya Natarajan',
                'followers' => 68_000,
                'bio' => 'Maths and physics explained properly. New video every Tuesday.',
                'content' => [
                    'video' => ['count' => 24, 'median_views' => 104_000, 'spread' => 0.22, 'viral_chance' => 0.08, 'engagement' => 0.058, 'view_pct' => 48, 'avg_watch' => 512],
                ],
            ],
            'youtube:bigtechdaily' => [
                'display_name' => 'Big Tech Daily',
                'followers' => 1_200_000,
                'bio' => 'Daily tech news. Fast, loud, occasionally right.',
                'content' => [
                    'video' => ['count' => 30, 'median_views' => 38_000, 'spread' => 0.35, 'viral_chance' => 0.03, 'engagement' => 0.014, 'view_pct' => 24, 'avg_watch' => 141],
                    'short' => ['count' => 20, 'median_views' => 12_000, 'spread' => 0.6, 'viral_chance' => 0.05, 'engagement' => 0.02, 'view_pct' => 71, 'avg_watch' => 22],
                ],
            ],
            'youtube:tomplays' => [
                'display_name' => 'Tom Nakamura',
                'followers' => 310_000,
                'bio' => 'Indie games, speedruns and long-form reviews.',
                'content' => [
                    'video' => ['count' => 30, 'median_views' => 95_000, 'spread' => 0.3, 'viral_chance' => 0.06, 'engagement' => 0.041, 'view_pct' => 41, 'avg_watch' => 688],
                ],
            ],
            'youtube:emilyzhangdev' => [
                'display_name' => 'Emily Zhang',
                'followers' => 210_000,
                'bio' => 'Software engineering, system design and career advice for developers.',
                'content' => [
                    'video' => ['count' => 28, 'median_views' => 66_000, 'spread' => 0.3, 'viral_chance' => 0.07, 'engagement' => 0.039, 'view_pct' => 43, 'avg_watch' => 455],
                ],
            ],
            'youtube:jamalfit' => [
                'display_name' => 'Jamal Carter',
                'followers' => 156_000,
                'bio' => 'Strength training for normal people. No 2-hour workouts.',
                'content' => [
                    'video' => ['count' => 25, 'median_views' => 48_000, 'spread' => 0.25, 'viral_chance' => 0.04, 'engagement' => 0.044, 'view_pct' => 44, 'avg_watch' => 296],
                    'short' => ['count' => 30, 'median_views' => 62_000, 'spread' => 0.55, 'viral_chance' => 0.1, 'engagement' => 0.05, 'view_pct' => 84, 'avg_watch' => 27],
                ],
            ],
            'youtube:isabellakitchen' => [
                'display_name' => "Isabella's Kitchen",
                'followers' => 720_000,
                'bio' => 'Italian home cooking. Real recipes from my nonna.',
                'content' => [
                    'video' => ['count' => 30, 'median_views' => 31_000, 'spread' => 0.4, 'viral_chance' => 0.02, 'engagement' => 0.021, 'view_pct' => 29, 'avg_watch' => 168],
                ],
            ],
            'youtube:gracelearns' => [
                'display_name' => 'Grace Liu',
                'followers' => 42_000,
                'bio' => 'Study techniques, note-taking and learning science for students.',
                'content' => [
                    'video' => ['count' => 22, 'median_views' => 61_000, 'spread' => 0.2, 'viral_chance' => 0.09, 'engagement' => 0.062, 'view_pct' => 52, 'avg_watch' => 401],
                ],
            ],
            'youtube:lucasmoreau' => [
                'display_name' => 'Lucas Moreau',
                'followers' => 98_000,
                'bio' => 'Bootstrapping software businesses. Revenue, hiring and mistakes.',
                'content' => [
                    'video' => ['count' => 20, 'median_views' => 34_000, 'spread' => 0.3, 'viral_chance' => 0.05, 'engagement' => 0.037, 'view_pct' => 39, 'avg_watch' => 362],
                ],
            ],
            'youtube:miaandersson' => [
                'display_name' => 'Mia Andersson',
                'followers' => 27_000,
                'bio' => 'Film photography, darkroom work and slow travel.',
                'content' => [
                    'video' => ['count' => 18, 'median_views' => 44_000, 'spread' => 0.24, 'viral_chance' => 0.06, 'engagement' => 0.071, 'view_pct' => 55, 'avg_watch' => 448],
                ],
            ],
            'youtube:amaratech' => ['display_name' => 'Amara Okafor', 'followers' => 480_000, 'bio' => 'Consumer tech reviews.'],
            'youtube:hannahtravels' => ['display_name' => 'Hannah Weiss', 'followers' => 89_000, 'bio' => 'Slow travel across Europe by train.'],
            'youtube:olivergrant' => ['display_name' => 'Oliver Grant', 'followers' => 3_400_000, 'bio' => 'Sketches and stand-up.'],
            'youtube:noahkimmoney' => ['display_name' => 'Noah Kim', 'followers' => 850_000, 'bio' => 'Investing for beginners.'],
            'youtube:leomartins' => ['display_name' => 'Leo Martins', 'followers' => 1_900_000, 'bio' => 'FPS content and tournaments.'],

            // --- Instagram ------------------------------------------------------------
            'instagram:lena.fit' => [
                'display_name' => 'Lena Fischer',
                'followers' => 182_000,
                'bio' => 'Mobility, strength and the 20-minute workouts you will actually do.',
                'audience' => [
                    'age' => ['18-24' => 22, '25-34' => 46, '35-44' => 21, '45-54' => 8, '55-64' => 2, '65+' => 1],
                    'gender' => ['female' => 68, 'male' => 31, 'user_specified' => 1],
                    'countries' => ['DE' => 44, 'US' => 11, 'AT' => 9, 'CH' => 8, 'GB' => 7, 'NL' => 3],
                    'cities' => ['Berlin' => 12, 'Munich' => 6, 'Hamburg' => 5, 'Vienna' => 4, 'Zurich' => 3],
                    'non_follower' => 61,
                ],
                'content' => [
                    'reel' => ['count' => 30, 'median_views' => 212_000, 'spread' => 0.3, 'viral_chance' => 0.06, 'engagement' => 0.048, 'reach_ratio' => 0.77, 'avg_watch' => 8.4],
                    'post' => ['count' => 12, 'median_views' => 41_000, 'spread' => 0.3, 'viral_chance' => 0.03, 'engagement' => 0.052, 'reach_ratio' => 0.9],
                ],
            ],
            'instagram:sofiacooks' => [
                'display_name' => 'Sofia Marchetti',
                'followers' => 540_000,
                'bio' => '30-minute dinners. Cookbook out now.',
                'content' => [
                    'reel' => ['count' => 30, 'median_views' => 61_000, 'spread' => 0.4, 'viral_chance' => 0.03, 'engagement' => 0.019, 'reach_ratio' => 0.8, 'avg_watch' => 5.1],
                    'post' => ['count' => 15, 'median_views' => 22_000, 'spread' => 0.3, 'viral_chance' => 0.02, 'engagement' => 0.023, 'reach_ratio' => 0.9],
                ],
            ],
            'instagram:avathompson' => [
                'display_name' => 'Ava Thompson',
                'followers' => 410_000,
                'bio' => 'Skincare that works. Dermatology nurse.',
                'content' => [
                    'reel' => ['count' => 30, 'median_views' => 380_000, 'spread' => 0.32, 'viral_chance' => 0.08, 'engagement' => 0.055, 'reach_ratio' => 0.74, 'avg_watch' => 9.7],
                    'post' => ['count' => 10, 'median_views' => 98_000, 'spread' => 0.3, 'viral_chance' => 0.03, 'engagement' => 0.047, 'reach_ratio' => 0.9],
                ],
            ],
            'instagram:freyalarsen' => [
                'display_name' => 'Freya Larsen',
                'followers' => 95_000,
                'bio' => 'Running coach. Marathon plans for busy people.',
                'content' => [
                    'reel' => ['count' => 28, 'median_views' => 130_000, 'spread' => 0.28, 'viral_chance' => 0.07, 'engagement' => 0.061, 'reach_ratio' => 0.78, 'avg_watch' => 7.9],
                ],
            ],
            'instagram:yukieats' => [
                'display_name' => 'Yuki Tanaka',
                'followers' => 61_000,
                'bio' => 'Tokyo street food, one stall at a time.',
                'content' => [
                    'reel' => ['count' => 30, 'median_views' => 210_000, 'spread' => 0.45, 'viral_chance' => 0.12, 'engagement' => 0.067, 'reach_ratio' => 0.72, 'avg_watch' => 11.2],
                ],
            ],
            'instagram:jamalfit' => ['display_name' => 'Jamal Carter', 'followers' => 240_000, 'bio' => 'Strength training for normal people.'],
            'instagram:charlotte.dubois' => [
                'display_name' => 'Charlotte Dubois',
                'followers' => 54_000,
                'bio' => 'Freelance strategy for designers.',
                'content' => [
                    'reel' => ['count' => 20, 'median_views' => 19_000, 'spread' => 0.3, 'viral_chance' => 0.04, 'engagement' => 0.041, 'reach_ratio' => 0.82, 'avg_watch' => 6.3],
                ],
            ],
            'instagram:chloebeauty' => ['display_name' => 'Chloe Bennett', 'followers' => 2_100_000, 'bio' => 'Makeup artist. Tutorials daily.'],
            'instagram:rafaelcosta.photo' => ['display_name' => 'Rafael Costa', 'followers' => 76_000, 'bio' => 'Street photography from Lisbon.'],
            'instagram:ninapetrova' => ['display_name' => 'Nina Petrova', 'followers' => 320_000, 'bio' => 'Interiors, slow living, plants.'],
            'instagram:mateo.travels' => ['display_name' => 'Mateo Alvarez', 'followers' => 128_000, 'bio' => 'Backpacking South America.'],

            // --- X --------------------------------------------------------------------
            'x:marcusreid' => [
                'display_name' => 'Marcus Reid',
                'followers' => 91_000,
                'bio' => 'Building in public. Notes on pricing, sales and SaaS.',
                'content' => [
                    'post' => ['count' => 30, 'median_views' => 64_000, 'spread' => 0.35, 'viral_chance' => 0.07, 'engagement' => 0.038, 'profile_click_rate' => 0.013, 'url_click_rate' => 0.006],
                ],
            ],
            'x:danielparkfi' => [
                'display_name' => 'Daniel Park',
                'followers' => 45_000,
                'bio' => 'Charts, macro and the occasional hot take.',
                'content' => [
                    'post' => ['count' => 30, 'median_views' => 88_000, 'spread' => 0.3, 'viral_chance' => 0.1, 'engagement' => 0.052, 'profile_click_rate' => 0.011, 'url_click_rate' => 0.009],
                ],
            ],
            'x:emilyzhang' => [
                'display_name' => 'Emily Zhang',
                'followers' => 130_000,
                'bio' => 'Software engineer. I post about systems and careers.',
                'content' => [
                    'post' => ['count' => 30, 'median_views' => 41_000, 'spread' => 0.4, 'viral_chance' => 0.06, 'engagement' => 0.029, 'profile_click_rate' => 0.009, 'url_click_rate' => 0.004],
                ],
            ],
            'x:lucasmoreau' => [
                'display_name' => 'Lucas Moreau',
                'followers' => 210_000,
                'bio' => 'Bootstrapper. Weekly revenue updates.',
                'content' => [
                    'post' => ['count' => 30, 'median_views' => 52_000, 'spread' => 0.4, 'viral_chance' => 0.05, 'engagement' => 0.024, 'profile_click_rate' => 0.008, 'url_click_rate' => 0.005],
                ],
            ],
            'x:zarahussain' => [
                'display_name' => 'Zara Hussain',
                'followers' => 38_000,
                'bio' => 'Life, work and the space between.',
                'content' => [
                    'post' => ['count' => 30, 'median_views' => 12_000, 'spread' => 0.5, 'viral_chance' => 0.03, 'engagement' => 0.011, 'profile_click_rate' => 0.004, 'url_click_rate' => 0.002],
                ],
            ],
            'x:ethanbrooks' => [
                'display_name' => 'Ethan Brooks',
                'followers' => 540_000,
                'bio' => 'Tech commentary. Formerly at a large fruit company.',
                'content' => [
                    'post' => ['count' => 30, 'median_views' => 96_000, 'spread' => 0.45, 'viral_chance' => 0.04, 'engagement' => 0.014, 'profile_click_rate' => 0.005, 'url_click_rate' => 0.003],
                ],
            ],
            'x:kenjiplays' => ['display_name' => 'Kenji Watanabe', 'followers' => 62_000, 'bio' => 'Fighting games and hardware.'],
            'x:samadeyemi' => ['display_name' => 'Samuel Adeyemi', 'followers' => 780_000, 'bio' => 'Jokes. Some of them good.'],
        ];
    }
}
