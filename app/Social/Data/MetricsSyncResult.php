<?php

namespace App\Social\Data;

final class MetricsSyncResult
{
    /**
     * @param  array<string, int|float>  $accountMetrics
     * @param  ContentMetrics[]  $contentMetrics
     */
    public function __construct(
        public readonly array $accountMetrics = [],
        public readonly array $contentMetrics = [],
        public readonly array $raw = [],
        public readonly ?AudienceInsights $audience = null,
    ) {}
}
