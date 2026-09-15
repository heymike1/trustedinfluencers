<?php

namespace App\Enums;

enum CreatorStatus: string
{
    case Active = 'active';
    case Hidden = 'hidden';
    case Merged = 'merged';
}
