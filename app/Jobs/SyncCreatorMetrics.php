<?php

namespace App\Jobs;

use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Data\AccountContext;
use Illuminate\Support\Facades\DB;

/**
 * Pulls private analytics for the imported content and records a snapshot per item.
 */
class SyncCreatorMetrics extends AccountSyncJob
{
    protected function sync(CreatorSocialAccount $account, SocialPlatformConnector $connector, AccountContext $context): void
    {
        $contents = $account->contents()->orderByDesc('published_at')->limit(config('social.sync.content_limit', 30))->get()->keyBy('provider_content_id');

        if ($contents->isEmpty()) {
            return;
        }

        $result = $connector->syncMetrics($context, $contents->values());
        $now = now();

        DB::transaction(function () use ($account, $contents, $result, $now) {
            foreach ($result->contentMetrics as $metrics) {
                /** @var SocialContent|null $content */
                $content = $contents->get($metrics->providerContentId);

                if ($content === null) {
                    continue;
                }

                // Analytics beat the public counts fetched with the listing.
                $content->forceFill([
                    'metrics' => $metrics->metrics + ($content->metrics ?? []),
                    'insights' => $metrics->insights !== [] ? $metrics->insights + ($content->insights ?? []) : $content->insights,
                    'metrics_synced_at' => $now,
                ])->save();

                $account->snapshots()->create([
                    'social_content_id' => $content->id,
                    'captured_at' => $now,
                    'metrics' => $content->metrics,
                    'raw_provider_data' => $metrics->raw ?: null,
                ]);
            }

            if ($result->accountMetrics !== []) {
                $account->snapshots()->create([
                    'captured_at' => $now,
                    'metrics' => $result->accountMetrics,
                    'raw_provider_data' => $result->raw ?: null,
                ]);
            }

            if ($result->audience && ! $result->audience->isEmpty()) {
                $audience = $result->audience;

                $account->audience()->updateOrCreate([], [
                    'age' => $audience->age ?: null,
                    'gender' => $audience->gender ?: null,
                    'countries' => $audience->countries ?: null,
                    'cities' => $audience->cities ?: null,
                    'devices' => $audience->devices ?: null,
                    'follower_type' => $audience->followerType ?: null,
                    'account_metrics' => $audience->accountMetrics ?: null,
                    'captured_at' => $now,
                ]);

                $account->snapshots()->create([
                    'captured_at' => $now,
                    'metrics' => ['audience' => array_filter([
                        'age' => $audience->age,
                        'gender' => $audience->gender,
                        'countries' => $audience->countries,
                        'cities' => $audience->cities,
                        'devices' => $audience->devices,
                        'follower_type' => $audience->followerType,
                    ]) + $audience->accountMetrics],
                    'raw_provider_data' => $audience->raw ?: null,
                ]);
            }
        });
    }
}
