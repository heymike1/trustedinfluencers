<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use App\Enums\Platform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'user_id',
        'creator_social_account_id',
        'platform',
        'status',
        'expected_provider_account_id',
        'returned_provider_account_id',
        'returned_handle',
        'failure_reason',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'status' => ClaimStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(CreatorSocialAccount::class, 'creator_social_account_id');
    }
}
