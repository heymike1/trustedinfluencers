<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorAudienceInsight extends Model
{
    protected $fillable = [
        'creator_social_account_id',
        'age',
        'gender',
        'countries',
        'cities',
        'devices',
        'follower_type',
        'account_metrics',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'array',
            'gender' => 'array',
            'countries' => 'array',
            'cities' => 'array',
            'devices' => 'array',
            'follower_type' => 'array',
            'account_metrics' => 'array',
            'captured_at' => 'datetime',
        ];
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(CreatorSocialAccount::class, 'creator_social_account_id');
    }

    /** Top N entries of a breakdown, highest first. */
    public function top(string $breakdown, int $limit = 5): array
    {
        $values = $this->{$breakdown} ?? [];
        arsort($values);

        return array_slice($values, 0, $limit, true);
    }

    public function metric(string $key): int|float|null
    {
        return $this->account_metrics[$key] ?? null;
    }
}
