<?php

namespace App\Http\Controllers;

use App\Models\SponsorSlot;
use Illuminate\Http\RedirectResponse;

class SponsorClickController extends Controller
{
    /** Counts the click, then hands the visitor to the sponsor. */
    public function __invoke(SponsorSlot $slot): RedirectResponse
    {
        abort_unless($slot->isLive(), 404);

        $slot->increment('clicks');

        return redirect()->away($slot->url);
    }
}
