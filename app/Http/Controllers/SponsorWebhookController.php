<?php

namespace App\Http\Controllers;

use App\Actions\Sponsors\ConfirmPayment;
use App\Models\SponsorSlot;
use App\Sponsors\StripeCheckout;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Stripe telling us the money is in. This, not the buyer's browser, is what marks a booking paid.
 */
class SponsorWebhookController extends Controller
{
    public function __invoke(Request $request, ConfirmPayment $confirm): Response
    {
        $verified = StripeCheckout::verifySignature(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            (string) config('services.stripe.webhook_secret'),
        );

        abort_unless($verified, 400, 'Bad signature.');

        $event = $request->json()->all();

        if (($event['type'] ?? null) !== 'checkout.session.completed') {
            return response('Ignored.', 200);
        }

        $session = $event['data']['object'] ?? [];

        if (($session['payment_status'] ?? null) !== 'paid') {
            return response('Not paid.', 200);
        }

        $booking = SponsorSlot::find($session['metadata']['booking'] ?? $session['client_reference_id'] ?? null);

        if ($booking) {
            $confirm->handle($booking, $session['payment_intent'] ?? $session['id'] ?? null);
        }

        return response('Thanks.', 200);
    }
}
