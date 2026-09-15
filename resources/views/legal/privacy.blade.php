<x-layouts.app title="Privacy policy" description="What Trusted Influencers stores about creators and visitors, on which legal basis, for how long, and how to get it deleted." :canonical="route('privacy')">
    <article class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Privacy policy</h1>
        <p class="mt-1 text-sm text-ink-500">Version {{ config('app.legal.version') }}, effective {{ config('app.legal.updated') }}.</p>

        <div class="mt-8 space-y-8 text-ink-700 [&_h2]:text-base [&_h2]:font-semibold [&_h2]:text-ink-950 [&_h2]:mb-2 [&_h3]:text-sm [&_h3]:font-semibold [&_h3]:text-ink-950 [&_h3]:mt-4 [&_p]:mt-2 [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mt-1 [&_table]:mt-3 [&_table]:w-full [&_table]:text-sm [&_th]:text-left [&_th]:font-semibold [&_th]:text-ink-950 [&_th]:py-1.5 [&_th]:pr-3 [&_th]:border-b [&_th]:border-ink-200 [&_td]:py-1.5 [&_td]:pr-3 [&_td]:align-top [&_td]:border-b [&_td]:border-ink-100">
            <section>
                <h2>1. Who is responsible</h2>
                <p>{{ config('app.name') }} ({{ config('app.url') }}, "the Service") is operated by <strong>{{ config('app.legal.entity') }}</strong>, established in the Netherlands ("we", "us"). We are the controller for the personal data described in this policy. Contact: <a href="mailto:{{ config('app.legal.contact') }}" class="underline underline-offset-2">{{ config('app.legal.contact') }}</a>.</p>
                <p>This policy applies to everyone who visits the Service, holds an account, is listed as a creator, or sends a contact request. It is written to meet the General Data Protection Regulation (GDPR / AVG) and the Dutch Telecommunications Act. Where this policy and a platform's own terms differ, the platform's terms govern the data that platform holds; this policy governs what we hold.</p>
            </section>

            <section>
                <h2>2. Scope and definitions</h2>
                <p>"Personal data" means any information relating to an identified or identifiable natural person. "Processing" means any operation performed on personal data, such as collecting, storing, publishing or deleting it. "Platform" means YouTube (Google LLC), Instagram (Meta Platforms Ireland Ltd.) and X (X Corp.). "Creator profile" means a public page on the Service describing one creator. "Connected account" means a platform account whose holder has authorised the Service to retrieve its data.</p>
                <p>This policy covers all personal data we process in connection with the Service. It does not cover the platforms' own processing, nor websites we link to.</p>
            </section>

            <section>
                <h2>3. What we process, why, and on which legal basis</h2>

                <h3>3.1 Visitors</h3>
                <table>
                    <tr><th>Data</th><th>Purpose</th><th>Legal basis</th></tr>
                    <tr><td>IP address, browser and device details, pages requested, timestamps (server logs)</td><td>Security, abuse prevention, debugging</td><td>Legitimate interest (Art. 6(1)(f) GDPR): keeping the Service running and secure</td></tr>
                    <tr><td>Session cookie</td><td>Keeping you signed in; protecting forms against forgery</td><td>Strictly necessary; no consent required (Art. 11.7a(3) Telecommunications Act)</td></tr>
                    <tr><td>Datafast analytics cookie and the events it records (pages visited, referrer, country, device, browser, approximate time on page)</td><td>Aggregated visitor statistics</td><td>Your consent (Art. 6(1)(a) GDPR; Art. 11.7a Telecommunications Act), given through the cookie banner and withdrawable at any time; see section 8</td></tr>
                </table>

                <h3>3.2 Account holders</h3>
                <table>
                    <tr><th>Data</th><th>Purpose</th><th>Legal basis</th></tr>
                    <tr><td>Name, email address; with Google sign-in also your Google account ID and profile picture; with email sign-in a hashed password</td><td>Creating and securing your account, signing you in, sending password reset links and contact requests addressed to you</td><td>Performance of a contract (Art. 6(1)(b)): providing the account you asked for</td></tr>
                    <tr><td>Profile details you enter (display name, bio, category, location, website, contact email, whether you accept contact requests)</td><td>Displaying your public profile as you set it</td><td>Performance of a contract</td></tr>
                </table>
                <p>Google sign-in gives us only your basic profile (name, email, picture). It does not give us access to your YouTube channel; that is a separate, explicit step described in 3.4.</p>

                <h3>3.3 Listed creators (profiles added by others)</h3>
                <p>Anyone may add a creator by name, platform and handle. This creates a public profile containing information that is already public on the platform, or that the person adding the profile entered. Where the platform offers a public lookup (YouTube, X), we retrieve the public display name, avatar and audience count. Such profiles are clearly marked "Public info only" and contain no non-public data.</p>
                <p><strong>Legal basis:</strong> legitimate interest (Art. 6(1)(f)): operating a public directory of professional creators, limited to information those creators have already made public in that professional capacity. We have balanced this against your interests: we only show professional, already-public information; we never show contact details submitted by third parties; and you can object at any time (section 9), after which we remove the profile. Because the data comes from public sources and not from you, this policy serves as the information required by Art. 14 GDPR.</p>

                <h3>3.4 Connected creators (you claimed a profile)</h3>
                <p>To claim a profile you sign in with the social account itself through the platform's official authorisation flow (OAuth). You choose what to grant on the platform's consent screen; we request read-only access. The platform then provides, and we store:</p>
                <table>
                    <tr><th>Data</th><th>Purpose</th><th>Legal basis</th></tr>
                    <tr><td>Your account ID and handle on that platform</td><td>Verifying that the account you signed in with is the one on the profile</td><td>Performance of a contract (the claim you requested)</td></tr>
                    <tr><td>Access token and, where the platform issues one, refresh token; granted scopes; token expiry</td><td>Retrieving your data now and on the daily refresh</td><td>Performance of a contract</td></tr>
                    <tr><td>Your recent content (titles, links, thumbnails, publish dates) and its analytics: views, watch time, retention, reach, likes, comments, shares, saves, profile and link clicks, daily view counts</td><td>Showing verified performance on your public profile and computing the derived figures (medians, averages, rates, curves)</td><td>Performance of a contract</td></tr>
                    <tr><td>Audience breakdowns where the platform provides them: age ranges, gender, country, city, device type, follower vs. non-follower reach (aggregated percentages only, never individual viewers)</td><td>Showing who your audience is on your public profile</td><td>Performance of a contract</td></tr>
                    <tr><td>Historic snapshots of the above at each refresh</td><td>Showing how your numbers develop over time</td><td>Performance of a contract</td></tr>
                </table>
                <p>Tokens are stored encrypted at rest and are never exposed to your browser or to anyone else. We refresh your data about once a day and whenever you press "Sync now". You cannot edit the retrieved numbers, and neither can we; that is what makes them "verified".</p>
                <p><strong>Publication.</strong> By connecting an account you instruct us to publish the retrieved figures on your public profile, where they may be viewed by anyone and indexed by search engines. You can stop this at any time by disconnecting (section 7).</p>

                <h3>3.5 People who send a contact request</h3>
                <table>
                    <tr><th>Data</th><th>Purpose</th><th>Legal basis</th></tr>
                    <tr><td>Name, email address, company (optional), subject, message, IP address, time</td><td>Delivering your message to the creator so they can reply to you; rate limiting and abuse prevention</td><td>Performance of a contract (delivering the message you asked us to deliver); legitimate interest for abuse prevention</td></tr>
                </table>
                <p>The creator receives your name, email address, company, subject and message. For unclaimed profiles we hold the message until the creator claims the profile; we never forward it to an email address entered by a third party unless that address has been confirmed by the creator.</p>
            </section>

            <section>
                <h2>4. Google and YouTube</h2>
                <p>The Service uses YouTube API Services. By connecting a YouTube channel you also agree to the <a href="https://www.youtube.com/t/terms" class="underline underline-offset-2">YouTube Terms of Service</a>, and the <a href="https://policies.google.com/privacy" class="underline underline-offset-2">Google Privacy Policy</a> applies to Google's handling of your data.</p>
                <p>Our use of information received from Google APIs adheres to the <a href="https://developers.google.com/terms/api-services-user-data-policy" class="underline underline-offset-2">Google API Services User Data Policy</a>, including the Limited Use requirements. Specifically, we only use Google user data to provide and improve the features described in this policy; we do not transfer it to third parties except as needed to provide those features, to comply with the law, or as part of a merger or acquisition with prior notice to you; we do not use it for advertising; and no human reads it except with your consent, for security purposes, to comply with the law, or in aggregated, anonymised form.</p>
                <p>We store YouTube data as described in section 3.4, refresh it at least daily so that it never reflects more than roughly 24 hours of staleness, and delete it when you disconnect, when you delete your account, or on request. You can revoke our access at any time at <a href="https://myaccount.google.com/permissions" class="underline underline-offset-2">myaccount.google.com/permissions</a>.</p>
            </section>

            <section>
                <h2>5. Instagram and X</h2>
                <p>Instagram data is retrieved through the Instagram API with Instagram Login (Meta Platforms Ireland Ltd.); X data through the X API (X Corp.). Their privacy policies apply to their handling of your data. You can revoke our access on Instagram under Settings → Website permissions → Apps and websites, and on X under Settings → Security and account access → Apps and sessions. If you revoke access, our next refresh fails and the profile shows "Needs reconnection" until you disconnect or reconnect; retrieved data is retained until you disconnect or ask us to delete it. To have Instagram-derived data deleted, disconnect the account or email us (section 7).</p>
            </section>

            <section>
                <h2>6. Who receives data</h2>
                <ul>
                    <li><strong>The public.</strong> Creator profiles, including verified figures and audience breakdowns, are public and may be indexed by search engines. Email addresses, tokens, contact requests and account details are never public.</li>
                    <li><strong>Processors.</strong> Our hosting provider (servers, database, backups) and our email delivery provider, both under data processing agreements; Datafast for analytics (section 8). Processors act on our instructions only.</li>
                    <li><strong>Data sources.</strong> Google, Meta and X provide data to us; we do not send them data other than the API requests needed to retrieve yours.</li>
                    <li><strong>Authorities.</strong> When required by law or a binding order.</li>
                    <li><strong>Successors.</strong> If the Service is transferred to another operator, data goes with it under the same commitments, with notice to account holders.</li>
                </ul>
                <p>We do not sell, rent or trade personal data, and we do not share it with brands or other users beyond what the public profile shows and what a contact request necessarily contains.</p>
                <p><strong>Transfers outside the EEA.</strong> Some processors and all three platforms process data in the United States. We rely on the EU-US Data Privacy Framework where the recipient is certified and otherwise on the European Commission's Standard Contractual Clauses.</p>
            </section>

            <section>
                <h2>7. Disconnecting and deletion</h2>
                <p><strong>Disconnect a platform</strong> from <em>Connected accounts</em> on your dashboard. This immediately and irreversibly destroys the stored tokens and deletes all content, analytics, audience data and historic snapshots retrieved for that account. Your public profile (name, handle, last known audience count) remains listed, exactly as if a visitor had added it. Use <em>Remove</em> on the same page to take the platform off your profile as well, or untick <em>Show my profile in the directory</em> under <em>Profile</em> to take the whole profile out of the directory.</p>
                <p><strong>Delete your account</strong> with <em>Delete my account</em> under <em>Profile</em> on your dashboard. This immediately disconnects all platforms as above and deletes your profile, contact requests and login. You can also email <a href="mailto:{{ config('app.legal.contact') }}" class="underline underline-offset-2">{{ config('app.legal.contact') }}</a> from the address on the account and we do the same for you, and confirm, within 30 days and normally much sooner. Backups are overwritten within a further 30 days.</p>
                <p><strong>Remove a profile someone else added</strong> about you: claim it and untick <em>Show my profile in the directory</em>, or email us with the profile link; we remove it within 30 days and normally much sooner.</p>
            </section>

            <section id="cookies">
                <h2>8. Cookies</h2>
                <table>
                    <tr><th>Cookie</th><th>Set by</th><th>Purpose</th><th>Lifetime</th></tr>
                    <tr><td>Session and CSRF cookies</td><td>Us</td><td>Strictly necessary: sign-in state, form protection</td><td>Session, or 2 hours of inactivity</td></tr>
                    <tr><td>Datafast identifier</td><td>Datafast (datafa.st)</td><td>Analytics: distinguishing new from returning visitors for aggregated statistics. Not used for advertising or cross-site tracking. Only set after you choose "Allow" in the cookie banner.</td><td>Set by Datafast; see their <a href="https://datafa.st/privacy" class="underline underline-offset-2">privacy policy</a></td></tr>
                    <tr><td>cookie-consent (browser storage)</td><td>Us</td><td>Remembering your cookie choice so we don't ask again</td><td>Until you clear site data</td></tr>
                </table>
                <p>The analytics cookie is set only after you allow it in the banner. Choosing "No thanks" keeps the Service fully usable. To change your mind later, use "Cookie settings" in the footer; the banner appears again and your new choice replaces the old one. Withdrawing consent stops the analytics cookie from then on; statistics already collected are aggregated and cannot be traced back to you. You can also delete cookies in your browser at any time; you will be signed out if you delete the session cookie.</p>
            </section>

            <section>
                <h2>9. Your rights</h2>
                <p>Under the GDPR you have the right to access the personal data we hold about you, to have it corrected or erased, to restrict or object to its processing, to receive the data you provided in a portable format, and, where processing is based on legitimate interest, to object on grounds relating to your particular situation. If you object to a profile listed under section 3.3 we will remove it unless we can demonstrate compelling legitimate grounds, which for a directory listing we do not expect to do.</p>
                <p>Exercise any right by emailing <a href="mailto:{{ config('app.legal.contact') }}" class="underline underline-offset-2">{{ config('app.legal.contact') }}</a>. We may ask you to confirm your identity, for instance by writing from the address on your account. We respond within one month, extendable by two months for complex requests, in which case we tell you. You may also lodge a complaint with the Dutch Data Protection Authority (Autoriteit Persoonsgegevens, <a href="https://autoriteitpersoonsgegevens.nl" class="underline underline-offset-2">autoriteitpersoonsgegevens.nl</a>).</p>
                <p>We do not make automated decisions with legal or similarly significant effects, and we do not profile individuals. Rankings on the Service order public profiles by published figures; they are not decisions about you.</p>
            </section>

            <section>
                <h2>10. Retention</h2>
                <table>
                    <tr><th>Data</th><th>Kept for</th></tr>
                    <tr><td>Server logs</td><td>30 days</td></tr>
                    <tr><td>Account data</td><td>Until you delete the account</td></tr>
                    <tr><td>Connected-account data (tokens, content, analytics, audience, snapshots)</td><td>While connected; deleted on disconnect</td></tr>
                    <tr><td>Listed-creator public profiles</td><td>Until removed by the creator, on objection, or by us</td></tr>
                    <tr><td>Contact requests</td><td>While the creator's profile exists, at most 24 months</td></tr>
                    <tr><td>Failed background jobs (may contain an account ID)</td><td>7 days</td></tr>
                    <tr><td>Backups</td><td>30 days rolling</td></tr>
                </table>
            </section>

            <section>
                <h2>11. Security</h2>
                <p>All traffic is encrypted (HTTPS). Platform tokens are encrypted at rest with a key that is not stored in the database. Passwords are hashed. Access to production systems is limited to the people who need it. No system is perfectly secure; if we discover a breach that is likely to affect your rights we will inform you and the Autoriteit Persoonsgegevens as the law requires.</p>
            </section>

            <section>
                <h2>12. Children</h2>
                <p>The Service is not intended for anyone under 16. We do not knowingly create accounts for them; if you believe we hold data about a child, email us and we will delete it.</p>
            </section>

            <section>
                <h2>13. Changes</h2>
                <p>We may update this policy. The version and effective date at the top change when we do. For material changes affecting connected creators we give notice on the dashboard or by email before they take effect. Continued use after the effective date means the updated policy applies.</p>
            </section>
        </div>
    </article>
</x-layouts.app>
