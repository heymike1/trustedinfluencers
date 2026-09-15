<x-layouts.app title="Sign in with {{ $platform->label() }} (fake)" :noindex="true">
    <div class="mx-auto max-w-md">
        <div class="card p-6">
            <div class="flex items-center gap-3">
                <span class="text-ink-900"><x-platform-icon :platform="$platform" class="size-6" /></span>
                <div>
                    <h1 class="text-lg font-semibold text-ink-950">Sign in with {{ $platform->label() }}</h1>
                    <p class="text-xs text-ink-500">Stand-in for the real {{ $platform->label() }} sign-in screen while developing.</p>
                </div>
            </div>

            <div class="mt-4 rounded-md border border-warn-100 bg-amber-50 px-3 py-2 text-xs text-warn-700">
                Fake connector is on (<code>SOCIAL_CONNECTOR_DRIVER=fake</code>). Whatever handle you type below counts as the signed-in account. Type a different one to test a mismatch.
            </div>

            <form method="POST" action="{{ route('oauth.fake.decide', $platform->value) }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="state" value="{{ $state }}">
                <div>
                    <label class="label" for="handle">Sign in as</label>
                    <div class="flex items-center">
                        <span class="rounded-l-md border border-r-0 border-ink-200 bg-ink-50 px-3 py-2 text-sm text-ink-500">@</span>
                        <input id="handle" name="handle" type="text" value="{{ old('handle', $hint) }}" class="input rounded-l-none" autocomplete="off" autofocus>
                    </div>
                    @error('handle')<p class="mt-1 text-xs text-danger-700">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-md border border-ink-200 p-3 text-xs text-ink-600">
                    <p class="font-medium text-ink-900 mb-1">{{ config('app.name') }} is requesting</p>
                    <ul class="list-disc pl-4 space-y-0.5">
                        <li>Read your public profile and account ID</li>
                        <li>Read your content list</li>
                        <li>Read analytics for your content</li>
                    </ul>
                </div>
                <div class="flex gap-2">
                    <button type="submit" name="decision" value="deny" class="btn-secondary flex-1">Cancel</button>
                    <button type="submit" name="decision" value="allow" class="btn-primary flex-1">Allow</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
