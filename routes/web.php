<?php

use App\Http\Controllers\AccountConnectionController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\FakeOAuthController;
use App\Http\Controllers\FakeSponsorCheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SponsorCheckoutController;
use App\Http\Controllers\SponsorWebhookController;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::view('/about', 'about')->name('about');
Route::view('/sponsor', 'sponsor')->name('sponsor');

// Buying a spot: hold it, pay for it, then fill in the card. The webhook is what marks it paid.
Route::post('/sponsor/checkout', [SponsorCheckoutController::class, 'start'])->middleware('throttle:10,1')->name('sponsor.checkout');
Route::get('/sponsor/checkout/{booking}/return', [SponsorCheckoutController::class, 'return'])->name('sponsor.return');
Route::get('/sponsor/checkout/{booking}/pending', [SponsorCheckoutController::class, 'pending'])->name('sponsor.pending');
Route::get('/sponsor/checkout/{booking}/fake', [FakeSponsorCheckoutController::class, 'show'])->name('sponsor.checkout.fake');
Route::post('/sponsor/checkout/{booking}/fake', [FakeSponsorCheckoutController::class, 'pay'])->name('sponsor.checkout.fake.pay');
Route::get('/sponsor/card/{token}', Livewire\SponsorCard::class)->name('sponsor.card');
Route::post('/webhooks/stripe', SponsorWebhookController::class)->name('webhooks.stripe');
Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

// Public directory
Route::get('/creators', Livewire\Marketplace::class)->name('creators.index');
Route::get('/creators/add', Livewire\AddCreator::class)->name('creators.create');
Route::get('/creators/{creator}', [CreatorController::class, 'show'])->name('creators.show');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', Livewire\Auth\Login::class)->name('login');
    Route::get('/register', Livewire\Auth\Register::class)->name('register');
    Route::get('/forgot-password', Livewire\Auth\ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', Livewire\Auth\ResetPassword::class)->name('password.reset');
    Route::get('/login/google', [GoogleLoginController::class, 'redirect'])->name('login.google');
    Route::get('/login/google/callback', [GoogleLoginController::class, 'callback'])->name('login.google.callback');
});
Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

// Claiming and OAuth
Route::middleware('auth')->group(function () {
    Route::get('/creators/{creator}/claim', [ClaimController::class, 'show'])->name('creators.claim');
    Route::post('/creators/{creator}/claim/{account}', [ClaimController::class, 'start'])->name('creators.claim.start');
    Route::post('/account/connections/{account}/connect', [AccountConnectionController::class, 'connect'])->name('account.connections.connect');
});
Route::get('/oauth/{platform}/callback', [OAuthController::class, 'callback'])->middleware('auth')->name('oauth.callback');
Route::get('/oauth/fake/google', [FakeOAuthController::class, 'showGoogle'])->name('oauth.fake.google');
Route::post('/oauth/fake/google', [FakeOAuthController::class, 'authorizeGoogle'])->name('oauth.fake.google.decide');
Route::get('/oauth/fake/{platform}/authorize', [FakeOAuthController::class, 'show'])->name('oauth.fake.authorize');
Route::post('/oauth/fake/{platform}/authorize', [FakeOAuthController::class, 'authorize'])->name('oauth.fake.decide');

// Creator account area
Route::middleware('auth')->prefix('account')->name('account')->group(function () {
    Route::get('/', Livewire\Account\Overview::class);
    Route::get('/profile', Livewire\Account\EditProfile::class)->name('.profile');
    Route::get('/connections', Livewire\Account\Connections::class)->name('.connections');
    Route::get('/requests', Livewire\Account\ContactRequests::class)->name('.requests');
    Route::get('/settings', Livewire\Account\LoginSettings::class)->name('.settings');
});

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/creators', Livewire\Admin\Creators::class)->name('creators');
    Route::get('/creators/{creator:id}', Livewire\Admin\CreatorDetail::class)->name('creators.show');
    Route::get('/users', Livewire\Admin\Users::class)->name('users');
    Route::get('/claims', Livewire\Admin\Claims::class)->name('claims');
    Route::get('/duplicates', Livewire\Admin\Duplicates::class)->name('duplicates');
    Route::get('/accounts', Livewire\Admin\Accounts::class)->name('accounts');
    Route::get('/content', Livewire\Admin\Content::class)->name('content');
    Route::get('/requests', Livewire\Admin\ContactRequests::class)->name('requests');
    Route::get('/categories', Livewire\Admin\Categories::class)->name('categories');
    Route::get('/sponsors', Livewire\Admin\Sponsors::class)->name('sponsors');
    Route::get('/settings', Livewire\Admin\SettingsScreen::class)->name('settings');
});
