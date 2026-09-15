<?php

namespace App\Jobs;

use App\Models\CreatorSocialAccount;
use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Data\AccountContext;
use App\Social\Data\ContentItem;

/**
 * Imports the most recent content items for the account.
 */
class SyncCreatorContent extends AccountSyncJob
{
    protected function sync(CreatorSocialAccount $account, SocialPlatformConnector $connector, AccountContext $context): void
    {
        $items = $connector->syncContent($context, config('social.sync.content_limit', 30));

        foreach ($items as $item) {
            /** @var ContentItem $item */
            $content = $account->contents()->firstOrNew(['provider_content_id' => $item->providerContentId]);

            $content->fill([
                'content_type' => $item->contentType,
                'title' => $item->title,
                'url' => $item->url,
                'thumbnail_url' => $item->thumbnailUrl,
                'duration_seconds' => $item->durationSeconds,
                'published_at' => $item->publishedAt,
            ]);

            // Listing endpoints usually return public counts; keep them until analytics arrive.
            if ($item->metrics !== [] && $content->metrics === null) {
                $content->metrics = $item->metrics;
            }

            $content->save();
        }
    }
}
