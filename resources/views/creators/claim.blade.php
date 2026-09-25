<x-layouts.app title="Claim {{ $creator->name }}" :noindex="true">
    <div class="mx-auto max-w-xl">
        <a href="{{ route('creators.show', $creator) }}" class="text-sm text-ink-500 hover:text-ink-950">← Back to profile</a>
        <div class="mt-3 flex items-center gap-3">
            <x-avatar :creator="$creator" size="lg" />
            <div>
                <h1 class="display text-2xl tracking-[-0.025em]">Claim {{ $creator->name }}</h1>
                <p class="text-sm text-ink-500">Sign in with the social account this profile belongs to and it’s yours.</p>
            </div>
        </div>

        @if($creator->isClaimed())
            <div class="card mt-6 p-5 text-sm">
                <p class="font-medium text-ink-950">This profile has already been claimed.</p>
                <p class="mt-1 text-ink-500">If this is actually your account, get in touch and we’ll sort it out.</p>
            </div>
        @elseif($alreadyOwnsProfile)
            <div class="card mt-6 p-5 text-sm">
                <p class="font-medium text-ink-950">Your account already owns a creator profile.</p>
                <p class="mt-1 text-ink-500">One login, one profile. If this is a duplicate of yours, ask us to merge it.</p>
                <a href="{{ route('account') }}" class="btn-secondary btn-sm mt-3">Go to my profile</a>
            </div>
        @else
            <div class="card mt-6 divide-y divide-ink-100">
                @foreach($creator->socialAccounts as $account)
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                        <div class="flex items-center gap-3">
                            <span class="text-ink-900"><x-platform-icon :platform="$account->platform" class="size-5" /></span>
                            <div>
                                <p class="text-sm font-medium text-ink-950">{{ $account->platform->label() }} {{ $account->handleWithAt() }}</p>
                                <p class="text-xs text-ink-500">
                                    @if($account->platform->isEnabled())
                                        Sign in with this exact account. If {{ $account->platform->label() }} tells us it’s a different one, the claim won’t go through.
                                    @else
                                        {{ $account->platform->label() }} claims are switched off for now. Claim another platform on this profile, or check back later.
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($account->platform->isEnabled())
                            <form method="POST" action="{{ route('creators.claim.start', [$creator, $account]) }}">
                                @csrf
                                <button type="submit" class="btn-primary btn-sm">Verify with {{ $account->platform->label() }}</button>
                            </form>
                        @else
                            <x-badge>Not available yet</x-badge>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 text-sm text-ink-500 space-y-2">
                <p class="font-medium text-ink-900">What happens next</p>
                <ol class="list-decimal pl-5 space-y-1">
                    <li>We send you to {{ $creator->socialAccounts->filter(fn ($a) => $a->platform->isEnabled())->map(fn ($a) => $a->platform->label())->unique()->join(', ', ' or ') }} to sign in. We only ask for read access.</li>
                    <li>If it’s the account on this profile, the profile is yours and linked to your login.</li>
                    <li>We pull in your content and stats in the background and show them as verified.</li>
                </ol>
                <p class="text-xs text-ink-400">We never go by email alone and we never see your password. You can disconnect whenever you want.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
