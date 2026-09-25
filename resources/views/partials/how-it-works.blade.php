{{-- The three verification steps, each with a small piece of the real UI. Shared by the home and about pages. --}}
<div class="grid gap-4 md:grid-cols-3">
    <div class="card p-6 flex flex-col gap-4">
        <span class="inline-flex self-start rounded-full border border-band-edge bg-verified-50 px-2.5 py-0.5 text-xs font-semibold tracking-wide text-brand-700 tnum">01</span>
        <div>
            <h3 class="display text-[19px] tracking-[-0.02em]">Anyone adds a creator</h3>
            <p class="mt-1.5 text-sm text-ink-500">A name, a platform and a handle is enough. The profile is live straight away and shows public info only.</p>
        </div>
        <div class="mt-2 rounded-xl border border-ink-100 bg-ink-50 p-4 flex flex-col gap-2.5" aria-hidden="true">
            <span class="flex h-10 items-center rounded-full border border-ink-200 bg-white px-4 text-[13px] text-ink-900">instagram.com/yourhandle<span class="ml-0.5 inline-block h-4 w-px bg-ink-900"></span></span>
            <span class="btn-primary btn-sm self-start pointer-events-none">Add to the directory</span>
        </div>
    </div>
    <div class="card p-6 flex flex-col gap-4">
        <span class="inline-flex self-start rounded-full border border-band-edge bg-verified-50 px-2.5 py-0.5 text-xs font-semibold tracking-wide text-brand-700 tnum">02</span>
        <div>
            <h3 class="display text-[19px] tracking-[-0.02em]">The creator claims it</h3>
            <p class="mt-1.5 text-sm text-ink-500">No screenshots, no email checks. They sign in with the social account itself; wrong account, no claim.</p>
        </div>
        <div class="mt-2 rounded-xl border border-ink-100 bg-ink-50 p-4 flex flex-col gap-2.5" aria-hidden="true">
            <span class="flex h-10 items-center gap-2.5 rounded-full border border-ink-200 bg-white px-4 text-[13px] font-semibold text-ink-900"><x-platform-icon platform="instagram" class="size-4" :colored="true" /> Continue with Instagram</span>
            <span class="inline-flex items-center gap-2 text-xs font-semibold text-brand-700"><svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg> Account matches the profile</span>
        </div>
    </div>
    <div class="card p-6 flex flex-col gap-4">
        <span class="inline-flex self-start rounded-full border border-band-edge bg-verified-50 px-2.5 py-0.5 text-xs font-semibold tracking-wide text-brand-700 tnum">03</span>
        <div>
            <h3 class="display text-[19px] tracking-[-0.02em]">The numbers come in</h3>
            <p class="mt-1.5 text-sm text-ink-500">Views, watch time, audience and engagement are pulled from the platform and refreshed every day.</p>
        </div>
        <dl class="mt-2 rounded-xl border border-ink-100 bg-ink-50 p-4 space-y-2 text-[13px] tnum" aria-hidden="true">
            <div class="flex justify-between"><dt class="text-ink-500">Median views</dt><dd class="font-semibold text-ink-950">68K</dd></div>
            <div class="flex justify-between"><dt class="text-ink-500">Watched at halfway</dt><dd class="font-semibold text-ink-950">48%</dd></div>
            <div class="flex justify-between"><dt class="text-ink-500">Viewers aged 25–34</dt><dd class="font-semibold text-ink-950">41%</dd></div>
            <div class="flex items-center gap-1.5 pt-1 text-xs font-semibold text-brand-700"><span class="size-1.5 rounded-full bg-brand-600"></span> Refreshed every {{ config('social.sync.refresh_every_hours') }} hours</div>
        </dl>
    </div>
</div>
