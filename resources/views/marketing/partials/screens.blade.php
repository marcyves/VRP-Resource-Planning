<section id="captures" class="marketing-screens" aria-labelledby="screens-title">
    <header class="marketing-section-header">
        <h2 id="screens-title">{{ __('messages.landing_screens_title') }}</h2>
        <p>{{ __('messages.landing_screens_lead') }}</p>
    </header>

    <figure class="marketing-product-stage" x-data="{ view: 'agenda' }">
        <div
            class="marketing-product-stage__tabs"
            role="tablist"
            aria-label="{{ __('messages.landing_stage_views') }}"
        >
            <button
                type="button"
                class="marketing-product-stage__tab"
                role="tab"
                id="landing-stage-agenda-tab"
                aria-controls="landing-stage-panel"
                :aria-selected="view === 'agenda'"
                :class="{ 'is-active': view === 'agenda' }"
                @click="view = 'agenda'"
            >
                {{ __('messages.landing_stage_agenda') }}
            </button>
            <button
                type="button"
                class="marketing-product-stage__tab"
                role="tab"
                id="landing-stage-treasury-tab"
                aria-controls="landing-stage-panel"
                :aria-selected="view === 'treasury'"
                :class="{ 'is-active': view === 'treasury' }"
                @click="view = 'treasury'"
            >
                {{ __('messages.landing_stage_treasury') }}
            </button>
        </div>

        <div id="landing-stage-panel" class="marketing-product-stage__frame" role="tabpanel" aria-labelledby="landing-stage-agenda-tab">
            <img
                src="{{ asset('images/landing-agenda.jpg') }}"
                alt="{{ __('messages.landing_hero_agenda_alt') }}"
                width="1476"
                height="1228"
                class="marketing-product-stage__image"
                decoding="async"
                :hidden="view !== 'agenda'"
            >
            <img
                src="{{ asset('images/landing-treasury.jpg') }}"
                alt="{{ __('messages.landing_hero_treasury_alt') }}"
                width="1476"
                height="1032"
                class="marketing-product-stage__image"
                decoding="async"
                hidden
                :hidden="view !== 'treasury'"
            >
        </div>
        <figcaption class="marketing-product-stage__caption">{{ __('messages.landing_stage_caption') }}</figcaption>
    </figure>
</section>
