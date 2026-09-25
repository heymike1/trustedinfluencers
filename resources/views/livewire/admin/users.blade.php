<x-slot:heading>Users</x-slot:heading>
<x-slot:subheading>{{ number_format($users->total()) }} accounts</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="grid gap-5 {{ $editing ? 'lg:grid-cols-[minmax(0,1fr)_320px]' : '' }} lg:items-start">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Name or email" class="input max-w-xs">
                <select wire:model.live="filter" class="input w-auto">
                    <option value="">Everyone</option>
                    <option value="admins">Admins</option>
                    <option value="creators">Owns a profile</option>
                    <option value="google">Signs in with Google</option>
                </select>
            </div>

            <div class="card overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th class="pl-4">User</th><th>Profile</th><th>Sign-in</th><th class="text-right">Claims</th><th>Joined</th><th class="pr-4"></th></tr></thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td class="pl-4">
                                    <p class="font-medium text-ink-950">{{ $user->name }} @if($user->is_admin)<x-badge variant="dark">Admin</x-badge>@endif</p>
                                    <p class="text-xs text-ink-500">{{ $user->email }}</p>
                                </td>
                                <td class="text-xs">
                                    @if($user->creator)
                                        <a href="{{ route('admin.creators.show', $user->creator) }}" class="font-medium text-brand-700 hover:underline">{{ $user->creator->name }}</a>
                                    @else
                                        <span class="text-ink-400">—</span>
                                    @endif
                                </td>
                                <td class="text-xs text-ink-500">{{ $user->google_id ? 'Google' : 'Email' }}{{ $user->password === null ? ' only' : '' }}</td>
                                <td class="text-right tnum">{{ $user->claims_count }}</td>
                                <td class="text-xs text-ink-500 whitespace-nowrap">{{ $user->created_at->format('j M Y') }}</td>
                                <td class="pr-4 text-right whitespace-nowrap">
                                    <button type="button" wire:click="edit({{ $user->id }})" class="btn-secondary btn-sm">Edit</button>
                                    <button type="button" wire:click="destroy({{ $user->id }})" wire:confirm="Delete {{ $user->email }}? Any profile they own becomes unclaimed." class="btn-danger btn-sm">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>

        @if($editing)
            <form wire:submit="save" class="card p-5 space-y-4">
                <h2 class="text-[15px] font-semibold text-ink-950">Edit user</h2>
                <div>
                    <label class="label" for="u-name">Name</label>
                    <input id="u-name" type="text" wire:model="name" class="input">
                    <x-field-error for="name" />
                </div>
                <div>
                    <label class="label" for="u-email">Email</label>
                    <input id="u-email" type="email" wire:model="email" class="input">
                    <x-field-error for="email" />
                </div>
                <div>
                    <label class="label" for="u-password">New password <span class="normal-case font-normal text-ink-400">(optional)</span></label>
                    <input id="u-password" type="text" wire:model="password" class="input" placeholder="Leave empty to keep">
                    <x-field-error for="password" />
                </div>
                <x-switch-row model="is_admin" title="Admin access" description="Can open this panel and manage everything in it." />
                <x-field-error for="is_admin" />
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">Save changes</button>
                    <button type="button" wire:click="cancel" class="btn-secondary">Cancel</button>
                </div>
            </form>
        @endif
    </div>
</div>
