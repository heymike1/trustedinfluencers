<?php

namespace App\Livewire\Admin;

use App\Enums\CreatorStatus;
use App\Livewire\Concerns\HasNotice;
use App\Models\Creator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Creators extends Component
{
    use HasNotice;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** all | active | hidden | merged | claimed | unclaimed */
    #[Url(except: 'all')]
    public string $filter = 'all';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function hide(int $id): void
    {
        Creator::findOrFail($id)->update(['status' => CreatorStatus::Hidden]);
    }

    public function restore(int $id): void
    {
        Creator::findOrFail($id)->update(['status' => CreatorStatus::Active]);
    }

    public function destroy(int $id): void
    {
        $creator = Creator::findOrFail($id);

        if ($creator->isClaimed()) {
            $this->notify('error', 'Claimed profiles cannot be deleted. Hide the profile or release the claim first.');

            return;
        }

        $creator->delete();
        $this->notify('success', 'Profile deleted.');
    }

    public function render(): View
    {
        $creators = Creator::query()
            ->with(['category', 'socialAccounts', 'user'])
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('slug', 'like', $term)->orWhereHas('socialAccounts', fn ($a) => $a->where('handle', 'like', $term)));
            })
            ->when($this->filter === 'active', fn ($q) => $q->active())
            ->when($this->filter === 'hidden', fn ($q) => $q->where('status', CreatorStatus::Hidden))
            ->when($this->filter === 'merged', fn ($q) => $q->where('status', CreatorStatus::Merged))
            ->when($this->filter === 'claimed', fn ($q) => $q->claimed())
            ->when($this->filter === 'unclaimed', fn ($q) => $q->unclaimed())
            ->latest('id')
            ->paginate(30);

        return view('livewire.admin.creators', ['creators' => $creators])->title('Admin · Creators');
    }
}
