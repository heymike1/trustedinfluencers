<x-layouts.app title="Terms of service" description="The terms for using Trusted Influencers: listing creators, claiming profiles, verified figures, acceptable use, liability, Dutch law." :canonical="route('terms')">
    <article class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Terms of service</h1>
        <p class="mt-1 text-sm text-ink-500">Version {{ config('app.legal.version') }}, effective {{ config('app.legal.updated') }}.</p>

        <div class="mt-8 space-y-8 text-ink-700 [&_h2]:text-base [&_h2]:font-semibold [&_h2]:text-ink-950 [&_h2]:mb-2 [&_p]:mt-2 [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mt-1">
            <section>
                <h2>1. Who we are and what these terms cover</h2>
                <p>{{ config('app.name') }} ({{ config('app.url') }}, "the Service") is operated by <strong>{{ config('app.legal.entity') }}</strong>, established in the Netherlands ("we", "us"). Contact: <a href="mailto:{{ config('app.legal.contact') }}" class="underline underline-offset-2">{{ config('app.legal.contact') }}</a>.</p>
                <p>These terms are a binding agreement between you and us for every use of the Service: browsing, adding creators, holding an account, claiming a profile, connecting a social account, or sending a contact request. By using the Service you accept them, together with our <a href="{{ route('privacy') }}" class="underline underline-offset-2">privacy policy</a>, which forms part of this agreement. If you do not accept them, do not use the Service.</p>
                <p>If you use the Service on behalf of a company or agency, you confirm you are authorised to bind it, and "you" includes that entity.</p>
            </section>

            <section>
                <h2>2. The Service</h2>
                <p>The Service is a public directory of creators on YouTube, Instagram and X. Anyone may add a creator. A creator may claim their profile by authenticating with the social account it lists, after which the Service retrieves and publishes figures from that platform's official API ("verified figures"). The Service also lets visitors send contact requests to creators. That is all it does: we are not a party to, and do not broker, negotiate, guarantee or process payment for, any arrangement between a creator and anyone else.</p>
            </section>

            <section>
                <h2>3. Eligibility and accounts</h2>
                <ul>
                    <li>You must be at least 16 years old to hold an account.</li>
                    <li>Provide accurate information and keep it current. You are responsible for everything done under your login and for keeping it confidential. Tell us at once if you suspect misuse.</li>
                    <li>One login may own one creator profile. A profile may have one connected account per platform.</li>
                    <li>We may refuse, suspend or close accounts as described in section 10.</li>
                </ul>
            </section>

            <section>
                <h2>4. Adding creators</h2>
                <p>You may add a creator who has a public YouTube, Instagram or X account and publishes content in a professional or public capacity. When you add someone, you warrant that the information is accurate to the best of your knowledge and that the account is real. You must not add private individuals who are not creators, add fictitious or impersonating accounts, or add anyone in order to harass, defame or expose them. We normalise handles so each account can be listed only once; duplicates that slip through may be merged or removed at our discretion.</p>
                <p>A person who is listed and does not wish to be may have the profile removed as set out in the privacy policy.</p>
            </section>

            <section>
                <h2>5. Claiming a profile and connecting accounts</h2>
                <ul>
                    <li>Only the person who controls a social account may claim its profile, and the sole method of claiming is authenticating with that account through the platform's official authorisation flow. Attempting to claim, or to connect, an account you do not control is a material breach and may be unlawful.</li>
                    <li>By connecting an account you (a) grant us permission to retrieve the data described in the privacy policy through the platform's API, (b) instruct us to publish the retrieved figures and audience breakdowns on your public profile, and (c) confirm that you are entitled to grant this and that doing so does not breach any agreement you have with the platform or a third party.</li>
                    <li>You must comply with the platform's own terms. Connecting YouTube means you also agree to the <a href="https://www.youtube.com/t/terms" class="underline underline-offset-2">YouTube Terms of Service</a>; connecting Instagram or X means agreeing to Meta's and X's terms respectively.</li>
                    <li>We may undo a claim at any time if we have reason to believe it was made by someone other than the account holder, or on the platform's instruction. We may also undo it on request from a person who demonstrates control of the account.</li>
                    <li>You may disconnect any account at any time; the consequences are described in the privacy policy.</li>
                </ul>
            </section>

            <section>
                <h2>6. Verified figures and public information</h2>
                <p>"Verified" has one meaning on the Service: the creator authenticated with their own account and the figures were retrieved from the platform's API. It is <strong>not</strong> an endorsement, a certification, an audit, a guarantee of accuracy, or a guarantee of future performance, and it is not the platform's own verification badge (such as a blue tick or a subscription mark). Neither the creator nor we can edit verified figures.</p>
                <p>Figures are retrieved roughly once a day and reflect the platform's API at that moment. Platforms change what they report and how; API figures may differ from a platform's own dashboard; data may be incomplete, delayed or wrong at source. Information on unclaimed profiles comes from public platform data or from the person who added the profile, and may be inaccurate or out of date. Rankings and derived figures (medians, averages, rates, curves) are computed by us from the retrieved data using the methods described on the Service and are provided for information only.</p>
                <p>You use all figures at your own risk. In particular, nothing on the Service constitutes advice, and you should verify anything on which you intend to rely commercially directly with the creator or the platform.</p>
            </section>

            <section>
                <h2>7. Acceptable use</h2>
                <p>You must not:</p>
                <ul>
                    <li>use the Service for anything unlawful, or in breach of any platform's terms;</li>
                    <li>scrape, crawl, harvest or bulk-download content or data from the Service, or access it by automated means other than a standard search engine crawler respecting robots.txt, without our written permission;</li>
                    <li>use contact requests for spam, unsolicited bulk messaging, phishing or anything other than a genuine enquiry to that creator;</li>
                    <li>attempt to bypass rate limits, authentication, or any security measure, or probe or test the Service's vulnerability without our written consent;</li>
                    <li>impersonate anyone, misrepresent your affiliation, or provide false information;</li>
                    <li>upload or link to malicious code, or interfere with the operation of the Service;</li>
                    <li>resell, sublicense or commercially exploit the Service or its data, including by building a competing database from it;</li>
                    <li>remove or alter any notice, attribution or "public"/"verified" labelling.</li>
                </ul>
            </section>

            <section>
                <h2>8. Content and intellectual property</h2>
                <p><strong>Your content.</strong> You keep all rights in information you submit (profile text, listings, contact requests). You grant us a worldwide, non-exclusive, royalty-free licence to store, display, reproduce and distribute it as needed to operate the Service, for as long as it is on the Service. You warrant that you have the right to submit it and that it does not infringe anyone's rights or the law.</p>
                <p><strong>Platform data.</strong> Content and figures retrieved from a platform remain subject to that platform's terms and the rights of the creator and the platform. We display them as authorised by the creator's connection and by the platform's API terms.</p>
                <p><strong>Our content.</strong> The Service itself, its design, software, text, derived figures, rankings and the compilation of the directory are owned by us or our licensors and protected by copyright and database rights. Nothing in these terms transfers those rights. You may view and share links to public profiles; any other use requires our permission.</p>
                <p><strong>Trade marks.</strong> YouTube, Google, Instagram, Meta and X are trade marks of their respective owners. We are not affiliated with, endorsed by or sponsored by any of them; their names and icons are used only to identify the platforms.</p>
            </section>

            <section>
                <h2>9. Notice and takedown</h2>
                <p>If you believe content on the Service infringes your rights, is unlawful, or concerns you and should be removed, email <a href="mailto:{{ config('app.legal.contact') }}" class="underline underline-offset-2">{{ config('app.legal.contact') }}</a> with the link, what is wrong with it, and how we can contact you. We act on well-founded notices promptly and in any event within the periods required by law, and we may inform the person who submitted the content.</p>
            </section>

            <section>
                <h2>10. Suspension, termination and changes to the Service</h2>
                <p>We may, with or without notice, remove or hide profiles, merge duplicates, undo claims, disconnect accounts, refuse or delete contact requests, and suspend or close accounts, where we reasonably believe something breaches these terms, a platform's terms, the law or the rights of others, or exposes us to liability. We may also modify, restrict or discontinue the Service or any part of it. Where a change or discontinuation materially affects connected creators we will try to give reasonable notice.</p>
                <p>You may stop using the Service and delete your account at any time. Sections 6, 7, 8, 11, 12 and 14 survive termination.</p>
            </section>

            <section>
                <h2>11. Availability and no warranty</h2>
                <p>The Service is provided "as is" and "as available", free of charge. To the fullest extent permitted by law we exclude all warranties, express or implied, including as to accuracy, completeness, fitness for a particular purpose, non-infringement and uninterrupted or error-free operation. We do not warrant that platforms will keep their APIs available or unchanged, and we are not responsible for anything a platform does, including revoking access, changing data, or suspending an account.</p>
            </section>

            <section>
                <h2>12. Liability</h2>
                <p>To the fullest extent permitted by Dutch law:</p>
                <ul>
                    <li>we are not liable for indirect or consequential loss, loss of profit, revenue, business, goodwill, data or opportunity, or for decisions made or deals concluded (or not) on the basis of anything on the Service;</li>
                    <li>we are not liable for the acts, omissions or content of creators, visitors, brands or platforms, or for any dealings between you and them;</li>
                    <li>our total aggregate liability to you for all claims arising out of or in connection with the Service in any twelve-month period is limited to the greater of the amount you paid us for the Service in that period (currently nothing) and EUR 100.</li>
                </ul>
                <p>Nothing in these terms excludes or limits liability for death or personal injury caused by our negligence, for fraud, for intent or gross negligence (opzet of bewuste roekeloosheid) on our part, or for anything else that cannot be excluded or limited under mandatory law. If you are a consumer, nothing in these terms affects your statutory rights under Dutch and EU consumer law.</p>
            </section>

            <section>
                <h2>13. Indemnity</h2>
                <p>If you are not a consumer, you will indemnify us against claims, damages, costs and reasonable legal fees arising from your breach of these terms, your content, or your misuse of the Service, including any claim by a platform or by a person you listed.</p>
            </section>

            <section>
                <h2>14. General</h2>
                <ul>
                    <li><strong>Governing law and jurisdiction.</strong> These terms and any dispute arising from them are governed by Dutch law. Disputes are submitted exclusively to the competent court in the Netherlands, without prejudice to a consumer's right to bring proceedings before the court of their own domicile within the EU, and to the EU online dispute resolution platform at <a href="https://ec.europa.eu/consumers/odr" class="underline underline-offset-2">ec.europa.eu/consumers/odr</a>.</li>
                    <li><strong>Changes to these terms.</strong> We may amend these terms. The version and effective date at the top change when we do; for material changes we give account holders notice on the dashboard or by email before they take effect. If you do not accept a change, stop using the Service before it takes effect.</li>
                    <li><strong>Severability.</strong> If any provision is held invalid or unenforceable, it is replaced by a valid provision that comes as close as possible to its intent, and the rest remains in force.</li>
                    <li><strong>No waiver.</strong> Not enforcing a provision is not a waiver of it.</li>
                    <li><strong>Assignment.</strong> You may not transfer this agreement. We may transfer it to a successor operator of the Service with notice to account holders.</li>
                    <li><strong>Entire agreement.</strong> These terms and the privacy policy are the entire agreement between you and us about the Service and replace any earlier terms.</li>
                    <li><strong>Language.</strong> These terms are in English. If a translation is provided, the English version prevails.</li>
                </ul>
            </section>
        </div>
    </article>
</x-layouts.app>
