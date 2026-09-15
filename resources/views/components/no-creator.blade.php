<div class="mx-auto max-w-lg card p-6 text-center">
    <p class="font-semibold text-ink-950">You haven’t claimed a creator profile yet.</p>
    <p class="mt-1 text-sm text-ink-500">Find yourself in the directory and claim the profile by signing in with your social account. Not listed yet? Add yourself first.</p>
    <div class="mt-4 flex justify-center gap-2">
        <a href="{{ route('creators.index') }}" class="btn-primary btn-sm">Browse creators</a>
        <a href="{{ route('creators.create') }}" class="btn-secondary btn-sm">Add a creator</a>
    </div>
</div>
