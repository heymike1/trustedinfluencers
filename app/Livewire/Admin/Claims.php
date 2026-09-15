<?php

namespace App\Livewire\Admin;

use App\Models\CreatorClaim;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Claims extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $claims = CreatorClaim::query()
            ->with(['creator', 'user'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(30);

        return view('livewire.admin.claims', ['claims' => $claims])->title('Admin · Claims');
    }
}
