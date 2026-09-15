<?php

namespace App\Livewire\Admin;

use App\Actions\Sync\StartAccountSync;
use App\Enums\ConnectionStatus;
use App\Livewire\Concerns\HasNotice;
use App\Models\CreatorSocialAccount;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Connections extends Component
{
    use HasNotice;
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    public function updated(): void
    {
        $this->resetPage();
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

    public function render(): View
    {
        $accounts = CreatorSocialAccount::query()
            ->with('creator')
            ->when($this->status !== '', fn ($q) => $q->where('connection_status', $this->status))
            ->when($this->status === '', fn ($q) => $q->where('connection_status', '!=', ConnectionStatus::Unconnected))
            ->latest('updated_at')
            ->paginate(30);

        return view('livewire.admin.connections', [
            'accounts' => $accounts,
            'statuses' => ConnectionStatus::cases(),
        ])->title('Admin · Connections');
    }
}
