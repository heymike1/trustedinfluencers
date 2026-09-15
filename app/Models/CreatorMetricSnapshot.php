<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorMetricSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_social_account_id',
        'social_content_id',
        'captured_at',
        'metrics',
        'raw_provider_data',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'metrics' => 'array',
            'raw_provider_data' => 'array',
        ];
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(CreatorSocialAccount::class, 'creator_social_account_id');
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(SocialContent::class, 'social_content_id');
    }
}
