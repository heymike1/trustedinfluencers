<?php

namespace App\Social\Data;

/**
 * Account-level audience breakdowns. Every map is label → percentage (0–100) except
 * accountMetrics, which holds recent totals (views_28d, profile_visits_30d, …).
 * Empty arrays mean "the platform does not provide this".
 */
final class AudienceInsights
{
    public function __construct(
        public readonly array $age = [],
        public readonly array $gender = [],
        public readonly array $countries = [],
        public readonly array $cities = [],
        public readonly array $devices = [],
        public readonly array $followerType = [],
        public readonly array $accountMetrics = [],
        public readonly array $raw = [],
    ) {}

    public function isEmpty(): bool
    {
        return $this->age === [] && $this->gender === [] && $this->countries === [] && $this->cities === []
            && $this->devices === [] && $this->followerType === [] && $this->accountMetrics === [];
    }
}
