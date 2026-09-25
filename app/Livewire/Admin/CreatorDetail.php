<?php

namespace App\Livewire\Admin;

use App\Actions\Admin\MergeCreators;
use App\Actions\Creators\RemoveSocialAccount;
use App\Actions\Sync\DisconnectAccount;
use App\Actions\Sync\RefreshCreatorSummary;
use App\Actions\Sync\StartAccountSync;
use App\Enums\ClaimStatus;
use App\Enums\ConnectionStatus;
use App\Enums\CreatorStatus;
use App\Livewire\Concerns\HasNotice;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Everything about one creator on a single screen: the fields an admin may edit, the accounts
 * with their sync state, and the rows that hang off them.
 */
#[Layout('components.layouts.admin')]
class CreatorDetail extends Component
{
    use HasNotice;

    public Creator $creator;

    public array $form = [];

    public string $mergeInto = '';

    public string $transferTo = '';

    public function mount(Creator $creator): void
    {
        $this->creator = $creator;
        $this->fillForm();
    }

    private function fillForm(): void
    {
        $this->form = [
            'name' => $this->creator->name,
            'slug' => $this->creator->slug,
            'creator_category_id' => (string) ($this->creator->creator_category_id ?? ''),
            'bio' => $this->creator->bio ?? '',
            'avatar_url' => $this->creator->avatar_url ?? '',
            'location' => $this->creator->location ?? '',
            'website' => $this->creator->website ?? '',
            'contact_email' => $this->creator->contact_email ?? '',
            'contact_enabled' => $this->creator->contact_enabled,
            'is_listed' => $this->creator->is_listed,
            'status' => $this->creator->status->value,
        ];
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.name' => ['required', 'string', 'max:80'],
            'form.slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('creators', 'slug')->ignore($this->creator->id)],
            'form.creator_category_id' => ['nullable', Rule::exists('creator_categories', 'id')],
            'form.bio' => ['nullable', 'string', 'max:500'],
            'form.avatar_url' => ['nullable', 'url', 'max:2048'],
            'form.location' => ['nullable', 'string', 'max:80'],
            'form.website' => ['nullable', 'url', 'max:2048'],
            'form.contact_email' => ['nullable', 'email', 'max:255'],
            'form.contact_enabled' => ['boolean'],
            'form.is_listed' => ['boolean'],
            'form.status' => ['required', Rule::enum(CreatorStatus::class)],
        ])['form'];

        $this->creator->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'creator_category_id' => $data['creator_category_id'] !== '' ? (int) $data['creator_category_id'] : null,
            'bio' => $data['bio'] ?: null,
            'avatar_url' => $data['avatar_url'] ?: null,
            'location' => $data['location'] ?: null,
            'website' => $data['website'] ?: null,
            'contact_email' => $data['contact_email'] ?: null,
            'contact_enabled' => $data['contact_enabled'],
            'is_listed' => $data['is_listed'],
            'status' => $data['status'],
        ]);

        $this->notify('success', 'Profile saved.');
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

    public function disconnect(int $accountId, DisconnectAccount $disconnect): void
    {
        $account = $this->creator->socialAccounts()->findOrFail($accountId);
        $disconnect->handle($account);

        $this->notify('success', $account->platform->label().' disconnected. Imported data deleted.');
    }

    public function removeAccount(int $accountId, RemoveSocialAccount $remove): void
    {
        $account = $this->creator->socialAccounts()->findOrFail($accountId);
        $remove->handle($account);

        $this->notify('success', $account->platform->label().' removed from the profile.');
    }

    public function recalculate(RefreshCreatorSummary $refresh): void
    {
        $refresh->handle($this->creator);
        $this->notify('success', 'Summary columns rebuilt from the stored metrics.');
    }

    /** Dispute resolution: unlink the owner so the real creator can claim the profile. */
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

    /** Hand the profile to another login without going through OAuth. */
    public function transfer(): void
    {
        $user = User::where('email', trim($this->transferTo))->orWhere('id', (int) $this->transferTo)->first();

        if (! $user) {
            $this->addError('transferTo', 'No user with that email or ID.');

            return;
        }

        if ($user->creator()->whereKeyNot($this->creator->id)->exists()) {
            $this->addError('transferTo', "{$user->email} already owns another profile.");

            return;
        }

        $this->creator->forceFill(['user_id' => $user->id, 'claimed_at' => $this->creator->claimed_at ?? now()])->save();
        $this->transferTo = '';

        $this->notify('success', "Profile now belongs to {$user->email}.");
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

    public function destroy(): void
    {
        $name = $this->creator->name;
        $this->creator->delete();

        session()->flash('success', "{$name} deleted with everything under it.");
        $this->redirectRoute('admin.creators');
    }

    public function render(): View
    {
        $this->creator->load([
            'socialAccounts.performanceMetrics', 'socialAccounts.audience', 'claims.user', 'user', 'category',
            'contactRequests', 'mergedInto',
        ]);

        return view('livewire.admin.creator-detail', [
            'state' => $this->creator->profileState(),
            'categories' => CreatorCategory::orderBy('sort_order')->get(),
            'statuses' => CreatorStatus::cases(),
        ])->title($this->creator->name);
    }
}
