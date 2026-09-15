<?php

namespace App\Social\Support;

use App\Enums\Platform;
use App\Social\Data\NormalizedHandle;

/**
 * Turns whatever a visitor typed ("@john", "youtube.com/@john", "https://www.instagram.com/john/")
 * into one canonical handle per platform so the same account always resolves to the same record.
 */
class HandleNormalizer
{
    /**
     * @return NormalizedHandle|null null when the input cannot be a valid handle/URL for the platform.
     */
    public function normalize(Platform $platform, string $input): ?NormalizedHandle
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        return match ($platform) {
            Platform::YouTube => $this->youtube($input),
            Platform::Instagram => $this->instagram($input),
            Platform::X => $this->x($input),
        };
    }

    /**
     * Guess the platform from a URL. Returns null for bare handles.
     */
    public function detectPlatform(string $input): ?Platform
    {
        $host = $this->host($input);

        return match (true) {
            $host === null => null,
            str_contains($host, 'youtube.com'), $host === 'youtu.be' => Platform::YouTube,
            str_contains($host, 'instagram.com') => Platform::Instagram,
            $host === 'x.com', str_ends_with($host, '.x.com'), str_contains($host, 'twitter.com') => Platform::X,
            default => null,
        };
    }

    private function youtube(string $input): ?NormalizedHandle
    {
        $path = $this->pathIfUrl($input, ['youtube.com', 'youtu.be']);

        if ($path === false) {
            return null;
        }

        if ($path !== null) {
            // /channel/UCxxxx → we know the canonical id but not the handle.
            if (preg_match('~^/channel/(UC[\w-]{20,})/?~', $path, $m)) {
                return new NormalizedHandle(strtolower($m[1]), 'https://www.youtube.com/channel/'.$m[1], $m[1]);
            }

            if (! preg_match('~^/(?:@|c/|user/)?([^/?#]+)~', $path, $m)) {
                return null;
            }

            $candidate = $m[1];
        } else {
            $candidate = $input;
        }

        $handle = $this->cleanHandle($candidate, '/^[a-z0-9._-]{3,30}$/');

        if ($handle === null) {
            return null;
        }

        return new NormalizedHandle($handle, Platform::YouTube->profileUrl($handle));
    }

    private function instagram(string $input): ?NormalizedHandle
    {
        $path = $this->pathIfUrl($input, ['instagram.com']);

        if ($path === false) {
            return null;
        }

        if ($path !== null) {
            if (! preg_match('~^/([^/?#]+)~', $path, $m) || in_array($m[1], ['p', 'reel', 'reels', 'stories', 'explore'], true)) {
                return null;
            }
            $candidate = $m[1];
        } else {
            $candidate = $input;
        }

        $handle = $this->cleanHandle($candidate, '/^[a-z0-9._]{1,30}$/');

        if ($handle === null) {
            return null;
        }

        return new NormalizedHandle($handle, Platform::Instagram->profileUrl($handle));
    }

    private function x(string $input): ?NormalizedHandle
    {
        $path = $this->pathIfUrl($input, ['x.com', 'twitter.com']);

        if ($path === false) {
            return null;
        }

        if ($path !== null) {
            if (! preg_match('~^/([^/?#]+)~', $path, $m) || in_array($m[1], ['i', 'home', 'search', 'explore', 'hashtag'], true)) {
                return null;
            }
            $candidate = $m[1];
        } else {
            $candidate = $input;
        }

        $handle = $this->cleanHandle($candidate, '/^[a-z0-9_]{1,15}$/');

        if ($handle === null) {
            return null;
        }

        return new NormalizedHandle($handle, Platform::X->profileUrl($handle));
    }

    /**
     * @return string|null|false path when the input is a URL on an allowed host, null when it is a bare
     *                           handle, false when it is a URL for a different site.
     */
    private function pathIfUrl(string $input, array $hosts): string|null|false
    {
        $host = $this->host($input);

        if ($host === null) {
            return null;
        }

        foreach ($hosts as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                $url = $this->withScheme($input);

                return parse_url($url, PHP_URL_PATH) ?: '/';
            }
        }

        return false;
    }

    private const KNOWN_HOSTS = ['youtube.com', 'youtu.be', 'instagram.com', 'x.com', 'twitter.com'];

    /**
     * The host when the input is clearly a URL. Bare handles (including dotted ones like
     * "john.smith") return null.
     */
    private function host(string $input): ?string
    {
        if (str_starts_with($input, '@') || ! str_contains($input, '.')) {
            return null;
        }

        $explicitUrl = (bool) preg_match('~^(https?://|www\.)~i', $input);
        $host = parse_url($this->withScheme($input), PHP_URL_HOST);

        if (! $host) {
            return null;
        }

        $host = strtolower(preg_replace('/^(www|m|mobile)\./i', '', $host));

        $known = collect(self::KNOWN_HOSTS)->contains(
            fn (string $h) => $host === $h || str_ends_with($host, '.'.$h)
        );

        return ($explicitUrl || $known) ? $host : null;
    }

    private function withScheme(string $input): string
    {
        return preg_match('~^https?://~i', $input) ? $input : 'https://'.$input;
    }

    private function cleanHandle(string $candidate, string $pattern): ?string
    {
        $handle = strtolower(ltrim(trim($candidate), '@'));

        return preg_match($pattern, $handle) ? $handle : null;
    }
}
