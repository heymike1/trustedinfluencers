<?php

namespace App\Livewire;

use App\Enums\ContentType;
use App\Enums\MetricWindow;
use App\Enums\Platform;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use App\View\ProfileInsights;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The analytics half of a profile: one connected account at a time, with window and
 * content-type switches. Polls while an import is running.
 */
class CreatorProfile extends Component
{
    public Creator $creator;

    #[Url(except: '')]
    public string $platform = '';

    #[Url(except: 'last_20')]
    public string $window = 'last_20';

    #[Url(except: '')]
    public string $type = '';

    public function mount(Creator $creator): void
    {
        $this->creator = $creator;
    }

    public function updatedPlatform(): void
    {
        $this->type = '';
    }

    public function render(): View
    {
        $this->creator->load(['category', 'socialAccounts.performanceMetrics', 'socialAccounts.audience']);

        $accounts = $this->creator->socialAccounts;
        $account = $accounts->firstWhere('platform', Platform::tryFrom($this->platform)) ?? $this->creator->primaryAccount();
        $window = MetricWindow::tryFrom($this->window) ?? MetricWindow::default();
        $types = $account ? $account->platform->contentTypes() : [];
        $type = ContentType::tryFrom($this->type);
        $type = $type && in_array($type, $types, true) ? $type : ($types[0] ?? null);

        $verified = $account?->hasVerifiedMetrics() ?? false;
        $performance = $verified && $type ? $account->performanceFor($type, $window) : null;

        $contents = $verified && $type
            ? $account->contents()->where('content_type', $type)->whereNotNull('metrics')->orderByDesc('published_at')->limit($window->itemLimit() ?? 30)->get()
            : collect();

        return view('livewire.creator-profile', [
            'accounts' => $accounts,
            'account' => $account,
            'activeWindow' => $window,
            'windows' => MetricWindow::cases(),
            'types' => $types,
            'activeType' => $type,
            'verified' => $verified,
            'insights' => $performance ? new ProfileInsights($account, $performance, $type, $contents, $account->audience) : null,
            'importing' => $accounts->contains(fn (CreatorSocialAccount $a) => $a->isImporting()),
            'state' => $this->creator->profileState(),
        ]);
    }
}
