{{-- Loads the Datafast analytics script only after the visitor accepts. Choice is remembered in localStorage. --}}
<div id="cookie-banner" hidden class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-lg rounded-md border border-ink-200 bg-white p-4 shadow-lg sm:inset-x-auto sm:right-6">
    <p class="text-sm text-ink-700">We use one analytics cookie (Datafast) to count visits. No ads, no tracking across sites. <a href="{{ route('privacy') }}#cookies" class="underline underline-offset-2 text-ink-900">Details</a></p>
    <div class="mt-3 flex gap-2">
        <button type="button" data-cookie="accept" class="btn-primary btn-sm">Allow</button>
        <button type="button" data-cookie="decline" class="btn-secondary btn-sm">No thanks</button>
    </div>
</div>
<script>
(function () {
    var key = 'cookie-consent', banner = document.getElementById('cookie-banner'), choice = null;
    try { choice = localStorage.getItem(key); } catch (e) {}

    function load() {
        var s = document.createElement('script');
        s.defer = true;
        s.src = 'https://datafa.st/js/script.js';
        s.dataset.websiteId = @json(config('services.datafast.website_id'));
        s.dataset.domain = @json(config('services.datafast.domain'));
        document.head.appendChild(s);
    }

    if (choice === 'accepted') { load(); return; }
    if (choice === 'declined') { return; }

    banner.hidden = false;
    banner.addEventListener('click', function (e) {
        var action = e.target.dataset.cookie;
        if (!action) return;
        try { localStorage.setItem(key, action === 'accept' ? 'accepted' : 'declined'); } catch (e) {}
        banner.hidden = true;
        if (action === 'accept') load();
    });
})();
</script>
