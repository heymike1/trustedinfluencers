<?php

namespace App\Support;

/**
 * The logo for a sponsor card. We have their website, so we point at Google's favicon service
 * for that domain: nothing to fetch, nothing to store, and it keeps up when they rebrand.
 */
class SiteLogo
{
    private const SERVICE = 'https://www.google.com/s2/favicons';

    private const SIZE = 64;

    /** @return string|null The logo's address, or null when that is not a website we can read. */
    public static function forWebsite(?string $website): ?string
    {
        $host = self::host($website);

        return $host ? self::SERVICE.'?domain='.urlencode($host).'&sz='.self::SIZE : null;
    }

    /** The bare domain out of whatever they typed, with or without scheme, path or query. */
    public static function host(?string $website): ?string
    {
        $website = trim((string) $website);

        if ($website === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $website)) {
            $website = 'https://'.$website;
        }

        $host = parse_url($website, PHP_URL_HOST);

        return $host && str_contains($host, '.') ? strtolower($host) : null;
    }
}
