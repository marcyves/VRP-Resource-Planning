<section class="marketing-cta-band" aria-labelledby="cta-title">
    <div class="marketing-cta-band__inner">
        <h2 id="cta-title">{{ __('messages.landing_cta_title') }}</h2>
        <p>{{ __('messages.landing_cta_lead') }}</p>
        <a href="{{ route('account-request.create') }}" class="btn btn-primary" data-landing-cta="register">
            {{ __('messages.landing_request_access') }}
        </a>
    </div>
</section>
