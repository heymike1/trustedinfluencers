<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\HasNotice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Users extends Component
{
    use HasNotice;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** '' | admins | creators | google */
    #[Url(except: '')]
    public string $filter = '';

    public ?int $editing = null;

    public string $name = '';

    public string $email = '';

    public bool $is_admin = false;

    public string $password = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'filter'], true)) {
            $this->resetPage();
        }
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->editing = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_admin = $user->is_admin;
        $this->password = '';
    }

    public function cancel(): void
    {
        $this->reset('editing', 'name', 'email', 'is_admin', 'password');
        $this->resetValidation();
    }

    public function save(): void
    {
        $user = User::findOrFail($this->editing);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'is_admin' => ['boolean'],
            'password' => ['nullable', Password::min(8)],
        ]);

        if ($user->is(auth()->user()) && ! $data['is_admin']) {
            $this->addError('is_admin', 'You cannot remove your own admin access.');

            return;
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);
        // is_admin is deliberately not fillable, so it is set on the model instead.
        $user->is_admin = $data['is_admin'];

        if ($data['password']) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $this->notify('success', 'User updated.');
        $this->cancel();
    }

    public function destroy(int $id): void
    {
        $user = User::withCount('claims')->findOrFail($id);

        if ($user->is(auth()->user())) {
            $this->notify('error', 'You cannot delete your own account here.');

            return;
        }

        // Deleting the user releases the creator profile instead of taking it down with them.
        $user->creator?->forceFill(['user_id' => null, 'claimed_at' => null])->save();
        $user->delete();

        $this->notify('success', 'User deleted. Any profile they owned is unclaimed again.');
    }

    public function render(): View
    {
        $users = User::query()
            ->with('creator')
            ->withCount('claims')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->filter === 'admins', fn ($q) => $q->where('is_admin', true))
            ->when($this->filter === 'creators', fn ($q) => $q->has('creator'))
            ->when($this->filter === 'google', fn ($q) => $q->whereNotNull('google_id'))
            ->latest()
            ->paginate(30);

        return view('livewire.admin.users', ['users' => $users])->title('Users');
    }
}
