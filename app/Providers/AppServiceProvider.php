<?php

namespace App\Providers;

use App\Social\ConnectorManager;
use App\Social\Login\FakeGoogleLoginProvider;
use App\Social\Login\GoogleLoginProvider;
use App\Social\Login\LiveGoogleLoginProvider;
use App\Support\Settings;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
        $this->app->singleton(ConnectorManager::class);
        $this->app->bind(GoogleLoginProvider::class, fn () => config('social.driver', 'fake') === 'fake' ? new FakeGoogleLoginProvider : new LiveGoogleLoginProvider);
    }

    public function boot(Settings $settings): void
    {
        // Admin-managed values override the config files before anything reads them.
        $settings->apply();
    }
}
