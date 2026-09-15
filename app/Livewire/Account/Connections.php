<?php

namespace App\Livewire\Account;

use App\Actions\Creators\AddSocialAccountToCreator;
use App\Actions\Creators\DuplicateCreatorException;
use App\Actions\Creators\RemoveSocialAccount;
use App\Actions\Sync\DisconnectAccount;
use App\Actions\Sync\RequestManualSync;
use App\Enums\Platform;
use App\Livewire\Concerns\HasNotice;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['band' => true])]
class Connections extends Component
{
    use HasNotice;

    public ?Creator $creator = null;

    public string $newPlatform = '';

    public string $newHandle = '';

    public function mount(): void
    {
        $this->creator = auth()->user()->creator;
    }

    public function syncNow(int $accountId, RequestManualSync $requestSync): void
    {
        $account = $this->ownedAccount($accountId);

        try {
            $requestSync->handle($account);
            $this->notify('success', $account->platform->label().' sync started. Your numbers will update in a few minutes.');
        } catch (ValidationException $e) {
            $this->notify('error', collect($e->errors())->flatten()->first());
        }
    }

    public function disconnect(int $accountId, DisconnectAccount $disconnect): void
    {
        $account = $this->ownedAccount($accountId);
        $disconnect->handle($account);

        $this->notify('success', $account->platform->label().' disconnected. The verified numbers and stats for this account are gone.');
    }

    public function remove(int $accountId, RemoveSocialAccount $remove): void
    {
        $account = $this->ownedAccount($accountId);
        $unlisted = $remove->handle($account);

        $this->notify('success', $account->platform->label().' removed from your profile.'.($unlisted ? ' Your profile is now hidden from the directory because it has no platforms left. Add one and turn the listing back on under Profile whenever you like.' : ''));
    }

    public function addAccount(AddSocialAccountToCreator $add): void
    {
        abort_unless($this->creator?->isOwnedBy(auth()->user()), 403);

        $data = $this->validate([
            'newPlatform' => ['required', Rule::enum(Platform::class)],
            'newHandle' => ['required', 'string', 'max:255'],
        ]);

        try {
            $add->handle($this->creator, Platform::from($data['newPlatform']), $data['newHandle']);
        } catch (DuplicateCreatorException $e) {
            $this->addError('newHandle', $e->getMessage());

            return;
        } catch (ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                $this->addError(match ($key) {
                    'handle' => 'newHandle',
                    'platform' => 'newPlatform',
                    default => $key,
                }, $messages[0]);
            }

            return;
        }

        $this->reset('newPlatform', 'newHandle');
        $this->notify('success', 'Added. Connect it to confirm it\'s yours and pull in your numbers.');
    }

    private function ownedAccount(int $accountId): CreatorSocialAccount
    {
        abort_unless($this->creator?->isOwnedBy(auth()->user()), 403);

        return $this->creator->socialAccounts()->findOrFail($accountId);
    }

    public function render(): View
    {
        $accounts = $this->creator?->socialAccounts()->with('performanceMetrics')->get() ?? collect();
        $importing = $accounts->contains(fn (CreatorSocialAccount $a) => $a->isImporting());

        $lastSync = $accounts->filter->isConnected()->min('last_synced_at');

        return view('livewire.account.connections', [
            'accounts' => $accounts,
            'importing' => $importing,
            'platforms' => collect(Platform::cases())->reject(fn (Platform $p) => $accounts->contains('platform', $p)),
            'cooldown' => config('social.sync.manual_cooldown_minutes'),
            'nextRefresh' => $lastSync?->addHours(config('social.sync.refresh_every_hours')),
        ])->title('Connected accounts');
    }
}
