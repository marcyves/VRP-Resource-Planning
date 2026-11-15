<x-marketing-layout :title="$title">
    <header class="marketing-page-intro">
        <h1>{{ __('messages.landing_legal_title') }}</h1>
        <p>{{ __('messages.landing_legal_lead') }}</p>
    </header>

    <div class="marketing-prose">
        <section aria-labelledby="legal-publisher">
            <h2 id="legal-publisher">{{ __('messages.landing_legal_publisher_title') }}</h2>
            <p>{{ __('messages.landing_legal_publisher_text') }}</p>
        </section>

        <section aria-labelledby="legal-hosting">
            <h2 id="legal-hosting">{{ __('messages.landing_legal_hosting_title') }}</h2>
            <p>{{ __('messages.landing_legal_hosting_text') }}</p>
        </section>

        <section aria-labelledby="legal-privacy">
            <h2 id="legal-privacy">{{ __('messages.landing_legal_privacy_title') }}</h2>
            <p>{{ __('messages.landing_legal_privacy_text') }}</p>
        </section>

        <section aria-labelledby="legal-ip">
            <h2 id="legal-ip">{{ __('messages.landing_legal_ip_title') }}</h2>
            <p>{{ __('messages.landing_legal_ip_text') }}</p>
        </section>

        <section aria-labelledby="legal-contact">
            <h2 id="legal-contact">{{ __('messages.landing_legal_contact_title') }}</h2>
            <p>{{ __('messages.landing_legal_contact_text') }}</p>
        </section>
    </div>
</x-marketing-layout>
