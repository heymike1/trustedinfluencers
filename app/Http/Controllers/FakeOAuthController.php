<?php

namespace App\Http\Controllers;

use App\Enums\Platform;
use App\Social\ConnectorManager;
use App\Social\Fake\FakeConnector;
use App\Social\Login\FakeGoogleLoginProvider;
use Illuminate\Http\Request;

/**
 * Stand-in for the provider's consent screen when social.driver = fake.
 * Lets the developer choose which handle "signs in", which is enough to exercise both
 * successful ownership verification and mismatches.
 */
class FakeOAuthController extends Controller
{
    public function show(Request $request, string $platform, ConnectorManager $connectors)
    {
        abort_unless($connectors->usingFakeDriver(), 404);
        $platform = Platform::tryFrom($platform) ?? abort(404);

        return view('oauth.fake-authorize', [
            'platform' => $platform,
            'state' => $request->query('state'),
            'hint' => $request->query('hint'),
        ]);
    }

    public function authorize(Request $request, string $platform, ConnectorManager $connectors)
    {
        abort_unless($connectors->usingFakeDriver(), 404);
        $platform = Platform::tryFrom($platform) ?? abort(404);

        $data = $request->validate([
            'state' => ['required', 'string'],
            'handle' => ['required', 'string', 'max:60'],
            'decision' => ['required', 'in:allow,deny'],
        ]);

        $query = ['state' => $data['state']];

        if ($data['decision'] === 'deny') {
            $query['error'] = 'access_denied';
        } else {
            $query['code'] = FakeConnector::codeFor(ltrim($data['handle'], '@'));
        }

        return redirect()->route('oauth.callback', ['platform' => $platform->value] + $query);
    }

    public function showGoogle(Request $request, ConnectorManager $connectors)
    {
        abort_unless($connectors->usingFakeDriver(), 404);

        return view('oauth.fake-google', ['state' => $request->query('state'), 'hint' => $request->query('hint')]);
    }

    public function authorizeGoogle(Request $request, ConnectorManager $connectors)
    {
        abort_unless($connectors->usingFakeDriver(), 404);

        $data = $request->validate([
            'state' => ['required', 'string'],
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:80'],
            'decision' => ['required', 'in:allow,deny'],
        ]);

        $query = ['state' => $data['state']];
        $query += $data['decision'] === 'deny'
            ? ['error' => 'access_denied']
            : ['code' => FakeGoogleLoginProvider::codeFor($data['email'], $data['name'] ?? '')];

        return redirect()->route('login.google.callback', $query);
    }
}
