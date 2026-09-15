<?php

namespace App\Livewire\Account;

use App\Models\Creator;
use App\Models\CreatorContactRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The creator's inbox: a list on the left, the open request on the right. Opening one marks it read.
 */
#[Layout('components.layouts.app', ['band' => true])]
class ContactRequests extends Component
{
    use WithPagination;

    public ?Creator $creator = null;

    #[Url(as: 'r', except: null)]
    public ?int $selected = null;

    /** '' | unread */
    #[Url(except: '')]
    public string $filter = '';

    public function mount(): void
    {
        $this->creator = auth()->user()->creator;
    }

    public function open(int $id): void
    {
        $request = $this->owned($id);

        $this->selected = $request->id;

        if ($request->read_at === null) {
            $request->update(['read_at' => now()]);
        }
    }

    public function markUnread(int $id): void
    {
        $this->owned($id)->update(['read_at' => null]);
        $this->selected = null;
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    private function owned(int $id): CreatorContactRequest
    {
        abort_unless($this->creator?->isOwnedBy(auth()->user()), 403);

        return $this->creator->contactRequests()->findOrFail($id);
    }

    public function render(): View
    {
        $requests = $this->creator?->contactRequests()
            ->when($this->filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->paginate(15);

        // Default to the newest request so the right-hand pane is never empty when there is something to read.
        $selected = $this->creator && $this->selected ? $this->creator->contactRequests()->find($this->selected) : null;
        $selected ??= $requests?->first();

        return view('livewire.account.contact-requests', [
            'requests' => $requests,
            'open' => $selected,
            'unread' => $this->creator?->contactRequests()->whereNull('read_at')->count() ?? 0,
        ])->title('Contact requests');
    }
}
