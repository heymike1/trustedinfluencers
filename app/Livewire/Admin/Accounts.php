<?php

namespace App\Livewire\Admin;

use App\Actions\Creators\RemoveSocialAccount;
use App\Actions\Sync\DisconnectAccount;
use App\Actions\Sync\StartAccountSync;
use App\Enums\ConnectionStatus;
use App\Enums\Platform;
use App\Livewire\Concerns\HasNotice;
use App\Models\CreatorSocialAccount;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Every social account in one list: what state it is in, when it last synced and what went wrong.
 */
#[Layout('components.layouts.admin')]
class Accounts extends Component
{
    use HasNotice;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $platform = '';

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function sync(int $id, StartAccountSync $startSync): void
    {
        $account = CreatorSocialAccount::findOrFail($id);

        if (! $account->isConnected()) {
            $this->notify('error', 'Only connected accounts can be synced.');

            return;
        }

        $startSync->handle($account);
        $this->notify('success', 'Sync queued.');
    }

    public function disconnect(int $id, DisconnectAccount $disconnect): void
    {
        $disconnect->handle(CreatorSocialAccount::findOrFail($id));
        $this->notify('success', 'Disconnected. Imported data deleted.');
    }

    public function destroy(int $id, RemoveSocialAccount $remove): void
    {
        $remove->handle(CreatorSocialAccount::findOrFail($id));
        $this->notify('success', 'Account removed from its profile.');
    }

    public function render(): View
    {
        $accounts = CreatorSocialAccount::query()
            ->with('creator')
            ->withCount('contents')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('handle', 'like', "%{$this->search}%")->orWhereHas('creator', fn ($c) => $c->where('name', 'like', "%{$this->search}%"))))
            ->when($this->status !== '', fn ($q) => $q->where('connection_status', $this->status))
            ->when($this->platform !== '', fn ($q) => $q->where('platform', $this->platform))
            ->latest('updated_at')
            ->paginate(30);

        return view('livewire.admin.accounts', [
            'accounts' => $accounts,
            'statuses' => ConnectionStatus::cases(),
            'platforms' => Platform::cases(),
        ])->title('Social accounts');
    }
}
