<?php

namespace App\Support;

use App\Models\Creator;
use App\Models\CreatorSocialAccount;

/**
 * Structured data (JSON-LD) for the public pages.
 */
class Seo
{
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/').'#organization',
                    'name' => config('app.name'),
                    'url' => url('/'),
                    'logo' => url('/logo.png'),
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/').'#website',
                    'name' => config('app.name'),
                    'description' => config('app.tagline'),
                    'url' => url('/'),
                    'publisher' => ['@id' => url('/').'#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('creators.index').'?q={search_term_string}'],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    }

    public static function creator(Creator $creator): array
    {
        $url = route('creators.show', $creator);

        $person = array_filter([
            '@type' => 'Person',
            '@id' => $url.'#person',
            'name' => $creator->name,
            'url' => $url,
            'description' => $creator->bio,
            'image' => $creator->avatar_url,
            'sameAs' => $creator->socialAccounts->map(fn (CreatorSocialAccount $a) => $a->profile_url)->values()->all(),
            'knowsAbout' => $creator->category?->name,
            'homeLocation' => $creator->location ? ['@type' => 'Place', 'name' => $creator->location] : null,
        ]);

        $breadcrumbs = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Creators', 'item' => route('creators.index')],
        ];
        if ($creator->category) {
            $breadcrumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $creator->category->name, 'item' => route('creators.index', ['category' => $creator->category->slug])];
        }
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => count($breadcrumbs) + 1, 'name' => $creator->name, 'item' => $url];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ProfilePage',
            'url' => $url,
            'dateModified' => ($creator->metrics_synced_at ?? $creator->updated_at)?->toIso8601String(),
            'mainEntity' => $person,
            'breadcrumb' => ['@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbs],
            'isPartOf' => ['@id' => url('/').'#website'],
        ];
    }
}
