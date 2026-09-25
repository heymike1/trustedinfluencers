<?php

namespace App\Livewire\Account;

use App\Enums\Platform;
use App\Models\Creator;
use App\Support\Format;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Where a creator lands after signing in: is the profile live, what does a brand see, and what
 * is still missing. Editing lives one tab away.
 */
#[Layout('components.layouts.app', ['band' => true])]
class Overview extends Component
{
    public ?Creator $creator = null;

    public function mount(): void
    {
        $this->creator = auth()->user()->creator;
    }

    /**
     * The four figures a brand judges the profile on, taken from the summary columns so they
     * read the same here as in the directory.
     *
     * @return list<array{label: string, value: string, hint: string}>
     */
    private function headline(): array
    {
        $creator = $this->creator;
        $account = $creator->primaryAccount();
        $reach = $account?->platform === Platform::Instagram;

        return array_values(array_filter([
            [
                'label' => $reach ? 'Median reach' : 'Median views',
                'value' => Format::compact($creator->median_views),
                'hint' => 'of '.Format::compact($creator->follower_count).' '.($account?->platform->audienceNoun() ?? 'followers'),
            ],
            $creator->average_view_percentage ? [
                'label' => 'Watched',
                'value' => Format::percent($creator->average_view_percentage, 0),
                'hint' => 'of each video',
            ] : null,
            [
                'label' => 'Engagement',
                'value' => Format::percent($creator->engagement_rate),
                'hint' => 'likes, comments, saves',
            ],
            [
                'label' => 'Posts / month',
                'value' => $creator->posts_per_month ? (string) round($creator->posts_per_month, 1) : '—',
                'hint' => 'how regularly you post',
            ],
        ]));
    }

    /** @return list<array{label: string, done: bool, href: string|null}> */
    private function checklist(): array
    {
        $accounts = $this->creator->socialAccounts;
        $missing = collect(Platform::enabled())->first(fn (Platform $p) => ! $accounts->contains('platform', $p));

        return [
            ['label' => 'Connect a platform', 'done' => $accounts->contains->isConnected(), 'href' => route('account.connections')],
            ['label' => 'Pick a category', 'done' => $this->creator->creator_category_id !== null, 'href' => route('account.profile')],
            ['label' => 'Write a bio', 'done' => filled($this->creator->bio), 'href' => route('account.profile')],
            ['label' => 'Turn on contact requests', 'done' => $this->creator->contact_enabled, 'href' => route('account.profile')],
            ['label' => $missing ? 'Add your '.$missing->label() : 'All platforms added', 'done' => $missing === null, 'href' => route('account.connections')],
        ];
    }

    public function render(): View
    {
        if (! $this->creator) {
            return view('livewire.account.overview', [
                'headline' => [], 'checklist' => [], 'accounts' => collect(), 'messages' => collect(), 'unread' => 0, 'nextRefresh' => null,
            ])->title('My profile');
        }

        $this->creator->load(['socialAccounts.performanceMetrics', 'category']);
        $accounts = $this->creator->socialAccounts;
        $lastSync = $accounts->filter->isConnected()->min('last_synced_at');

        return view('livewire.account.overview', [
            'headline' => $this->creator->has_verified_metrics ? $this->headline() : [],
            'checklist' => $this->checklist(),
            'accounts' => $accounts,
            'messages' => $this->creator->contactRequests()->limit(3)->get(),
            'unread' => $this->creator->contactRequests()->whereNull('read_at')->count(),
            'nextRefresh' => $lastSync?->addHours(config('social.sync.refresh_every_hours')),
            'missingPlatforms' => collect(Platform::enabled())->reject(fn (Platform $p) => $accounts->contains('platform', $p)),
        ])->title('My profile');
    }
}
