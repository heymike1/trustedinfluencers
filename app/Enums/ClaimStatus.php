<?php

namespace App\Enums;

enum ClaimStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Failed = 'failed';
}
