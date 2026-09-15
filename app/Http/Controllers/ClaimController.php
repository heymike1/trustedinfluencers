<?php

namespace App\Http\Controllers;

use App\Actions\Claims\StartClaim;
use App\Enums\CreatorStatus;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    public function show(Request $request, Creator $creator)
    {
        abort_if($creator->status !== CreatorStatus::Active, 404);

        $creator->load('socialAccounts');

        return view('creators.claim', [
            'creator' => $creator,
            'alreadyOwnsProfile' => $request->user()->creator()->exists(),
        ]);
    }

    public function start(Request $request, Creator $creator, CreatorSocialAccount $account, StartClaim $startClaim)
    {
        try {
            $url = $startClaim->handle($creator, $account, $request->user());
        } catch (ValidationException $e) {
            return redirect()->route('creators.claim', $creator)->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->away($url);
    }
}
