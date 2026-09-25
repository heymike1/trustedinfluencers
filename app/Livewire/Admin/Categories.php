<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\HasNotice;
use App\Models\CreatorCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Categories are the only taxonomy in the app: creators point at one, and the directory and
 * home page browse by them.
 */
#[Layout('components.layouts.admin')]
class Categories extends Component
{
    use HasNotice;

    public ?int $editing = null;

    public string $name = '';

    public string $slug = '';

    public int $sort_order = 0;

    public function edit(int $id): void
    {
        $category = CreatorCategory::findOrFail($id);

        $this->editing = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->sort_order = $category->sort_order;
    }

    public function cancel(): void
    {
        $this->reset('editing', 'name', 'slug', 'sort_order');
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:60'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('creator_categories', 'slug')->ignore($this->editing)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        if ($this->editing) {
            CreatorCategory::findOrFail($this->editing)->update($data);
            $this->notify('success', 'Category updated.');
        } else {
            CreatorCategory::create($data);
            $this->notify('success', 'Category added.');
        }

        $this->cancel();
    }

    public function move(int $id, int $direction): void
    {
        $categories = CreatorCategory::orderBy('sort_order')->orderBy('id')->get();
        $index = $categories->search(fn (CreatorCategory $c) => $c->id === $id);
        $swapWith = $categories->get($index + $direction);

        if ($swapWith === null) {
            return;
        }

        // Positions can be equal or unset, so renumber the whole list from the swapped order.
        $ordered = $categories->toBase()->swap($index, $index + $direction);
        $ordered->each(fn (CreatorCategory $c, int $i) => $c->update(['sort_order' => $i]));
    }

    public function destroy(int $id): void
    {
        $category = CreatorCategory::withCount('creators')->findOrFail($id);

        if ($category->creators_count > 0) {
            $this->notify('error', "{$category->name} still has {$category->creators_count} creators. Move them first.");

            return;
        }

        $category->delete();
        $this->notify('success', 'Category removed.');
    }

    public function render(): View
    {
        return view('livewire.admin.categories', [
            'categories' => CreatorCategory::withCount(['creators', 'creators as verified_count' => fn ($q) => $q->where('has_verified_metrics', true)])
                ->orderBy('sort_order')->orderBy('id')->get(),
        ])->title('Categories');
    }
}
