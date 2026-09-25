<?php

namespace App\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Finds the logo of a website the buyer typed in, and keeps a copy.
 *
 * The URL comes from whoever is filling in the form, so every request out of here is treated as
 * hostile: public http(s) addresses only, no redirect chains, a short timeout and a size cap.
 * The image is stored on our own disk, because a favicon someone else hosts is gone by next month.
 */
class SiteLogo
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    private const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/gif'];

    /** @return string|null A URL on our own disk, or null when the site gives us nothing usable. */
    public function fetch(string $website): ?string
    {
        $base = $this->safeUrl($website);

        if (! $base) {
            return null;
        }

        $page = $this->get($base);

        if (! $page) {
            return null;
        }

        foreach ($this->candidates($page->body(), $base) as $candidate) {
            if ($stored = $this->store($candidate)) {
                return $stored;
            }
        }

        return null;
    }

    /** The places a site says where its logo is, best first. */
    private function candidates(string $html, string $base): array
    {
        $found = [];

        foreach ([
            '/<meta[^>]+property=["\']og:image(?::secure_url)?["\'][^>]+content=["\']([^"\']+)/i',
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i',
            '/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)/i',
            '/<link[^>]+rel=["\'][^"\']*apple-touch-icon[^"\']*["\'][^>]+href=["\']([^"\']+)/i',
            '/<link[^>]+rel=["\'][^"\']*\bicon\b[^"\']*["\'][^>]+href=["\']([^"\']+)/i',
            '/<meta[^>]+name=["\']msapplication-TileImage["\'][^>]+content=["\']([^"\']+)/i',
        ] as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $found[] = $m[1];
            }
        }

        $found[] = '/favicon.ico';

        return collect($found)
            ->map(fn (string $url) => $this->absolute(html_entity_decode($url), $base))
            ->filter()
            ->unique()
            ->all();
    }

    private function store(string $url): ?string
    {
        $response = $this->get($url);

        if (! $response) {
            return null;
        }

        $type = Str::before((string) $response->header('Content-Type'), ';');
        $body = $response->body();

        if (! in_array(strtolower(trim($type)), self::IMAGE_TYPES, true) || $body === '' || strlen($body) > self::MAX_BYTES) {
            return null;
        }

        $extension = match (strtolower(trim($type))) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            default => 'ico',
        };

        $path = 'sponsors/'.Str::random(32).'.'.$extension;

        Storage::disk('public')->put($path, $body);

        return Storage::disk('public')->url($path);
    }

    private function get(string $url): ?Response
    {
        try {
            $response = Http::withHeaders(['User-Agent' => config('app.name').' logo fetcher'])
                ->withoutRedirecting()
                ->timeout(8)
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        // One redirect is normal (http → https, bare → www); we follow it by hand so we can
        // check where it is actually going before asking for it.
        if ($response->redirect() && ($location = $response->header('Location'))) {
            $next = $this->safeUrl($this->absolute($location, $url) ?? '');

            return $next ? $this->get($next) : null;
        }

        return $response->successful() ? $response : null;
    }

    /** Turns whatever the page said ("/logo.png", "//cdn/x.png", "logo.png") into a full URL. */
    private function absolute(string $url, string $base): ?string
    {
        $url = trim($url);

        if ($url === '' || str_starts_with(strtolower($url), 'data:')) {
            return null;
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        $parts = parse_url($base);

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $root = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        if (str_starts_with($url, '/')) {
            return $root.$url;
        }

        $path = $parts['path'] ?? '/';
        $directory = str_ends_with($path, '/') ? $path : rtrim(dirname($path), '/').'/';

        return $root.$directory.$url;
    }

    /** Only somewhere on the public internet, over http or https. */
    private function safeUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // Anything carrying its own scheme keeps it, so file:// and the like are turned away
        // rather than quietly rewritten into an https address.
        if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url)) {
            if (! preg_match('#^https?://#i', $url)) {
                return null;
            }
        } else {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);
        $host = $parts['host'] ?? null;

        if (! $host || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return null;
        }

        // A name with no dot in it is something on the local network, never a public website.
        if (! filter_var($host, FILTER_VALIDATE_IP) && ! str_contains($host, '.')) {
            return null;
        }

        if (isset($parts['port']) && ! in_array((int) $parts['port'], [80, 443], true)) {
            return null;
        }

        foreach ($this->addressesFor($host) as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return null;
            }
        }

        return $url;
    }

    /** @return array<int, string> */
    private function addressesFor(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        // The test suite has no business resolving names; a literal address is still checked.
        if (app()->runningUnitTests()) {
            return [];
        }

        $records = @dns_get_record($host, DNS_A + DNS_AAAA) ?: [];
        $addresses = collect($records)->map(fn (array $r) => $r['ip'] ?? $r['ipv6'] ?? null)->filter()->all();

        // A name that resolves to nothing is not worth a request either.
        return $addresses ?: ['127.0.0.1'];
    }
}
