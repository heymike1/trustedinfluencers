<x-layouts.app title="Privacy policy" description="What Trusted Influencers stores about creators and visitors, why, for how long, and how to get it deleted." :canonical="route('privacy')">
    <article class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Privacy policy</h1>
        <p class="mt-1 text-sm text-ink-500">Last updated {{ config('app.legal.updated') }}. {{ config('app.name') }} is run by {{ config('app.legal.entity') }} in the Netherlands.</p>

        <div class="prose-sm mt-8 space-y-8 text-ink-700 [&_h2]:text-base [&_h2]:font-semibold [&_h2]:text-ink-950 [&_h2]:mb-2 [&_p]:mt-2 [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mt-1">
            <section>
                <h2>The short version</h2>
                <p>Creator profiles on this site are public. What we store about you depends on what you do: visit, sign in, or connect a social account. Connecting is what gives us access to your analytics, and disconnecting deletes everything we pulled in. We never see your passwords, and we never sell data.</p>
            </section>

            <section>
                <h2>If you only visit</h2>
                <p>We keep server logs (IP address, browser, pages requested) for security and debugging, for up to 30 days. We use <a href="https://datafa.st" class="underline underline-offset-2">Datafast</a> for visitor statistics. Datafast sets a cookie to tell returning visitors from new ones and records pages visited, referrer, country, device and browser. We use it to see which pages are used, not to identify you.</p>
                <p>A session cookie keeps you signed in and protects forms against forgery. Nothing else is set.</p>
            </section>

            <section>
                <h2>If someone adds you as a creator</h2>
                <p>Anyone can list a creator with a name, a platform and a handle. That creates a public profile with information that is already public on the platform, or that the person who added it typed in. Where the platform offers a public lookup (YouTube, X), we also fetch the public display name, avatar and audience count. Such profiles are marked "Public info only".</p>
                <p>If a profile about you exists and you don't want it, claim it and hide it from your dashboard, or email us and we'll remove it.</p>
            </section>

            <section>
                <h2>If you sign in</h2>
                <p>With Google we receive your Google account id, name, email address and profile picture. With email we store your email and a hashed password. We use these to run your account and to send you contact requests and password reset links. We don't send newsletters.</p>
            </section>

            <section>
                <h2>If you connect YouTube, Instagram or X</h2>
                <p>This is the part that matters. When you claim a profile you sign in with the social account itself. The platform then gives us:</p>
                <ul>
                    <li>your account id and handle, used to check that it's really your account</li>
                    <li>an access token (and a refresh token where the platform supports it), stored encrypted, so we can fetch your data again later</li>
                    <li>your recent content and its analytics: views, watch time, retention, reach, likes, comments, shares, saves, clicks</li>
                    <li>audience breakdowns where the platform offers them: age, gender, country, city, device, follower vs. non-follower reach</li>
                </ul>
                <p>We refresh this about once a day and keep a history of each refresh so the profile can show how numbers change over time. Everything is shown publicly on your profile as "verified", because it came from your own account through the platform's official API. You can't edit these numbers, and neither can we.</p>
                <p>We only ask for read access. We never post, message, or change anything on your accounts.</p>
            </section>

            <section>
                <h2>Disconnecting and deleting</h2>
                <p>On your <em>Connected accounts</em> page you can disconnect any platform at any time. That immediately destroys the stored tokens and deletes all content, analytics and audience data we pulled in for that account, history included. Your public profile (name, handle, last known audience count) stays listed, exactly as if a visitor had added it.</p>
                <p>To delete your whole account and everything attached to it, email {{ config('app.legal.contact') }} from the address you signed up with. We'll confirm within a few days.</p>
                <p>You can also revoke our access on the platform's side: Google at <a href="https://myaccount.google.com/permissions" class="underline underline-offset-2">myaccount.google.com/permissions</a>, Instagram under Settings → Apps and websites, X under Settings → Security and account access → Apps and sessions. If you do, the next refresh fails and your profile shows "Needs reconnection" until you delete or reconnect.</p>
            </section>

            <section>
                <h2>YouTube</h2>
                <p>This site uses YouTube API Services. By connecting a YouTube channel you also agree to the <a href="https://www.youtube.com/t/terms" class="underline underline-offset-2">YouTube Terms of Service</a>, and Google's <a href="https://policies.google.com/privacy" class="underline underline-offset-2">Privacy Policy</a> applies to the data we receive from Google. We store YouTube data as described above, refresh it daily, and delete it when you disconnect. YouTube data is never shared with third parties or used for advertising.</p>
            </section>

            <section>
                <h2>Contact requests</h2>
                <p>When a visitor sends a contact request through a profile, we store their name, email, company, subject and message, and forward it to the creator by email. The creator gets the sender's email address to reply. For unclaimed profiles we keep the request until the creator claims the profile; we don't forward it to any email address a third party typed in.</p>
            </section>

            <section>
                <h2>Who else sees what</h2>
                <p>Profiles, including verified numbers, are public and can appear in search engines. Your email address, tokens and contact requests are never public. We use a hosting provider for servers and email delivery, and Datafast for statistics; they process data on our behalf and under our instructions. We don't sell or rent data, and we don't share it with brands or anyone else beyond what the public profile shows.</p>
            </section>

            <section>
                <h2>How long we keep things</h2>
                <ul>
                    <li>Server logs: up to 30 days</li>
                    <li>Connected-account data: while connected, deleted on disconnect</li>
                    <li>Account data: while your account exists</li>
                    <li>Contact requests: while the creator's profile exists</li>
                    <li>Failed background jobs: 7 days</li>
                </ul>
            </section>

            <section>
                <h2>Your rights</h2>
                <p>Under the GDPR (AVG) you can ask what we hold about you, have it corrected or deleted, or object to how we use it. Email {{ config('app.legal.contact') }}. You can also complain to the Dutch Data Protection Authority (Autoriteit Persoonsgegevens).</p>
            </section>

            <section>
                <h2>Changes</h2>
                <p>If this policy changes in a way that matters, we'll update the date at the top and, for connected creators, mention it on the dashboard.</p>
            </section>
        </div>
    </article>
</x-layouts.app>
