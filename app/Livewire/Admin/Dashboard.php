<?php

namespace App\Livewire\Admin;

use App\Enums\ConnectionStatus;
use App\Models\Creator;
use App\Models\CreatorClaim;
use App\Models\CreatorSocialAccount;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.admin.dashboard', [
            'counts' => [
                'Creators' => Creator::active()->count(),
                'Claimed' => Creator::active()->claimed()->count(),
                'Verified metrics' => Creator::active()->where('has_verified_metrics', true)->count(),
                'Users' => User::count(),
                'Failed syncs' => CreatorSocialAccount::where('connection_status', ConnectionStatus::SyncFailed)->count(),
                'Needs reconnection' => CreatorSocialAccount::where('connection_status', ConnectionStatus::NeedsReconnection)->count(),
            ],
            'recentClaims' => CreatorClaim::with(['creator', 'user'])->latest()->limit(8)->get(),
            'failedAccounts' => CreatorSocialAccount::with('creator')->whereIn('connection_status', [ConnectionStatus::SyncFailed, ConnectionStatus::NeedsReconnection])->latest('updated_at')->limit(8)->get(),
        ])->title('Admin');
    }
}
