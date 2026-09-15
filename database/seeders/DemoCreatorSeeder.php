<?php

namespace Database\Seeders;

use App\Actions\Sync\ConnectAccount;
use App\Actions\Sync\RefreshCreatorSummary;
use App\Enums\ClaimStatus;
use App\Enums\ConnectionStatus;
use App\Enums\Platform;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorSocialAccount;
use App\Models\User;
use App\Social\ConnectorManager;
use App\Social\Data\OAuthRequest;
use App\Social\Fake\FakeConnector;
use App\Social\Fake\FakeDataGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ~30 realistic creators. Claimed ones are imported through the real sync pipeline using the
 * fake connector, so their content, snapshots and performance metrics are produced exactly the
 * way a live claim would produce them.
 */
class DemoCreatorSeeder extends Seeder
{
    /**
     * accounts:  platform => handle (must exist in FakeAccounts for curated numbers)
     * connected: platforms the creator has connected (implies claimed)
     * state:     optional override applied after import: outdated | needs_reconnection | sync_failed
     */
    private const CREATORS = [
        ['name' => 'John Smith', 'category' => 'Finance', 'location' => 'Manchester, UK', 'website' => 'https://johnsmith.money', 'accounts' => ['youtube' => 'johnsmith'], 'connected' => ['youtube']],
        ['name' => 'Priya Natarajan', 'category' => 'Education', 'location' => 'Bengaluru, India', 'accounts' => ['youtube' => 'priyaexplains'], 'connected' => ['youtube']],
        ['name' => 'Big Tech Daily', 'category' => 'Tech', 'location' => 'Austin, TX', 'accounts' => ['youtube' => 'bigtechdaily'], 'connected' => ['youtube']],
        ['name' => 'Tom Nakamura', 'category' => 'Gaming', 'location' => 'Osaka, Japan', 'accounts' => ['youtube' => 'tomplays'], 'connected' => ['youtube']],
        ['name' => 'Emily Zhang', 'category' => 'Tech', 'location' => 'San Francisco, CA', 'website' => 'https://emilyzhang.dev', 'accounts' => ['youtube' => 'emilyzhangdev', 'x' => 'emilyzhang'], 'connected' => ['youtube', 'x']],
        ['name' => 'Jamal Carter', 'category' => 'Fitness', 'location' => 'Atlanta, GA', 'accounts' => ['youtube' => 'jamalfit', 'instagram' => 'jamalfit'], 'connected' => ['youtube']],
        ['name' => "Isabella's Kitchen", 'category' => 'Food', 'location' => 'Bologna, Italy', 'accounts' => ['youtube' => 'isabellakitchen'], 'connected' => ['youtube']],
        ['name' => 'Grace Liu', 'category' => 'Education', 'location' => 'Toronto, Canada', 'accounts' => ['youtube' => 'gracelearns'], 'connected' => ['youtube']],
        ['name' => 'Lucas Moreau', 'category' => 'Business', 'location' => 'Lyon, France', 'website' => 'https://lucasmoreau.co', 'accounts' => ['youtube' => 'lucasmoreau', 'x' => 'lucasmoreau'], 'connected' => ['youtube', 'x'], 'state' => ['x' => 'needs_reconnection']],
        ['name' => 'Mia Andersson', 'category' => 'Photography', 'location' => 'Gothenburg, Sweden', 'accounts' => ['youtube' => 'miaandersson'], 'connected' => ['youtube']],
        ['name' => 'Amara Okafor', 'category' => 'Tech', 'accounts' => ['youtube' => 'amaratech']],
        ['name' => 'Hannah Weiss', 'category' => 'Travel', 'accounts' => ['youtube' => 'hannahtravels']],
        ['name' => 'Oliver Grant', 'category' => 'Comedy', 'accounts' => ['youtube' => 'olivergrant']],
        ['name' => 'Noah Kim', 'category' => 'Finance', 'accounts' => ['youtube' => 'noahkimmoney']],
        ['name' => 'Leo Martins', 'category' => 'Gaming', 'accounts' => ['youtube' => 'leomartins']],

        ['name' => 'Lena Fischer', 'category' => 'Fitness', 'location' => 'Berlin, Germany', 'website' => 'https://lena.fit', 'accounts' => ['instagram' => 'lena.fit'], 'connected' => ['instagram']],
        ['name' => 'Sofia Marchetti', 'category' => 'Food', 'location' => 'Rome, Italy', 'accounts' => ['instagram' => 'sofiacooks'], 'connected' => ['instagram'], 'state' => ['instagram' => 'outdated']],
        ['name' => 'Ava Thompson', 'category' => 'Beauty', 'location' => 'London, UK', 'accounts' => ['instagram' => 'avathompson'], 'connected' => ['instagram']],
        ['name' => 'Freya Larsen', 'category' => 'Fitness', 'location' => 'Copenhagen, Denmark', 'accounts' => ['instagram' => 'freyalarsen'], 'connected' => ['instagram']],
        ['name' => 'Yuki Tanaka', 'category' => 'Food', 'location' => 'Tokyo, Japan', 'accounts' => ['instagram' => 'yukieats'], 'connected' => ['instagram']],
        ['name' => 'Charlotte Dubois', 'category' => 'Business', 'location' => 'Paris, France', 'accounts' => ['instagram' => 'charlotte.dubois'], 'connected' => ['instagram'], 'state' => ['instagram' => 'sync_failed']],
        ['name' => 'Chloe Bennett', 'category' => 'Beauty', 'accounts' => ['instagram' => 'chloebeauty']],
        ['name' => 'Rafael Costa', 'category' => 'Photography', 'accounts' => ['instagram' => 'rafaelcosta.photo']],
        ['name' => 'Nina Petrova', 'category' => 'Lifestyle', 'accounts' => ['instagram' => 'ninapetrova']],
        ['name' => 'Mateo Alvarez', 'category' => 'Travel', 'accounts' => ['instagram' => 'mateo.travels']],

        ['name' => 'Marcus Reid', 'category' => 'Business', 'location' => 'Denver, CO', 'website' => 'https://marcusreid.com', 'accounts' => ['x' => 'marcusreid'], 'connected' => ['x']],
        ['name' => 'Daniel Park', 'category' => 'Finance', 'location' => 'Seoul, South Korea', 'accounts' => ['x' => 'danielparkfi'], 'connected' => ['x']],
        ['name' => 'Zara Hussain', 'category' => 'Lifestyle', 'location' => 'Dubai, UAE', 'accounts' => ['x' => 'zarahussain'], 'connected' => ['x']],
        ['name' => 'Ethan Brooks', 'category' => 'Tech', 'location' => 'Seattle, WA', 'accounts' => ['x' => 'ethanbrooks'], 'connected' => ['x']],
        ['name' => 'Kenji Watanabe', 'category' => 'Gaming', 'accounts' => ['x' => 'kenjiplays']],
        ['name' => 'Samuel Adeyemi', 'category' => 'Comedy', 'accounts' => ['x' => 'samadeyemi']],
    ];

