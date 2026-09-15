{{-- Loads the analytics script only after the visitor accepts. Choice is remembered in localStorage. --}}
<div id="cookie-banner" hidden class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-lg rounded-xl border border-ink-200 bg-white p-4 shadow-lg sm:inset-x-auto sm:right-6">
    <p class="text-sm font-semibold text-ink-950">Cookies on this site</p>
    <p class="mt-1 text-sm text-ink-700">Some cookies are needed to keep you signed in and to protect forms; those are always on. With your permission we also set one analytics cookie so we can see which pages are used and how many people visit. It doesn't follow you to other sites and it isn't used for advertising. You can change your mind any time from the cookie policy.</p>
    <p class="mt-1 text-xs text-ink-500"><a href="{{ route('privacy') }}#cookies" class="underline underline-offset-2 text-ink-700">Read the cookie policy</a></p>
    <div class="mt-3 flex gap-2">
        <button type="button" data-cookie="accept" class="btn-primary btn-sm">Allow analytics</button>
        <button type="button" data-cookie="decline" class="btn-secondary btn-sm">Only what's needed</button>
    </div>
</div>
<script>
(function () {
    var key = 'cookie-consent', banner = document.getElementById('cookie-banner'), loaded = false, choice = null;
    try { choice = localStorage.getItem(key); } catch (e) {}

    function load() {
        if (loaded) return;
        loaded = true;
        var s = document.createElement('script');
        s.defer = true;
        s.src = 'https://datafa.st/js/script.js';
        s.dataset.websiteId = @json(config('services.datafast.website_id'));
        s.dataset.domain = @json(config('services.datafast.domain'));
        document.head.appendChild(s);
    }

    banner.addEventListener('click', function (e) {
        var action = e.target.closest('[data-cookie]');
        if (!action) return;
        var accepted = action.dataset.cookie === 'accept';
        try { localStorage.setItem(key, accepted ? 'accepted' : 'declined'); } catch (e) {}
        banner.hidden = true;
        if (accepted) load();
    });

    document.querySelectorAll('[data-cookie-settings]').forEach(function (el) {
        el.addEventListener('click', function () { banner.hidden = false; });
    });

    if (choice === 'accepted') load();
    else if (choice !== 'declined') banner.hidden = false;
})();
</script>
