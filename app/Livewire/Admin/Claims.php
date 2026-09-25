<?php

namespace App\Livewire\Admin;

use App\Enums\ClaimStatus;
use App\Livewire\Concerns\HasNotice;
use App\Models\CreatorClaim;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Claims extends Component
{
    use HasNotice;
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function destroy(int $id): void
    {
        CreatorClaim::findOrFail($id)->delete();
        $this->notify('success', 'Claim record deleted.');
    }

    public function render(): View
    {
        $claims = CreatorClaim::query()
            ->with(['creator', 'user'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', fn ($q) => $q->whereHas('creator', fn ($c) => $c->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(30);

        return view('livewire.admin.claims', [
            'claims' => $claims,
            'statuses' => ClaimStatus::cases(),
        ])->title('Claims');
    }
}
