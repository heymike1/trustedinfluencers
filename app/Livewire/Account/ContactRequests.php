<?php

namespace App\Livewire\Account;

use App\Models\Creator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ContactRequests extends Component
{
    use WithPagination;

    public ?Creator $creator = null;

    public function mount(): void
    {
        $this->creator = auth()->user()->creator;
    }

    public function markRead(int $id): void
    {
        abort_unless($this->creator?->isOwnedBy(auth()->user()), 403);

        $this->creator->contactRequests()->whereNull('read_at')->whereKey($id)->update(['read_at' => now()]);
    }

    public function render(): View
    {
        return view('livewire.account.contact-requests', [
            'requests' => $this->creator?->contactRequests()->paginate(15),
        ])->title('Contact requests');
    }
}
