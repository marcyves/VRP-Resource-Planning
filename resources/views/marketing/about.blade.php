<x-marketing-layout :title="$title">
    <header class="marketing-page-intro">
        <h1>{{ __('messages.landing_about_title') }}</h1>
        <p>{{ __('messages.landing_about_lead') }}</p>
    </header>

    <div class="marketing-prose">
        <section aria-labelledby="about-product">
            <h2 id="about-product">{{ __('messages.landing_about_product_title') }}</h2>
            <p>{{ __('messages.landing_about_product_text') }}</p>
        </section>

        <section aria-labelledby="about-editor">
            <h2 id="about-editor">{{ __('messages.landing_about_editor_title') }}</h2>
            <p>{{ __('messages.landing_about_editor_text') }}</p>
        </section>

        <section aria-labelledby="about-licence">
            <h2 id="about-licence">{{ __('messages.landing_about_licence_title') }}</h2>
            <p>{{ __('messages.landing_about_licence_text') }}</p>
        </section>
    </div>

    @include('marketing.partials.cta-band')
</x-marketing-layout>
