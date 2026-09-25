<?php

namespace App\Livewire;

use App\Actions\Creators\CreateCreator;
use App\Actions\Creators\DuplicateCreatorException;
use App\Enums\Platform;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Social\Support\HandleNormalizer;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddCreator extends Component
{
    public string $name = '';

    public string $platform = 'youtube';

    public string $handle = '';

    public string $category = '';

    public ?int $existingCreatorId = null;

    /** Live preview of the normalised handle so the visitor sees what will be stored. */
    public function updatedHandle(): void
    {
        $this->existingCreatorId = null;

        $platform = app(HandleNormalizer::class)->detectPlatform($this->handle);

        if ($platform) {
            $this->platform = $platform->value;
        }
    }

    public function updatedPlatform(): void
    {
        $this->existingCreatorId = null;
    }

    public function normalizedHandle(): ?string
    {
        $platform = Platform::tryFrom($this->platform);

        if (! $platform || trim($this->handle) === '') {
            return null;
        }

        return app(HandleNormalizer::class)->normalize($platform, $this->handle)?->handle;
    }

    public function submit(CreateCreator $createCreator)
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'platform' => ['required', Rule::enum(Platform::class)],
            'handle' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::exists('creator_categories', 'id')],
        ]);

        try {
            $creator = $createCreator->handle(
                $data['name'],
                Platform::from($data['platform']),
                $data['handle'],
                $data['category'] !== '' ? (int) $data['category'] : null,
            );
        } catch (DuplicateCreatorException $e) {
            $this->existingCreatorId = $e->existing->id;
            $this->addError('handle', $e->getMessage());

            return;
        }

        session()->flash('success', "{$creator->name} is now listed. If this is you, claim the profile to show your real numbers.");

        return $this->redirectRoute('creators.show', $creator);
    }

    public function render(): View
    {
        return view('livewire.add-creator', [
            'categories' => CreatorCategory::orderBy('sort_order')->orderBy('name')->get(),
            'platforms' => Platform::enabled(),
            'preview' => $this->normalizedHandle(),
            'existing' => $this->existingCreatorId ? Creator::find($this->existingCreatorId) : null,
        ])->layout('components.layouts.app', [
            'band' => true,
            'description' => 'List a creator on YouTube, Instagram or X with a name and a handle. The profile goes live straight away; the creator can claim it later.',
            'canonical' => route('creators.create'),
        ])->title('Add a creator');
    }
}
