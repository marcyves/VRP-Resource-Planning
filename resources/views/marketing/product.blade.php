<x-marketing-layout :title="$title">
    <header class="marketing-page-intro">
        <h1>{{ __('messages.landing_product_title') }}</h1>
        <p>{{ __('messages.landing_product_lead') }}</p>
    </header>

    <section class="marketing-modules" aria-labelledby="modules-title">
        <h2 id="modules-title" class="sr-only">{{ __('messages.landing_features_title') }}</h2>

        <ul class="marketing-modules__list">
            @foreach ([
                ['icon' => 'calendar-range', 'title' => 'landing_module_agenda_title', 'text' => 'landing_module_agenda_text'],
                ['icon' => 'grid', 'title' => 'landing_module_activity_title', 'text' => 'landing_module_activity_text'],
                ['icon' => 'wallet', 'title' => 'landing_module_billing_title', 'text' => 'landing_module_billing_text'],
                ['icon' => 'receipt', 'title' => 'landing_module_expenses_title', 'text' => 'landing_module_expenses_text'],
                ['icon' => 'banknote', 'title' => 'landing_module_bank_title', 'text' => 'landing_module_bank_text'],
                ['icon' => 'folder', 'title' => 'landing_module_documents_title', 'text' => 'landing_module_documents_text'],
                ['icon' => 'layers', 'title' => 'landing_module_vocabulary_title', 'text' => 'landing_module_vocabulary_text'],
                ['icon' => 'shield', 'title' => 'landing_module_tenancy_title', 'text' => 'landing_module_tenancy_text'],
            ] as $module)
                <li class="marketing-module">
                    <span class="marketing-feature-card__icon" aria-hidden="true">
                        <x-module-tab-icon :name="$module['icon']" />
                    </span>
                    <div>
                        <h3>{{ __('messages.' . $module['title']) }}</h3>
                        <p>{{ __('messages.' . $module['text']) }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    @include('marketing.partials.screens')

    @include('marketing.partials.cta-band')
</x-marketing-layout>
