<?php

namespace App\Http\Controllers;

use App\Actions\Sponsors\ConfirmPayment;
use App\Actions\Sponsors\StartBooking;
use App\Models\SponsorSlot;
use App\Sponsors\CheckoutGateway;
use App\Support\Sponsorship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SponsorCheckoutController extends Controller
{
    /** Holds the spot and sends the buyer off to pay. */
    public function start(Request $request, StartBooking $startBooking, CheckoutGateway $gateway): RedirectResponse
    {
        abort_unless(Sponsorship::isForSale(), 404);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'spot' => ['nullable', 'string', 'max:10'],
        ]);

        $booking = $startBooking->handle($data['email'], $data['spot'] ?? null);

        try {
            $url = $gateway->start(
                $booking,
                route('sponsor.return', $booking),
                route('sponsor', ['spot' => $booking->spotKey()]),
            );
        } catch (RuntimeException $e) {
            report($e);
            $booking->forceFill(['status' => SponsorSlot::CANCELLED, 'position' => null])->save();

            return back()->with('error', 'The checkout would not start. Nothing was charged; try again or email '.Sponsorship::contact().'.');
        }

        return redirect()->away($url);
    }

    /**
     * Where the payment provider drops the buyer off. The webhook is what we trust, but it can
     * arrive after the buyer does, so we ask once here as well.
     */
    public function return(SponsorSlot $booking, ConfirmPayment $confirm, CheckoutGateway $gateway): RedirectResponse
    {
        if ($booking->paid_at === null && $gateway->isPaid($booking) === true) {
            $confirm->handle($booking);
        }

        $booking->refresh();

        if ($booking->paid_at === null) {
            // Paid but not confirmed yet: the page they land on says so and picks it up.
            return redirect()->route('sponsor.pending', $booking);
        }

        return redirect()->route('sponsor.card', $booking->freshToken());
    }

    /** A holding page for the seconds between paying and the webhook landing. */
    public function pending(SponsorSlot $booking, ConfirmPayment $confirm, CheckoutGateway $gateway)
    {
        if ($booking->paid_at === null && $gateway->isPaid($booking) === true) {
            $confirm->handle($booking);
            $booking->refresh();
        }

        if ($booking->paid_at !== null) {
            return redirect()->route('sponsor.card', $booking->freshToken());
        }

        return view('sponsor-pending', ['booking' => $booking]);
    }
}
