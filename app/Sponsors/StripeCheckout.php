<?php

namespace App\Sponsors;

use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Stripe Checkout over the plain HTTP API, so there is no SDK to keep up to date. Add
 * STRIPE_SECRET and STRIPE_WEBHOOK_SECRET to .env and this is all there is to it.
 */
class StripeCheckout implements CheckoutGateway
{
    private const API = 'https://api.stripe.com/v1';

    public function start(SponsorSlot $booking, string $returnUrl, string $cancelUrl): string
    {
        $response = $this->request()->asForm()->post(self::API.'/checkout/sessions', [
            'mode' => 'payment',
            'success_url' => $returnUrl.(str_contains($returnUrl, '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'customer_email' => $booking->buyer_email,
            'client_reference_id' => (string) $booking->id,
            'metadata' => ['booking' => (string) $booking->id, 'spot' => $booking->spotKey() ?? 'queue'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $booking->currency ?? Sponsorship::currency(),
                    'unit_amount' => (int) $booking->amount * 100,
                    'product_data' => [
                        'name' => config('app.name').' · sponsor spot',
                        'description' => 'One card in the rails for '.Sponsorship::days().' days',
                    ],
                ],
            ]],
        ]);

        if ($response->failed()) {
            Log::error('Stripe checkout failed', ['booking' => $booking->id, 'body' => $response->json()]);

            throw new RuntimeException('Could not start the checkout.');
        }

        $booking->update(['checkout_session_id' => $response->json('id')]);

        return $response->json('url');
    }

    public function isPaid(SponsorSlot $booking): ?bool
    {
        if (! $booking->checkout_session_id) {
            return null;
        }

        $response = $this->request()->get(self::API.'/checkout/sessions/'.$booking->checkout_session_id);

        if ($response->failed()) {
            return null;
        }

        return $response->json('payment_status') === 'paid';
    }

    /** Stripe signs every webhook; an unsigned or stale one is not from Stripe. */
    public static function verifySignature(string $payload, ?string $header, string $secret, int $toleranceSeconds = 300): bool
    {
        if (! $header || ! $secret) {
            return false;
        }

        $parts = collect(explode(',', $header))
            ->mapWithKeys(function (string $part) {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

                return [$key => $value];
            });

        $timestamp = (int) $parts->get('t');

        if ($timestamp <= 0 || abs(time() - $timestamp) > $toleranceSeconds) {
            return false;
        }

        return hash_equals(
            hash_hmac('sha256', $timestamp.'.'.$payload, $secret),
            (string) $parts->get('v1'),
        );
    }

    private function request()
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('No Stripe secret configured.');
        }

        return Http::withToken($secret)->timeout(20);
    }
}
