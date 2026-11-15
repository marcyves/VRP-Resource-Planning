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
