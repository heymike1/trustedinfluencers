<x-layouts.app title="Sign in with Google (fake)" :noindex="true">
    <div class="mx-auto max-w-md">
        <div class="card p-6">
            <h1 class="text-lg font-semibold text-ink-950">Sign in with Google</h1>
            <p class="text-xs text-ink-500">Stand-in for the real Google sign-in while developing.</p>
            <div class="mt-4 rounded-md border border-warn-100 bg-amber-50 px-3 py-2 text-xs text-warn-700">
                Fake connector is on. Whatever email you type counts as the Google account that signed in. Use an existing user's email to log in as them, or a new one to create an account.
            </div>
            <form method="POST" action="{{ route('oauth.fake.google.decide') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="state" value="{{ $state }}">
                <div>
                    <label class="label" for="email">Google account email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $hint) }}" class="input" autofocus>
                    @error('email')<p class="mt-1 text-xs text-danger-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="name">Name <span class="normal-case font-normal text-ink-400">(optional)</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="input">
                </div>
                <div class="flex gap-2">
                    <button type="submit" name="decision" value="deny" class="btn-secondary flex-1">Cancel</button>
                    <button type="submit" name="decision" value="allow" class="btn-primary flex-1">Continue</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
