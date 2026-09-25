<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connector driver
    |--------------------------------------------------------------------------
    |
    | "fake" simulates OAuth and API responses locally so the whole product
    | works without Meta, Google or X app approval. "live" talks to the
    | official APIs using the credentials below.
    |
    */

    'driver' => env('SOCIAL_CONNECTOR_DRIVER', 'fake'),

    /*
    |--------------------------------------------------------------------------
    | Google sign-in
    |--------------------------------------------------------------------------
    |
    | The site login. Identity only (name, email, avatar): it does not grant
    | YouTube access, that is still a separate connection on the profile.
    | Defaults to the YouTube OAuth client, since both live in one Google project.
    |
    */

    'google_login' => [
        'client_id' => env('GOOGLE_CLIENT_ID') ?: env('YOUTUBE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET') ?: env('YOUTUBE_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Platforms creators can connect
    |--------------------------------------------------------------------------
    |
    | Platforms left out here stay in the code and keep showing the data they
    | already have, but they are not offered anywhere and cannot be claimed or
    | connected. YouTube is parked until Google has verified the OAuth scopes.
    |
    */

    'enabled_platforms' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SOCIAL_ENABLED_PLATFORMS', 'instagram,x'))
    ))),

    'platforms' => [
        'youtube' => [
            'client_id' => env('YOUTUBE_CLIENT_ID'),
            'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
            // Optional. Used only to resolve public channel data for unclaimed profiles.
            'api_key' => env('YOUTUBE_API_KEY'),
        ],
        'instagram' => [
            'client_id' => env('INSTAGRAM_CLIENT_ID'),
            'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        ],
        'x' => [
            'client_id' => env('X_CLIENT_ID'),
            'client_secret' => env('X_CLIENT_SECRET'),
            // Optional app-only bearer token for public user lookups.
            'bearer_token' => env('X_BEARER_TOKEN'),
        ],
    ],

    'sync' => [
        // How many recent items to import per account.
        'content_limit' => (int) env('SOCIAL_SYNC_CONTENT_LIMIT', 50),
        // Minimum gap between manual "Sync now" requests by a creator.
        'manual_cooldown_minutes' => (int) env('SOCIAL_SYNC_MANUAL_COOLDOWN', 60),
        // Verified metrics older than this are shown as "Metrics outdated".
        'stale_after_days' => (int) env('SOCIAL_SYNC_STALE_AFTER_DAYS', 7),
        // The scheduler re-syncs connected accounts this often.
        'refresh_every_hours' => (int) env('SOCIAL_SYNC_REFRESH_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sponsor rails
    |--------------------------------------------------------------------------
    |
    | The two columns beside the page. A rail that is not full shows an "open
    | slot" card with your price, up to the target number of cards. Set the
    | price to null to switch that card off.
    |
    */

    'sponsors' => [
        'slots_per_rail' => (int) env('SPONSOR_SLOTS_PER_RAIL', 4),
        'price' => env('SPONSOR_PRICE', '€250'),
        // A booking runs this many days from the day it goes live. One month, counted in days.
        'days' => (int) env('SPONSOR_DAYS', 30),
        // Held when every spot is taken: it books the first one that frees up.
        'advance_price' => env('SPONSOR_ADVANCE_PRICE', '€999'),
        'contact' => env('SPONSOR_CONTACT', env('APP_CONTACT_EMAIL', 'info@runmorebrands.com')),
    ],

    'contact' => [
        // Forward contact requests for unclaimed profiles to their public email.
        // Off by default: the address was submitted by a third party and is unverified.
        'forward_to_public_email' => (bool) env('CONTACT_FORWARD_TO_PUBLIC_EMAIL', false),
    ],

];
