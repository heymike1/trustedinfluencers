<x-slot:heading>Categories</x-slot:heading>
<x-slot:subheading>{{ $categories->count() }} categories · creators pick one, the directory browses by them</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <div class="card overflow-hidden">
            <table class="data-table">
                <thead><tr><th class="pl-4">Name</th><th>Slug</th><th class="text-right">Creators</th><th class="text-right">Verified</th><th class="pr-4"></th></tr></thead>
                <tbody>
                    @foreach($categories as $i => $category)
                        <tr wire:key="cat-{{ $category->id }}">
                            <td class="pl-4 font-medium text-ink-950">{{ $category->name }}</td>
                            <td class="text-xs text-ink-500">{{ $category->slug }}</td>
                            <td class="text-right tnum">{{ $category->creators_count }}</td>
                            <td class="text-right tnum">{{ $category->verified_count }}</td>
                            <td class="pr-4 text-right whitespace-nowrap">
                                <button type="button" wire:click="move({{ $category->id }}, -1)" class="btn-secondary btn-sm" @disabled($i === 0) aria-label="Move up">↑</button>
                                <button type="button" wire:click="move({{ $category->id }}, 1)" class="btn-secondary btn-sm" @disabled($i === $categories->count() - 1) aria-label="Move down">↓</button>
                                <button type="button" wire:click="edit({{ $category->id }})" class="btn-secondary btn-sm">Edit</button>
                                <button type="button" wire:click="destroy({{ $category->id }})" wire:confirm="Remove {{ $category->name }}?" class="btn-danger btn-sm">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form wire:submit="save" class="card p-5 space-y-4">
            <h2 class="text-[15px] font-semibold text-ink-950">{{ $editing ? 'Edit category' : 'New category' }}</h2>
            <div>
                <label class="label" for="name">Name</label>
                <input id="name" type="text" wire:model="name" class="input">
                <x-field-error for="name" />
            </div>
            <div>
                <label class="label" for="slug">Slug <span class="normal-case font-normal text-ink-400">(optional)</span></label>
                <input id="slug" type="text" wire:model="slug" class="input" placeholder="From the name">
                <p class="mt-1 text-xs text-ink-400">Changing this breaks existing links to the filtered directory.</p>
                <x-field-error for="slug" />
            </div>
            <div>
                <label class="label" for="sort_order">Order</label>
                <input id="sort_order" type="number" min="0" max="999" wire:model="sort_order" class="input">
                <x-field-error for="sort_order" />
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">{{ $editing ? 'Save changes' : 'Add category' }}</button>
                @if($editing)<button type="button" wire:click="cancel" class="btn-secondary">Cancel</button>@endif
            </div>
        </form>
    </div>
</div>
