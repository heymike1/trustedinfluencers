<x-layouts.app title="Terms of service" description="The rules for using Trusted Influencers: adding creators, claiming profiles, verified numbers and what we may remove." :canonical="route('terms')">
    <article class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Terms of service</h1>
        <p class="mt-1 text-sm text-ink-500">Last updated {{ config('app.legal.updated') }}. {{ config('app.name') }} is run by {{ config('app.legal.entity') }} and these terms are governed by Dutch law.</p>

        <div class="mt-8 space-y-8 text-ink-700 [&_h2]:text-base [&_h2]:font-semibold [&_h2]:text-ink-950 [&_h2]:mb-2 [&_p]:mt-2 [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mt-1">
            <section>
                <h2>What this is</h2>
                <p>A public database of creators. Anyone can add a creator. The creator can claim their profile by signing in with the social account it belongs to, after which the site shows numbers pulled straight from that platform. Using the site means you agree to these terms and to our <a href="{{ route('privacy') }}" class="underline underline-offset-2">privacy policy</a>.</p>
            </section>

            <section>
                <h2>Adding creators</h2>
                <p>You may add any creator who has a public YouTube, Instagram or X account. Add real accounts only, with the correct handle. Don't add private individuals who aren't creators, and don't add someone to harass them. We tidy up handles so an account can only be listed once; if a duplicate slips through, we merge it.</p>
            </section>

            <section>
                <h2>Claiming a profile</h2>
                <p>Only the person who controls the social account may claim its profile, and the only way to claim is by signing in with that account. Don't try to claim accounts that aren't yours. A claim that turns out to be wrong can be undone by us at any time. One login can own one profile.</p>
            </section>

            <section>
                <h2>Verified numbers</h2>
                <p>"Verified" means one thing: the creator signed in with their own account and the numbers came from the platform's API. It is not an endorsement, not a guarantee of future performance, and not the platform's own verification badge. Verified numbers can't be edited by the creator or by us. Public info on unclaimed profiles comes from the platform or from whoever added the profile and may be out of date.</p>
                <p>Platforms change their APIs and sometimes report differently from their own dashboards. We show what the API returns, refreshed roughly daily.</p>
            </section>

            <section>
                <h2>Your account</h2>
                <p>Keep your login to yourself. You're responsible for what happens under it. You can disconnect platforms and delete your account at any time; see the privacy policy for what that removes.</p>
            </section>

            <section>
                <h2>Contact requests</h2>
                <p>The contact form is for genuine enquiries to the creator. No spam, no bulk messages, no scraping of the site to build lists. Creators can switch contact requests off. We're not a party to any deal you make with a creator.</p>
            </section>

            <section>
                <h2>What we may do</h2>
                <p>We may remove or hide profiles, merge duplicates, undo claims, block accounts and refuse contact requests when something is spam, misleading, abusive, or breaks a platform's rules or the law. We may change or stop the service. We'll try to be reasonable about it.</p>
            </section>

            <section>
                <h2>Liability</h2>
                <p>The site is provided as is. We do our best to keep it accurate and available, but we don't guarantee either, and we're not liable for decisions you make based on what you see here, for downtime, or for what platforms do with their APIs. Nothing here limits liability that can't be limited under Dutch law.</p>
            </section>

            <section>
                <h2>Platforms</h2>
                <p>We're not affiliated with YouTube, Google, Instagram, Meta or X. Their names and logos belong to them. Connecting YouTube also means agreeing to the <a href="https://www.youtube.com/t/terms" class="underline underline-offset-2">YouTube Terms of Service</a>.</p>
            </section>

            <section>
                <h2>Dutch law</h2>
                <p>Dutch law applies. Disputes go to the competent court in the Netherlands. Questions about these terms: {{ config('app.legal.contact') }}.</p>
            </section>
        </div>
    </article>
</x-layouts.app>
