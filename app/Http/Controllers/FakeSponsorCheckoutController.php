<?php

namespace App\Http\Controllers;

use App\Actions\Sponsors\ConfirmPayment;
use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Stands in for the payment provider while there is none. Only reachable with the fake checkout
 * driver, which is never on in production.
 */
class FakeSponsorCheckoutController extends Controller
{
    public function show(SponsorSlot $booking): View
    {
        abort_unless(config('social.sponsors.checkout') === 'fake', 404);

        return view('sponsor-fake-checkout', [
            'booking' => $booking,
            'price' => Sponsorship::money($booking->amount),
        ]);
    }

    public function pay(SponsorSlot $booking, ConfirmPayment $confirm): RedirectResponse
    {
        abort_unless(config('social.sponsors.checkout') === 'fake', 404);

        $confirm->handle($booking, 'fake_payment_'.$booking->id);

        return redirect()->route('sponsor.card', $booking->fresh()->freshToken());
    }
}
