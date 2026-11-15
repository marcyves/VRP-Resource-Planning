<x-marketing-layout :title="$title">
    <header class="marketing-page-intro">
        <h1>{{ __('messages.landing_pricing_title') }}</h1>
        <p>{{ __('messages.landing_pricing_lead') }}</p>
    </header>

    <section class="marketing-pricing marketing-pricing--page" aria-labelledby="pricing-offer-title">
        <h2 id="pricing-offer-title" class="sr-only">{{ __('messages.landing_pricing_plan') }}</h2>
        @include('marketing.partials.pricing-offer')
    </section>

    <section class="marketing-faq" aria-labelledby="faq-title">
        <header class="marketing-section-header">
            <h2 id="faq-title">{{ __('messages.landing_faq_title') }}</h2>
            <p>{{ __('messages.landing_faq_lead') }}</p>
        </header>

        <div class="marketing-faq__list">
            @foreach ([
                ['q' => 'landing_faq_start_q', 'a' => 'landing_faq_start_a'],
                ['q' => 'landing_faq_trial_q', 'a' => 'landing_faq_trial_a'],
                ['q' => 'landing_faq_included_q', 'a' => 'landing_faq_included_a'],
                ['q' => 'landing_faq_users_q', 'a' => 'landing_faq_users_a'],
                ['q' => 'landing_faq_cancel_q', 'a' => 'landing_faq_cancel_a'],
                ['q' => 'landing_faq_data_q', 'a' => 'landing_faq_data_a'],
            ] as $item)
                <details class="marketing-faq__item">
                    <summary>{{ __('messages.' . $item['q']) }}</summary>
                    <p>{{ __('messages.' . $item['a']) }}</p>
                </details>
            @endforeach
        </div>
    </section>

    @include('marketing.partials.cta-band')
</x-marketing-layout>
