<?php

namespace App\Social\Data;

use App\Enums\ContentType;
use Carbon\CarbonImmutable;

final class ContentItem
{
    public function __construct(
        public readonly string $providerContentId,
        public readonly ContentType $contentType,
        public readonly ?string $title,
        public readonly ?string $url,
        public readonly ?string $thumbnailUrl,
        public readonly ?CarbonImmutable $publishedAt,
        public readonly ?int $durationSeconds = null,
        /** Metrics that came back with the listing call (usually public counts). */
        public readonly array $metrics = [],
        public readonly array $raw = [],
    ) {}
}
