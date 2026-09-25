<?php

namespace Tests\Feature;

use App\Support\SiteLogo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteLogoTest extends TestCase
{
    public function test_it_takes_the_logo_a_site_advertises(): void
    {
        Storage::fake('public');

        Http::fake([
            'https://blotato.example' => Http::response('<html><head><meta property="og:image" content="/brand/logo.png"></head></html>', 200, ['Content-Type' => 'text/html']),
            'https://blotato.example/brand/logo.png' => Http::response('binary-png', 200, ['Content-Type' => 'image/png']),
        ]);

        $url = app(SiteLogo::class)->fetch('blotato.example');

        $this->assertNotNull($url);
        $this->assertCount(1, Storage::disk('public')->files('sponsors'));
    }

    public function test_it_falls_back_to_the_favicon(): void
    {
        Storage::fake('public');

        Http::fake([
            'https://blotato.example/favicon.ico' => Http::response('icon', 200, ['Content-Type' => 'image/x-icon']),
            'https://blotato.example*' => Http::response('<html><head></head></html>', 200, ['Content-Type' => 'text/html']),
        ]);

        $this->assertNotNull(app(SiteLogo::class)->fetch('https://blotato.example'));
    }

    public function test_it_refuses_to_fetch_anything_but_a_public_website(): void
    {
        Http::fake();

        $logos = app(SiteLogo::class);

        $this->assertNull($logos->fetch('http://127.0.0.1/admin'));
        $this->assertNull($logos->fetch('http://10.0.0.5/'));
        $this->assertNull($logos->fetch('file:///etc/passwd'));
        $this->assertNull($logos->fetch('http://169.254.169.254/latest/meta-data/'));
        $this->assertNull($logos->fetch('https://example.com:8080/'));

        Http::assertNothingSent();
    }

    public function test_a_page_that_serves_something_other_than_an_image_gives_nothing(): void
    {
        Storage::fake('public');

        Http::fake([
            'https://blotato.example*' => Http::response('<html><head><link rel="icon" href="/logo.svg"></head></html>', 200, ['Content-Type' => 'text/html']),
        ]);

        $this->assertNull(app(SiteLogo::class)->fetch('https://blotato.example'));
    }
}
