<?php

namespace App\Http\Controllers;

use App\Actions\Sync\StartAccountConnection;
use App\Models\CreatorSocialAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountConnectionController extends Controller
{
    public function connect(Request $request, CreatorSocialAccount $account, StartAccountConnection $start)
    {
        try {
            $url = $start->handle($account, $request->user());
        } catch (ValidationException $e) {
            return redirect()->route('account.connections')->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->away($url);
    }
}
