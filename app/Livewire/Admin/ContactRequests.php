<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\HasNotice;
use App\Models\CreatorContactRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ContactRequests extends Component
{
    use HasNotice;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** '' | unread */
    #[Url(except: '')]
    public string $filter = '';

    public ?int $open = null;

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function show(int $id): void
    {
        $this->open = $this->open === $id ? null : $id;
    }

    public function toggleRead(int $id): void
    {
        $request = CreatorContactRequest::findOrFail($id);
        $request->update(['read_at' => $request->read_at ? null : now()]);
    }

    public function destroy(int $id): void
    {
        CreatorContactRequest::findOrFail($id)->delete();
        $this->notify('success', 'Message deleted.');
    }

    public function render(): View
    {
        $requests = CreatorContactRequest::query()
            ->with('creator')
            ->when($this->filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('subject', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(25);

        return view('livewire.admin.contact-requests', [
            'requests' => $requests,
            'unread' => CreatorContactRequest::whereNull('read_at')->count(),
        ])->title('Contact requests');
    }
}
