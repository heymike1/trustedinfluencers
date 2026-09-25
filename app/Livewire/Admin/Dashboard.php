<?php

namespace App\Livewire\Admin;

use App\Enums\ClaimStatus;
use App\Enums\ConnectionStatus;
use App\Enums\CreatorStatus;
use App\Models\Creator;
use App\Models\CreatorClaim;
use App\Models\CreatorContactRequest;
use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render(): View
    {
        $failed = CreatorSocialAccount::whereIn('connection_status', [ConnectionStatus::SyncFailed, ConnectionStatus::NeedsReconnection]);

        return view('livewire.admin.dashboard', [
            'stats' => [
                ['Creators', Creator::count(), Creator::active()->count().' listed', route('admin.creators')],
                ['Verified', Creator::where('has_verified_metrics', true)->count(), Creator::claimed()->count().' claimed', route('admin.creators', ['filter' => 'claimed'])],
                ['Accounts', CreatorSocialAccount::count(), CreatorSocialAccount::whereIn('connection_status', [ConnectionStatus::Connected, ConnectionStatus::Importing])->count().' connected', route('admin.accounts')],
                ['Content', SocialContent::count(), 'imported items', route('admin.content')],
                ['Users', User::count(), User::where('is_admin', true)->count().' admins', route('admin.users')],
                ['Messages', CreatorContactRequest::count(), CreatorContactRequest::whereNull('read_at')->count().' unread', route('admin.requests')],
            ],
            'needsAttention' => (clone $failed)->with('creator')->latest('updated_at')->limit(6)->get(),
            'attentionCount' => (clone $failed)->count(),
            'recentClaims' => CreatorClaim::with(['creator', 'user'])->latest()->limit(6)->get(),
            'pendingClaims' => CreatorClaim::where('status', ClaimStatus::Pending)->count(),
            'recentCreators' => Creator::with('category')->latest()->limit(6)->get(),
            'hiddenCount' => Creator::where('status', CreatorStatus::Hidden)->count(),
            'unlistedCount' => Creator::where('is_listed', false)->count(),
            'staleCount' => Creator::where('has_verified_metrics', true)
                ->where(fn ($q) => $q->whereNull('metrics_synced_at')->orWhere('metrics_synced_at', '<', now()->subDays(config('social.sync.stale_after_days', 7))))
                ->count(),
            'failedJobs' => DB::table('failed_jobs')->count(),
        ])->title('Overview');
    }
}