    public function run(): void
    {
        // Demo data must always come from the fake connector and be imported synchronously.
        config(['social.driver' => 'fake', 'queue.default' => 'sync']);
        app()->forgetInstance(ConnectorManager::class);

        $connectors = app(ConnectorManager::class);
        $generator = app(FakeDataGenerator::class);
        $connect = app(ConnectAccount::class);
        $categories = CreatorCategory::pluck('id', 'name');

        $brandUser = User::factory()->create(['name' => 'Brand Manager', 'email' => 'brand@example.com']);

        foreach (self::CREATORS as $i => $definition) {
            $createdAt = now()->subDays(60 - $i * 2)->subHours($i * 3);

            $creator = Creator::create([
                'name' => $definition['name'],
                'slug' => Creator::uniqueSlugFor($definition['name']),
                'creator_category_id' => $categories[$definition['category']] ?? null,
                'location' => $definition['location'] ?? null,
                'website' => $definition['website'] ?? null,
            ]);
            $creator->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            foreach ($definition['accounts'] as $platformValue => $handle) {
                $platform = Platform::from($platformValue);
                $connector = $connectors->for($platform);

                $account = $creator->socialAccounts()->create([
                    'platform' => $platform,
                    'handle' => $handle,
                    'profile_url' => $platform->profileUrl($handle),
                    'connection_status' => ConnectionStatus::Unconnected,
                ]);

                // Public data, as the SyncPublicProfile job would have filled it in.
                if ($public = $connector->publicProfile($handle)) {
                    $account->forceFill([
                        'provider_account_id' => $public->providerAccountId,
                        'display_name' => $public->displayName,
                        'avatar_url' => $public->avatarUrl,
                        'follower_count' => $public->followerCount,
                        'public_data' => array_filter(['bio' => $public->bio]),
                        'public_synced_at' => $createdAt,
                    ])->save();
                    $creator->fill(['avatar_url' => $creator->avatar_url ?? $public->avatarUrl, 'bio' => $creator->bio ?? $public->bio])->save();
                } else {
                    // Instagram has no public lookup; the submitter only gave us the handle. Use the fake
                    // account's follower count as "what the visitor typed" so cards have something to show.
                    $identity = $generator->identity($platform, $handle);
                    $account->forceFill(['follower_count' => $identity->followerCount, 'display_name' => $identity->displayName])->save();
                }
            }

            if (! empty($definition['connected'])) {
                $this->claim($creator, $definition, $connectors, $generator, $connect);
            }

            app(RefreshCreatorSummary::class)->handle($creator->fresh());
        }

        $this->seedContactRequests($brandUser);
        $this->seedFailedClaim();
    }

