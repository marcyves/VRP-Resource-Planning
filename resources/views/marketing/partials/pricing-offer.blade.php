<article class="marketing-pricing__offer">
    <div class="marketing-pricing__lead">
        <p class="marketing-pricing__plan">{{ __('messages.landing_pricing_plan') }}</p>
        <p class="marketing-pricing__amount">{{ __('messages.landing_pricing_trial_price') }}</p>
        <p class="marketing-pricing__period">{{ __('messages.landing_pricing_trial_label') }}</p>
        <p class="marketing-pricing__follow">
            {{ __('messages.landing_pricing_then') }}
            <strong>{{ __('messages.landing_pricing_monthly_price') }}</strong>
            {{ __('messages.landing_pricing_monthly_label') }}
        </p>
        <a href="{{ route('account-request.create') }}" class="btn btn-primary marketing-pricing__cta" data-landing-cta="register">
            {{ __('messages.landing_pricing_cta') }}
        </a>
    </div>

    <div class="marketing-pricing__details">
        <p class="marketing-pricing__included-label">{{ __('messages.landing_pricing_included') }}</p>
        <ul class="marketing-pricing__included">
            @foreach ([
                'landing_pricing_included_modules',
                'landing_pricing_included_workspace',
                'landing_pricing_included_support',
                'landing_pricing_included_users',
                'landing_pricing_included_locales',
            ] as $item)
                <li>{{ __('messages.' . $item) }}</li>
            @endforeach
        </ul>
    </div>
</article>
