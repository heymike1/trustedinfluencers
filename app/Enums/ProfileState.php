<?php

namespace App\Enums;

/**
 * The public-facing state of a creator profile. Derived, never stored.
 */
enum ProfileState: string
{
    case Unclaimed = 'unclaimed';
    case Claimed = 'claimed';
    case VerifiedMetrics = 'verified_metrics';
    case MetricsOutdated = 'metrics_outdated';
    case NeedsReconnection = 'needs_reconnection';

    public function isClaimed(): bool
    {
        return $this !== self::Unclaimed;
    }
}
