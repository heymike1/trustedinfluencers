<?php

namespace App\Livewire\Account;

use App\Actions\Auth\DeleteUserAccount;
use App\Models\Creator;
use App\Models\CreatorCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The creator edits public profile details only. Verified metrics are never editable.
 */
#[Layout('components.layouts.app')]
class EditProfile extends Component
{
    public ?Creator $creator = null;

    public string $name = '';

    public string $bio = '';

    public string $category = '';

    public string $avatar_url = '';

    public string $location = '';

    public string $website = '';

    public string $contact_email = '';

    public bool $contact_enabled = true;

    public bool $is_listed = true;

    public function mount(): void
    {
        $this->creator = auth()->user()->creator;

        if ($this->creator) {
            $this->fill([
                'name' => $this->creator->name,
                'bio' => $this->creator->bio ?? '',
                'category' => (string) ($this->creator->creator_category_id ?? ''),
                'avatar_url' => $this->creator->avatar_url ?? '',
                'location' => $this->creator->location ?? '',
                'website' => $this->creator->website ?? '',
                'contact_email' => $this->creator->contact_email ?? '',
                'contact_enabled' => $this->creator->contact_enabled,
                'is_listed' => $this->creator->is_listed,
            ]);
        }
    }

    public function save(): void
    {
        abort_unless($this->creator?->isOwnedBy(auth()->user()), 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'bio' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', Rule::exists('creator_categories', 'id')],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
            'location' => ['nullable', 'string', 'max:80'],
            'website' => ['nullable', 'url', 'max:2048'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_enabled' => ['boolean'],
            'is_listed' => ['boolean'],
        ]);

        $this->creator->fill([
            'name' => $data['name'],
            'bio' => $data['bio'] ?: null,
            'creator_category_id' => $data['category'] !== '' ? (int) $data['category'] : null,
            'avatar_url' => $data['avatar_url'] ?: null,
            'location' => $data['location'] ?: null,
            'website' => $data['website'] ?: null,
            'contact_email' => $data['contact_email'] ?: null,
            'contact_enabled' => $data['contact_enabled'],
            'is_listed' => $data['is_listed'],
        ]);

        if ($this->creator->isDirty('name')) {
            $this->creator->slug = Creator::uniqueSlugFor($data['name'], $this->creator->id);
        }

        $this->creator->save();

        session()->flash('success', 'Profile updated.');
        $this->redirectRoute('account');
    }

    public function deleteAccount(DeleteUserAccount $delete): void
    {
        try {
            $delete->handle(auth()->user());
        } catch (ValidationException $e) {
            $this->addError('account', collect($e->errors())->flatten()->first());

            return;
        }

        // Not Auth::logout(): cycling the remember token would save() the deleted model back into the table.
        Auth::forgetUser();
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('success', 'Your account and profile have been deleted.');

        $this->redirectRoute('home');
    }

    public function render(): View
    {
        return view('livewire.account.edit-profile', [
            'categories' => CreatorCategory::orderBy('sort_order')->orderBy('name')->get(),
        ])->title('My profile');
    }
}
