<?php

namespace App\Providers;

use App\Social\ConnectorManager;
use App\Social\Login\FakeGoogleLoginProvider;
use App\Social\Login\GoogleLoginProvider;
use App\Social\Login\LiveGoogleLoginProvider;
use App\Sponsors\CheckoutGateway;
use App\Sponsors\FakeCheckout;
use App\Sponsors\StripeCheckout;
use App\Support\Settings;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
        $this->app->singleton(ConnectorManager::class);
        // Stripe as soon as there is a secret. Without one we fall back to the stand-in checkout,
        // but only where nobody is really buying: in production that is an error, not a free spot.
        $this->app->bind(CheckoutGateway::class, fn () => config('services.stripe.secret') || ! app()->environment('local', 'testing')
            ? new StripeCheckout
            : new FakeCheckout);
        $this->app->bind(GoogleLoginProvider::class, fn () => config('social.driver', 'fake') === 'fake' ? new FakeGoogleLoginProvider : new LiveGoogleLoginProvider);
    }

    public function boot(Settings $settings): void
    {
        // Admin-managed values override the config files before anything reads them.
        $settings->apply();
    }
}
