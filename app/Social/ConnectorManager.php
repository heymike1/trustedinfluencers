<?php

namespace App\Social;

use App\Enums\Platform;
use App\Social\Connectors\InstagramConnector;
use App\Social\Connectors\XConnector;
use App\Social\Connectors\YouTubeConnector;
use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Fake\FakeConnector;
use Illuminate\Contracts\Container\Container;

/**
 * Resolves the connector for a platform based on the configured driver.
 */
class ConnectorManager
{
    /** @var array<string, SocialPlatformConnector> */
    private array $resolved = [];

    public function __construct(private readonly Container $container) {}

    public function for(Platform $platform): SocialPlatformConnector
    {
        return $this->resolved[$platform->value] ??= $this->make($platform);
    }

    public function usingFakeDriver(): bool
    {
        return config('social.driver', 'fake') === 'fake';
    }

    private function make(Platform $platform): SocialPlatformConnector
    {
        if ($this->usingFakeDriver()) {
            return $this->container->make(FakeConnector::class, ['platform' => $platform]);
        }

        $class = match ($platform) {
            Platform::YouTube => YouTubeConnector::class,
            Platform::Instagram => InstagramConnector::class,
            Platform::X => XConnector::class,
        };

        return $this->container->make($class);
    }

    /** Swap in a connector (used by tests). */
    public function fake(Platform $platform, SocialPlatformConnector $connector): void
    {
        $this->resolved[$platform->value] = $connector;
    }
}
