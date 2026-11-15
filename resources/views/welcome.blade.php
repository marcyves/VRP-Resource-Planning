<x-marketing-layout>
    <section class="marketing-hero" aria-labelledby="landing-title">
        <div class="marketing-hero__content">
            <h1 id="landing-title" class="marketing-hero__title">{{ __('messages.landing_title') }}</h1>
            <p class="marketing-hero__lead">{{ __('messages.landing_lead') }}</p>

            <p class="marketing-hero__trust">{{ __('messages.landing_trust_line') }}</p>

            <div class="marketing-hero__actions">
                <a href="{{ route('account-request.create') }}" class="btn btn-primary marketing-hero__cta" data-landing-cta="register">
                    {{ __('messages.landing_trial_cta') }}
                </a>
                <a href="{{ route('login') }}" class="btn btn-secondary marketing-hero__cta" data-landing-cta="login">
                    {{ __('messages.login') }}
                </a>
                <a href="{{ route('marketing.product') }}" class="btn btn-secondary marketing-hero__cta">
                    {{ __('messages.landing_discover_features') }}
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
                fetchpriority="high"
            >
        </div>
    </section>

    <section class="marketing-compare" aria-labelledby="compare-title">
        <header class="marketing-section-header">
            <h2 id="compare-title">{{ __('messages.landing_compare_title') }}</h2>
            <p>{{ __('messages.landing_compare_lead') }}</p>
        </header>

        <div class="marketing-compare__grid">
            <article class="marketing-compare__card marketing-compare__card--before">
                <h3>{{ __('messages.landing_compare_before_title') }}</h3>
                <ul>
                    <li>{{ __('messages.landing_compare_before_1') }}</li>
                    <li>{{ __('messages.landing_compare_before_2') }}</li>
                    <li>{{ __('messages.landing_compare_before_3') }}</li>
                    <li>{{ __('messages.landing_compare_before_4') }}</li>
                </ul>
            </article>
            <article class="marketing-compare__card marketing-compare__card--after">
                <h3>{{ __('messages.landing_compare_after_title') }}</h3>
                <ul>
                    <li>{{ __('messages.landing_compare_after_1') }}</li>
                    <li>{{ __('messages.landing_compare_after_2') }}</li>
                    <li>{{ __('messages.landing_compare_after_3') }}</li>
                    <li>{{ __('messages.landing_compare_after_4') }}</li>
                </ul>
            </article>
        </div>
    </section>

    <section class="marketing-steps" aria-labelledby="steps-title">
        <header class="marketing-section-header">
            <h2 id="steps-title">{{ __('messages.landing_steps_title') }}</h2>
            <p>{{ __('messages.landing_steps_lead') }}</p>
        </header>

        <ol class="marketing-steps__grid">
            @foreach ([
                ['icon' => 'calendar-range', 'title' => 'landing_step_1_title', 'text' => 'landing_step_1_text'],
                ['icon' => 'wallet', 'title' => 'landing_step_2_title', 'text' => 'landing_step_2_text'],
                ['icon' => 'coins', 'title' => 'landing_step_3_title', 'text' => 'landing_step_3_text'],
            ] as $index => $step)
                <li class="marketing-step-card">
                    <span class="marketing-step-card__index" aria-hidden="true">{{ $index + 1 }}</span>
                    <span class="marketing-feature-card__icon" aria-hidden="true">
                        <x-module-tab-icon :name="$step['icon']" />
                    </span>
                    <h3>{{ __('messages.' . $step['title']) }}</h3>
                    <p>{{ __('messages.' . $step['text']) }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    @include('marketing.partials.screens')

    <section id="fonctionnalites" class="marketing-features" aria-labelledby="features-title">
        <header class="marketing-section-header">
            <h2 id="features-title">{{ __('messages.landing_features_title') }}</h2>
            <p>{{ __('messages.landing_features_lead') }}</p>
        </header>

        @include('marketing.partials.feature-grid')

        <p class="marketing-section-follow">
            <a href="{{ route('marketing.product') }}" class="marketing-text-link">{{ __('messages.landing_features_more') }}</a>
        </p>
    </section>

    <section class="marketing-sectors" aria-labelledby="sectors-title">
        <header class="marketing-section-header">
            <h2 id="sectors-title">{{ __('messages.landing_sectors_title') }}</h2>
            <p>{{ __('messages.landing_sectors_lead') }}</p>
        </header>

        <ul class="marketing-sectors__list">
            <li>{{ __('messages.landing_sector_education') }}</li>
            <li>{{ __('messages.landing_sector_consulting') }}</li>
            <li>{{ __('messages.landing_sector_medical') }}</li>
        </ul>
    </section>

    <section class="marketing-personas" aria-labelledby="personas-title">
        <header class="marketing-section-header">
            <h2 id="personas-title">{{ __('messages.landing_personas_title') }}</h2>
            <p>{{ __('messages.landing_personas_lead') }}</p>
        </header>

        <ul class="marketing-personas__grid">
            @foreach ([
                ['icon' => 'school', 'title' => 'landing_persona_trainer_title', 'text' => 'landing_persona_trainer_text'],
                ['icon' => 'person', 'title' => 'landing_persona_consultant_title', 'text' => 'landing_persona_consultant_text'],
                ['icon' => 'users', 'title' => 'landing_persona_practice_title', 'text' => 'landing_persona_practice_text'],
            ] as $persona)
                <li class="marketing-feature-card">
                    <span class="marketing-feature-card__icon" aria-hidden="true">
                        <x-module-tab-icon :name="$persona['icon']" />
                    </span>
                    <h3>{{ __('messages.' . $persona['title']) }}</h3>
                    <p>{{ __('messages.' . $persona['text']) }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section id="tarifs" class="marketing-pricing" aria-labelledby="pricing-title">
        <header class="marketing-section-header">
            <h2 id="pricing-title">{{ __('messages.landing_pricing_title') }}</h2>
            <p>{{ __('messages.landing_pricing_lead') }}</p>
        </header>

        @include('marketing.partials.pricing-offer')

        <p class="marketing-section-follow">
            <a href="{{ route('marketing.pricing') }}" class="marketing-text-link" data-landing-cta="pricing">{{ __('messages.landing_pricing_more') }}</a>
        </p>
    </section>

    <section id="a-propos" class="marketing-about" aria-labelledby="about-title">
        <header class="marketing-section-header">
            <h2 id="about-title">{{ __('messages.landing_about_title') }}</h2>
            <p>{{ __('messages.landing_about_lead') }}</p>
        </header>
        <p class="marketing-section-follow">
            <a href="{{ route('marketing.about') }}" class="marketing-text-link">{{ __('messages.landing_about_more') }}</a>
        </p>
    </section>

    @include('marketing.partials.cta-band')
</x-marketing-layout>
