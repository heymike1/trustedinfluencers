<?php

namespace App\Livewire\Admin;

use App\Actions\Admin\MergeCreators;
use App\Enums\CreatorStatus;
use App\Livewire\Concerns\HasNotice;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Surfaces likely duplicates: same name, or the same handle listed under different profiles
 * (usually the same person on two platforms who was added twice).
 */
#[Layout('components.layouts.app')]
class Duplicates extends Component
{
    use HasNotice;

    public function merge(int $duplicateId, int $targetId, MergeCreators $merge): void
    {
        try {
            $target = $merge->handle(Creator::findOrFail($duplicateId), Creator::findOrFail($targetId));
            $this->notify('success', "Merged into {$target->name}.");
        } catch (ValidationException $e) {
            $this->notify('error', collect($e->errors())->flatten()->first());
        }
    }

    /** @return Collection<int, array{reason: string, creators: Collection<int, Creator>}> */
    private function groups(): Collection
    {
        $creators = Creator::query()->where('status', '!=', CreatorStatus::Merged)->with(['socialAccounts', 'user'])->get();

        $byName = $creators->groupBy(fn (Creator $c) => mb_strtolower(preg_replace('/\s+/', ' ', trim($c->name))))
            ->filter(fn ($group) => $group->count() > 1)
            ->map(fn ($group, $name) => ['reason' => "Same name “{$group->first()->name}”", 'creators' => $group->values()]);

        $byHandle = CreatorSocialAccount::query()
            ->whereIn('creator_id', $creators->pluck('id'))
            ->get()
            ->groupBy('handle')
            ->filter(fn ($accounts) => $accounts->pluck('creator_id')->unique()->count() > 1)
            ->map(function ($accounts, $handle) use ($creators) {
                $ids = $accounts->pluck('creator_id')->unique();

                return ['reason' => "Same handle @{$handle} on different profiles", 'creators' => $creators->whereIn('id', $ids)->values()];
            });

        return $byName->values()->concat($byHandle->values());
    }

    public function render(): View
    {
        return view('livewire.admin.duplicates', ['groups' => $this->groups()])->title('Admin · Duplicates');
    }
}
