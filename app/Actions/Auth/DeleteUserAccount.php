<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The self-service version of the deletion the privacy policy promises: the claimed profile with
 * everything under it (platforms, tokens, imported data, contact requests), then the user itself.
 */
class DeleteUserAccount
{
    public function handle(User $user): void
    {
        if ($user->isAdmin()) {
            throw ValidationException::withMessages(['account' => 'Admin accounts can’t be deleted here. Ask another admin to remove the admin flag first.']);
        }

        DB::transaction(function () use ($user) {
            $user->creator?->delete();
            $user->delete();
        });
    }
}
