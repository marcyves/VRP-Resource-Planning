<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.landing_traffic') }}</h2>
    </x-slot>

    <p class="form-hint">{{ __('messages.landing_traffic_intro') }} {{ __('messages.landing_traffic_timezone', ['tz' => $displayTimezone]) }}</p>

    <x-kpi-grid :items="[
        ['icon' => 'chart', 'label' => __('messages.landing_traffic_visits'), 'value' => (string) $visitCount, 'variant' => 'info'],
        ['icon' => 'users', 'label' => __('messages.landing_traffic_unique'), 'value' => (string) $uniqueCount, 'variant' => 'accent'],
        ['icon' => 'person', 'label' => __('messages.landing_traffic_returning_kpi'), 'value' => (string) $returningCount, 'variant' => 'success'],
        ['icon' => 'arrow-up', 'label' => __('messages.landing_traffic_cta_kpi'), 'value' => (string) $ctaCount, 'variant' => 'warning'],
    ]" />

    <div class="landing-traffic-panels">
        <section class="landing-traffic-chart school-panel" aria-labelledby="landing-traffic-device-heading">
            <div class="school-panel__box">
                <header class="school-panel__header">
                    <h3 id="landing-traffic-device-heading" class="school-panel__title">{{ __('messages.landing_traffic_device_split') }}</h3>
                </header>
                @if ($visitCount === 0)
                    <p role="status"><x-empty-state icon="chart">{{ __('messages.landing_traffic_empty') }}</x-empty-state></p>
                @else
                    @php
                        $desktopStop = $desktopPercent;
                        $tabletStop = $desktopPercent + $tabletPercent;
                        $gradient = 'var(--landing-traffic-desktop) 0% '.$desktopStop.'%, var(--landing-traffic-tablet) '.$desktopStop.'% '.$tabletStop.'%, var(--landing-traffic-mobile) '.$tabletStop.'% 100%';
                    @endphp
                    <figure class="charts landing-traffic-chart__figure">
                        <div class="charts__content">
                            <div
                                class="pie"
                                style="background-image: conic-gradient(from 30deg, {!! $gradient !!});"
                                role="img"
                                aria-label="{{ __('messages.landing_traffic_device_split') }}"
                            ></div>
                            <div class="chart-legend" role="list" aria-label="{{ __('messages.landing_traffic_device_split') }}">
                                <div class="chart-legend__item" role="listitem">
                                    <span class="chart-legend__swatch landing-traffic-swatch landing-traffic-swatch--desktop" aria-hidden="true"></span>
                                    <span class="chart-legend__school">{{ __('messages.landing_traffic_device_desktop') }}</span>
                                    <span class="chart-legend__amount">
                                        {{ $desktopCount }}
                                        <span class="chart-legend__percent">{{ number_format($desktopPercent, 1, ',', ' ') }} %</span>
                                    </span>
                                </div>
                                <div class="chart-legend__item" role="listitem">
                                    <span class="chart-legend__swatch landing-traffic-swatch landing-traffic-swatch--tablet" aria-hidden="true"></span>
                                    <span class="chart-legend__school">{{ __('messages.landing_traffic_device_tablet') }}</span>
                                    <span class="chart-legend__amount">
                                        {{ $tabletCount }}
                                        <span class="chart-legend__percent">{{ number_format($tabletPercent, 1, ',', ' ') }} %</span>
                                    </span>
                                </div>
                                <div class="chart-legend__item" role="listitem">
                                    <span class="chart-legend__swatch landing-traffic-swatch landing-traffic-swatch--mobile" aria-hidden="true"></span>
                                    <span class="chart-legend__school">{{ __('messages.landing_traffic_device_mobile') }}</span>
                                    <span class="chart-legend__amount">
                                        {{ $mobileCount }}
                                        <span class="chart-legend__percent">{{ number_format($mobilePercent, 1, ',', ' ') }} %</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </figure>
                @endif
            </div>
        </section>

        <section class="landing-traffic-tops school-panel" aria-labelledby="landing-traffic-tops-heading">
            <div class="school-panel__box">
                <header class="school-panel__header">
                    <h3 id="landing-traffic-tops-heading" class="school-panel__title">{{ __('messages.landing_traffic_tops') }}</h3>
                </header>
                <div class="landing-traffic-tops__grid">
                    <div>
                        <h4 class="landing-traffic-tops__label">{{ __('messages.landing_traffic_top_referers') }}</h4>
                        @include('landing-traffic.partials-top', ['rows' => $topReferers])
                    </div>
                    <div>
                        <h4 class="landing-traffic-tops__label">{{ __('messages.landing_traffic_top_countries') }}</h4>
                        @include('landing-traffic.partials-top', ['rows' => $topCountries])
                    </div>
                    <div>
                        <h4 class="landing-traffic-tops__label">{{ __('messages.landing_traffic_top_locales') }}</h4>
                        @include('landing-traffic.partials-top', ['rows' => $topLocales])
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="landing-traffic-filters school-panel" aria-labelledby="landing-traffic-filters-heading">
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 id="landing-traffic-filters-heading" class="school-panel__title">{{ __('messages.landing_traffic_filters') }}</h3>
            </header>
            <form action="{{ $formAction }}" method="get" class="landing-traffic-filter-form">
                <div class="form-group">
                    <label for="landing-traffic-from">{{ __('messages.login_stats_from') }}</label>
                    <x-text-input id="landing-traffic-from" type="date" name="from" :value="$filters['from']" />
                </div>
                <div class="form-group">
                    <label for="landing-traffic-to">{{ __('messages.login_stats_to') }}</label>
                    <x-text-input id="landing-traffic-to" type="date" name="to" :value="$filters['to']" />
                </div>
                <div class="form-group">
                    <label for="landing-traffic-locale">{{ __('messages.landing_traffic_locale') }}</label>
                    <select id="landing-traffic-locale" name="locale">
                        <option value="all" @selected($filters['locale'] === 'all')>{{ __('messages.landing_traffic_all') }}</option>
                        <option value="fr" @selected($filters['locale'] === 'fr')>FR</option>
                        <option value="en" @selected($filters['locale'] === 'en')>EN</option>
                        <option value="it" @selected($filters['locale'] === 'it')>IT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="landing-traffic-device">{{ __('messages.landing_traffic_device') }}</label>
                    <select id="landing-traffic-device" name="device">
                        <option value="all" @selected($filters['device'] === 'all')>{{ __('messages.landing_traffic_all') }}</option>
                        <option value="desktop" @selected($filters['device'] === 'desktop')>{{ __('messages.landing_traffic_device_desktop') }}</option>
                        <option value="tablet" @selected($filters['device'] === 'tablet')>{{ __('messages.landing_traffic_device_tablet') }}</option>
                        <option value="mobile" @selected($filters['device'] === 'mobile')>{{ __('messages.landing_traffic_device_mobile') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="landing-traffic-page">{{ __('messages.landing_traffic_page') }}</label>
                    <select id="landing-traffic-page" name="page">
                        <option value="all" @selected($filters['page'] === 'all')>{{ __('messages.landing_traffic_all') }}</option>
                        <option value="landing" @selected($filters['page'] === 'landing')>{{ __('messages.landing_traffic_page_landing') }}</option>
                        <option value="login" @selected($filters['page'] === 'login')>{{ __('messages.landing_traffic_page_login') }}</option>
                        <option value="register" @selected($filters['page'] === 'register')>{{ __('messages.landing_traffic_page_register') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="landing-traffic-referer">{{ __('messages.landing_traffic_referer_host') }}</label>
                    <x-text-input id="landing-traffic-referer" type="search" name="referer_host" :value="$filters['referer_host']" :placeholder="__('messages.landing_traffic_referer_placeholder')" />
                </div>
                <div class="form-group">
                    <label for="landing-traffic-utm">{{ __('messages.landing_traffic_utm') }}</label>
                    <x-text-input id="landing-traffic-utm" type="search" name="utm" :value="$filters['utm']" :placeholder="__('messages.landing_traffic_utm_placeholder')" />
                </div>
                <div class="landing-traffic-filter-form__actions">
                    <x-button-primary>{{ __('messages.login_stats_apply') }}</x-button-primary>
                    <a href="{{ $formAction }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
                </div>
            </form>
        </div>
    </section>

    <section class="landing-traffic-table" aria-labelledby="landing-traffic-table-heading">
        <h3 id="landing-traffic-table-heading" class="landing-traffic-table__title">{{ __('messages.landing_traffic_table') }}</h3>
        @if ($visits->isEmpty())
            <p role="status"><x-empty-state icon="chart">{{ __('messages.landing_traffic_empty') }}</x-empty-state></p>
        @else
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="date">{{ __('messages.landing_traffic_datetime') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_locale') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_device') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_referer') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_keywords') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_utm') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_country') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_visitor') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_cta') }}</th>
                            <th scope="col">{{ __('messages.landing_traffic_path') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visits as $visit)
                            <tr>
                                <td class="date">{{ $visit->visitedAtDisplay() }}</td>
                                <td>{{ strtoupper((string) $visit->locale) }}</td>
                                <td>{{ $visit->deviceLabel() }}</td>
                                <td>
                                    @if ($visit->referer_host)
                                        <span title="{{ $visit->referer_url }}">{{ $visit->referer_host }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $visit->keywords ?: '—' }}</td>
                                <td>{{ $visit->utmSummary() }}</td>
                                <td>{{ $visit->countryLabel() }}</td>
                                <td>{{ $visit->visitorLabel() }}</td>
                                <td>{{ $visit->ctaLabel() }}</td>
                                <td>
                                    <code>{{ $visit->landing_path }}@if ($visit->landing_query)?{{ $visit->landing_query }}@endif</code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-container">
                {{ $visits->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
