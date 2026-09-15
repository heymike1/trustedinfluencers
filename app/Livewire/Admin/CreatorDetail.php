<?php

namespace App\Livewire\Admin;

use App\Actions\Admin\MergeCreators;
use App\Actions\Sync\DisconnectAccount;
use App\Actions\Sync\StartAccountSync;
use App\Enums\ClaimStatus;
use App\Enums\ConnectionStatus;
use App\Enums\CreatorStatus;
use App\Livewire\Concerns\HasNotice;
use App\Models\Creator;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreatorDetail extends Component
{
    use HasNotice;

    public Creator $creator;

    public string $mergeInto = '';

    public function mount(Creator $creator): void
    {
        $this->creator = $creator;
    }

    public function sync(int $accountId, StartAccountSync $startSync): void
    {
        $account = $this->creator->socialAccounts()->findOrFail($accountId);

        if (! $account->isConnected()) {
            $this->notify('error', 'That account is not connected; only the creator can connect it.');

            return;
        }

        $startSync->handle($account);
        $this->notify('success', 'Sync queued for '.$account->platform->label().'.');
    }

    public function toggleHidden(): void
    {
        $this->creator->update([
            'status' => $this->creator->status === CreatorStatus::Hidden ? CreatorStatus::Active : CreatorStatus::Hidden,
        ]);
    }

    /**
     * Dispute resolution: unlink the current owner so the real creator can claim the profile.
     * Connected accounts are disconnected (credentials destroyed, verified data deleted).
     */
    public function releaseClaim(DisconnectAccount $disconnect): void
    {
        foreach ($this->creator->socialAccounts as $account) {
            if ($account->isConnected() || $account->connection_status === ConnectionStatus::NeedsReconnection) {
                $disconnect->handle($account);
            }
        }

        $this->creator->claims()->where('status', ClaimStatus::Verified)->update(['status' => ClaimStatus::Failed, 'failure_reason' => 'Released by admin']);
        $this->creator->forceFill(['user_id' => null, 'claimed_at' => null, 'has_verified_metrics' => false])->save();

        $this->notify('success', 'Claim released. The profile is unclaimed again.');
    }

    public function merge(MergeCreators $merge): void
    {
        $target = Creator::where('slug', trim($this->mergeInto))->orWhere('id', (int) $this->mergeInto)->first();

        if (! $target) {
            $this->addError('mergeInto', 'No creator found with that slug or ID.');

            return;
        }

        try {
            $merge->handle($this->creator, $target);
        } catch (ValidationException $e) {
            $this->addError('mergeInto', collect($e->errors())->flatten()->first());

            return;
        }

        session()->flash('success', "Merged into {$target->name}.");
        $this->redirectRoute('admin.creators.show', $target);
    }

    public function render(): View
    {
        $this->creator->load(['socialAccounts.performanceMetrics', 'claims.user', 'user', 'category', 'contactRequests']);

        return view('livewire.admin.creator-detail', [
            'state' => $this->creator->profileState(),
        ])->title('Admin · '.$this->creator->name);
    }
}
