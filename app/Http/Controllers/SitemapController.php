<?php

namespace App\Http\Controllers;

use App\Enums\Platform;
use App\Models\Creator;
use App\Models\CreatorCategory;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $urls = [
                ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
                ['loc' => route('creators.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
                ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.5'],
                ['loc' => route('privacy'), 'changefreq' => 'yearly', 'priority' => '0.2'],
                ['loc' => route('terms'), 'changefreq' => 'yearly', 'priority' => '0.2'],
            ];

            foreach (Platform::enabled() as $platform) {
                $urls[] = ['loc' => route('creators.index', ['platform' => $platform->value]), 'changefreq' => 'daily', 'priority' => '0.7'];
            }

            foreach (CreatorCategory::orderBy('sort_order')->get() as $category) {
                $urls[] = ['loc' => route('creators.index', ['category' => $category->slug]), 'changefreq' => 'daily', 'priority' => '0.6'];
            }

            Creator::active()->select(['slug', 'updated_at', 'metrics_synced_at', 'has_verified_metrics'])->orderBy('id')->chunk(500, function ($creators) use (&$urls) {
                foreach ($creators as $creator) {
                    $urls[] = [
                        'loc' => route('creators.show', $creator),
                        'lastmod' => ($creator->metrics_synced_at ?? $creator->updated_at)->toAtomString(),
                        'changefreq' => $creator->has_verified_metrics ? 'daily' : 'weekly',
                        'priority' => $creator->has_verified_metrics ? '0.8' : '0.5',
                    ];
                }
            });

            return view('sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
