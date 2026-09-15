<?php

namespace App\Http\Controllers;

use App\Actions\Claims\CompleteClaim;
use App\Actions\Claims\OwnershipMismatchException;
use App\Actions\Sync\ConnectOwnedAccount;
use App\Enums\Platform;
use App\Models\CreatorClaim;
use App\Models\CreatorSocialAccount;
use App\Social\ConnectorManager;
use App\Social\Exceptions\ConnectorException;
use App\Social\OAuth\OAuthSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Provider callback. Exchanges the code, asks the provider who signed in, then hands off to the
 * action matching the intent stored when the flow started. Sync work is queued, never done here.
 */
class OAuthController extends Controller
{
    public function callback(
        Request $request,
        string $platform,
        ConnectorManager $connectors,
        OAuthSession $session,
        CompleteClaim $completeClaim,
        ConnectOwnedAccount $connectOwned,
    ): RedirectResponse {
        $platform = Platform::tryFrom($platform) ?? abort(404);

        $pending = $session->complete($platform, $request->query('state'));

        if ($pending === null) {
            return redirect()->route('account')->with('error', 'That sign-in took too long or didn\'t come from us. Please try again.');
        }

        $payload = $pending['payload'];
        $returnTo = $this->returnRouteFor($payload);

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect($returnTo)->with('error', $platform->label().' sign-in was cancelled.');
        }

        $connector = $connectors->for($platform);

        try {
            $tokens = $connector->exchangeCode($request->query('code'), $pending['request']);
            $identity = $connector->identity($tokens);
        } catch (ConnectorException $e) {
            report($e);

            return redirect($returnTo)->with('error', 'The '.$platform->label().' sign-in didn\'t go through. '.$e->getMessage());
        }

        try {
            if ($payload['intent'] === 'claim') {
                $claim = CreatorClaim::findOrFail($payload['claim_id']);
                $completeClaim->handle($claim, $request->user(), $identity, $tokens);

                return redirect()->route('creators.show', $claim->creator)
                    ->with('success', 'Confirmed through '.$platform->label().'. This profile is yours. We\'re pulling in your numbers now.');
            }

            $account = CreatorSocialAccount::findOrFail($payload['account_id']);
            $connectOwned->handle($account, $request->user(), $identity, $tokens);

            return redirect()->route('account.connections')
                ->with('success', $platform->label().' connected. We\'re pulling in your numbers now.');
        } catch (OwnershipMismatchException $e) {
            return redirect($returnTo)->with('error', $e->getMessage());
        }
    }

    private function returnRouteFor(array $payload): string
    {
        if (($payload['intent'] ?? null) === 'claim' && isset($payload['claim_id'])) {
            $claim = CreatorClaim::with('creator')->find($payload['claim_id']);

            if ($claim?->creator) {
                return route('creators.claim', $claim->creator);
            }
        }

        return route('account.connections');
    }
}
