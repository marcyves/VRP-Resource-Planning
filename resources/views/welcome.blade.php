<x-marketing-layout>
    <section class="marketing-hero" aria-labelledby="landing-title">
        <div class="marketing-hero__content">
            <p class="marketing-hero__eyebrow">{{ __('messages.landing_eyebrow') }}</p>
            <h1 id="landing-title" class="marketing-hero__title">{{ __('messages.landing_title') }}</h1>
            <p class="marketing-hero__lead">{{ __('messages.landing_lead') }}</p>

            <div class="marketing-hero__actions">
                <a href="{{ route('account-request.create') }}" class="button-primary marketing-hero__cta">
                    {{ __('messages.landing_request_access') }}
                </a>
                <a href="{{ route('login') }}" class="button-secondary marketing-hero__cta">
                    {{ __('messages.login') }}
                </a>
            </div>
        </div>

        <div class="marketing-hero__visual" aria-hidden="true">
            <img
                src="{{ asset('images/VRP-login.jpg') }}"
                alt=""
                width="520"
                height="360"
                class="marketing-hero__image"
                decoding="async"
            >
        </div>
    </section>

    <section id="features" class="marketing-features" aria-labelledby="features-title">
        <header class="marketing-section-header">
            <h2 id="features-title">{{ __('messages.landing_features_title') }}</h2>
            <p>{{ __('messages.landing_features_lead') }}</p>
        </header>

        <ul class="marketing-features__grid">
            @foreach ([
                ['icon' => 'calendar-range', 'title' => 'landing_feature_planning_title', 'text' => 'landing_feature_planning_text'],
                ['icon' => 'grid', 'title' => 'landing_feature_workload_title', 'text' => 'landing_feature_workload_text'],
                ['icon' => 'wallet', 'title' => 'landing_feature_billing_title', 'text' => 'landing_feature_billing_text'],
                ['icon' => 'coins', 'title' => 'landing_feature_treasury_title', 'text' => 'landing_feature_treasury_text'],
                ['icon' => 'layers', 'title' => 'landing_feature_profiles_title', 'text' => 'landing_feature_profiles_text'],
                ['icon' => 'building', 'title' => 'landing_feature_secure_title', 'text' => 'landing_feature_secure_text'],
            ] as $feature)
                <li class="marketing-feature-card">
                    <span class="marketing-feature-card__icon" aria-hidden="true">
                        <x-module-tab-icon :name="$feature['icon']" />
                    </span>
                    <h3>{{ __('messages.' . $feature['title']) }}</h3>
                    <p>{{ __('messages.' . $feature['text']) }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="marketing-cta-band" aria-labelledby="cta-title">
        <div class="marketing-cta-band__inner">
            <h2 id="cta-title">{{ __('messages.landing_cta_title') }}</h2>
            <p>{{ __('messages.landing_cta_lead') }}</p>
            <a href="{{ route('account-request.create') }}" class="button-primary">
                {{ __('messages.landing_request_access') }}
            </a>
        </div>
    </section>
</x-marketing-layout>
