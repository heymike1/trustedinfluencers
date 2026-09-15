<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\HasNotice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Users extends Component
{
    use HasNotice;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function toggleAdmin(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->is($this->currentUser())) {
            $this->notify('error', 'You cannot change your own admin status.');

            return;
        }

        $user->update(['is_admin' => ! $user->is_admin]);
    }

    private function currentUser(): User
    {
        return auth()->user();
    }

    public function render(): View
    {
        $users = User::query()
            ->with('creator')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(30);

        return view('livewire.admin.users', ['users' => $users])->title('Admin · Users');
    }
}