    private function claim(Creator $creator, array $definition, ConnectorManager $connectors, FakeDataGenerator $generator, ConnectAccount $connect): void
    {
        $email = Str::slug($definition['name']).'@example.com';
        $user = User::factory()->create(['name' => $definition['name'], 'email' => $email]);
        $claimedAt = $creator->created_at->addDays(rand(1, 10))->min(now()->subHours(6));

        $creator->forceFill(['user_id' => $user->id, 'claimed_at' => $claimedAt])->save();

        foreach ($definition['connected'] as $platformValue) {
            $platform = Platform::from($platformValue);
            $account = $creator->socialAccounts()->where('platform', $platform)->firstOrFail();
            $connector = $connectors->for($platform);

            $tokens = $connector->exchangeCode(FakeConnector::codeFor($account->handle), new OAuthRequest('seed', 'seed', 'http://localhost/seed'));
            $identity = $generator->identity($platform, $account->handle);

            $creator->claims()->create([
                'user_id' => $user->id,
                'creator_social_account_id' => $account->id,
                'platform' => $platform,
                'status' => ClaimStatus::Verified,
                'expected_provider_account_id' => $account->provider_account_id,
                'returned_provider_account_id' => $identity->providerAccountId,
                'returned_handle' => $identity->handle,
                'verified_at' => $claimedAt,
                'created_at' => $claimedAt,
                'updated_at' => $claimedAt,
            ]);

            // Runs the whole chain synchronously (queue.default = sync).
            $connect->handle($account, $identity, $tokens);

            $account->refresh()->forceFill(['connected_at' => $claimedAt])->save();

            match ($definition['state'][$platformValue] ?? null) {
                'outdated' => $this->markOutdated($account),
                'needs_reconnection' => $account->forceFill([
                    'connection_status' => ConnectionStatus::NeedsReconnection,
                    'last_sync_error' => $platform->label().' refused to refresh the token: invalid_grant',
                ])->save(),
                'sync_failed' => $account->forceFill([
                    'connection_status' => ConnectionStatus::SyncFailed,
                    'last_sync_error' => $platform->label().': media insights failed with HTTP 500 (An unknown error has occurred.)',
                ])->save(),
                default => null,
            };
        }
    }

    private function markOutdated(CreatorSocialAccount $account): void
    {
        $when = now()->subDays(21);
        $account->forceFill(['last_synced_at' => $when, 'sync_requested_at' => $when])->save();
        $account->snapshots()->update(['captured_at' => $when]);
        $account->performanceMetrics()->update(['calculated_at' => $when]);
    }

    private function seedContactRequests(User $brandUser): void
    {
        $subjects = [
            ['Sponsored video for Q4 launch', 'We are launching a budgeting app in November and would love a dedicated video. Budget is flexible for the right fit.'],
            ['Long-term ambassador programme', 'We work with a small group of creators on a 6-month basis. Could we set up a call to walk you through the details?'],
            ['Product seeding', 'Would you be open to receiving our new kit for an honest review? No strings attached.'],
        ];

        Creator::claimed()->inRandomOrder()->limit(6)->get()->each(function (Creator $creator, int $i) use ($subjects, $brandUser) {
            [$subject, $message] = $subjects[$i % count($subjects)];
            $creator->contactRequests()->create([
                'name' => $brandUser->name,
                'email' => $brandUser->email,
                'company' => 'Northwind Labs',
                'subject' => $subject,
                'message' => $message,
                'delivered_at' => now()->subDays($i + 1),
                'read_at' => $i % 2 ? now()->subDays($i) : null,
                'created_at' => now()->subDays($i + 1),
            ]);
        });

        Creator::unclaimed()->inRandomOrder()->limit(3)->get()->each(function (Creator $creator, int $i) use ($subjects) {
            [$subject, $message] = $subjects[$i % count($subjects)];
            $creator->contactRequests()->create([
                'name' => 'Sam Rivera',
                'email' => 'sam@brightpath.example',
                'company' => 'Brightpath',
                'subject' => $subject,
                'message' => $message,
                'created_at' => now()->subDays($i + 2),
            ]);
        });
    }

    /** A realistic mismatch: someone signed in to the wrong account while claiming. */
    private function seedFailedClaim(): void
    {
        $creator = Creator::unclaimed()->whereHas('socialAccounts', fn ($q) => $q->where('platform', Platform::YouTube))->first();

        if (! $creator) {
            return;
        }

        $account = $creator->socialAccounts()->first();
        $user = User::factory()->create(['name' => 'Chris Doyle', 'email' => 'chris@example.com']);

        $creator->claims()->create([
            'user_id' => $user->id,
            'creator_social_account_id' => $account->id,
            'platform' => $account->platform,
            'status' => ClaimStatus::Failed,
            'expected_provider_account_id' => $account->provider_account_id,
            'returned_provider_account_id' => 'UCx1QF1zK0nfaCvHdb7Wn9hA',
            'returned_handle' => 'chrisdoyle',
            'failure_reason' => "You signed in to YouTube as @chrisdoyle, but this profile is for @{$account->handle}.",
            'created_at' => now()->subDays(3),
        ]);
    }
}
