<div>
    <x-admin-nav />
    <x-notice :notice="$notice" />
    <div class="flex items-center gap-2 mb-4">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search name or email" class="input max-w-xs">
        <p class="text-sm text-ink-500 ml-auto tnum">{{ number_format($users->total()) }} users</p>
    </div>
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Creator profile</th><th>Role</th><th>Joined</th><th></th></tr></thead>
            <tbody>
                @foreach($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td><span class="font-medium">{{ $user->name }}</span> <span class="text-ink-500 text-xs">{{ $user->email }}</span></td>
                        <td class="text-sm">
                            @if($user->creator)
                                <a href="{{ route('admin.creators.show', $user->creator) }}" class="hover:underline">{{ $user->creator->name }}</a>
                            @else
                                <span class="text-ink-400">—</span>
                            @endif
                        </td>
                        <td>@if($user->is_admin)<x-badge variant="dark">Admin</x-badge>@else<span class="text-xs text-ink-500">User</span>@endif</td>
                        <td class="text-xs text-ink-400 whitespace-nowrap">{{ $user->created_at->diffForHumans() }}</td>
                        <td class="text-right"><button type="button" wire:click="toggleAdmin({{ $user->id }})" class="btn-secondary btn-sm">{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
