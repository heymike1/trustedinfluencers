<?php

namespace App\View;

use App\Models\CreatorAudienceInsight;

/**
 * The compact "GB 38% · 25-34" line used in tables.
 */
class AudienceSummary
{
    public static function line(?CreatorAudienceInsight $audience): ?string
    {
        if (! $audience) {
            return null;
        }

        $parts = [];

        if ($country = array_key_first($audience->top('countries', 1))) {
            $parts[] = $country.' '.round($audience->countries[$country]).'%';
        }

        if ($age = array_key_first($audience->top('age', 1))) {
            $parts[] = $age;
        }

        return $parts ? implode(' · ', $parts) : null;
    }
}
