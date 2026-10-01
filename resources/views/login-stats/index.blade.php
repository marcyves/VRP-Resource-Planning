<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.login_stats') }}</h2>
    </x-slot>

    <p class="form-hint">{{ __('messages.login_stats_intro') }}</p>

    <x-kpi-grid :items="[
        ['icon' => 'shield', 'label' => __('messages.login_stats_total'), 'value' => (string) $totalCount, 'variant' => 'info'],
        ['icon' => 'person', 'label' => __('messages.login_stats_success'), 'value' => (string) $successCount, 'variant' => 'success'],
        ['icon' => 'logout', 'label' => __('messages.login_stats_failed'), 'value' => (string) $failedCount, 'variant' => 'warning'],
        ['icon' => 'users', 'label' => __('messages.login_stats_unique'), 'value' => (string) $uniqueUsernames, 'variant' => 'total'],
    ]" />

    <section class="login-stats-chart school-panel" aria-labelledby="login-stats-chart-heading">
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 id="login-stats-chart-heading" class="school-panel__title">{{ __('messages.login_stats_chart_title') }}</h3>
            </header>
            @if ($totalCount === 0)
                <p role="status">{{ __('messages.login_stats_empty') }}</p>
            @else
                @php
                    $successLabel = number_format($successPercent, 1, ',', ' ');
                    $failedLabel = number_format($failedPercent, 1, ',', ' ');
                    $gradient = $successPercent <= 0
                        ? 'var(--login-stats-failed) 0% 100%'
                        : ($failedPercent <= 0
                            ? 'var(--login-stats-success) 0% 100%'
                            : 'var(--login-stats-success) 0% '.$successPercent.'%, var(--login-stats-failed) '.$successPercent.'% 100%');
                @endphp
                <figure class="charts login-stats-chart__figure">
                    <div class="charts__content">
                        <div
                            class="pie"
                            style="background-image: conic-gradient(from 30deg, {!! $gradient !!});"
                            role="img"
                            aria-label="{{ __('messages.login_stats_chart_title') }}"
                        ></div>
                        <div class="chart-legend" role="list" aria-label="{{ __('messages.login_stats_chart_title') }}">
                            <div class="chart-legend__item" role="listitem">
                                <span class="chart-legend__swatch login-stats-swatch login-stats-swatch--success" aria-hidden="true"></span>
                                <span class="chart-legend__school">{{ __('messages.login_stats_success') }}</span>
                                <span class="chart-legend__amount">
                                    {{ $successCount }}
                                    <span class="chart-legend__percent">{{ $successLabel }} %</span>
                                </span>
                            </div>
                            <div class="chart-legend__item" role="listitem">
                                <span class="chart-legend__swatch login-stats-swatch login-stats-swatch--failed" aria-hidden="true"></span>
                                <span class="chart-legend__school">{{ __('messages.login_stats_failed') }}</span>
                                <span class="chart-legend__amount">
                                    {{ $failedCount }}
                                    <span class="chart-legend__percent">{{ $failedLabel }} %</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </figure>
            @endif
        </div>
    </section>

    <section class="login-stats-filters school-panel" aria-labelledby="login-stats-filters-heading">
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 id="login-stats-filters-heading" class="school-panel__title">{{ __('messages.login_stats_filters') }}</h3>
            </header>
            <form action="{{ $formAction }}" method="get" class="login-stats-filter-form">
                <div class="form-group">
                    <label for="login-stats-outcome">{{ __('messages.login_stats_outcome') }}</label>
                    <select id="login-stats-outcome" name="outcome">
                        <option value="all" @selected($filters['outcome'] === 'all')>{{ __('messages.login_stats_outcome_all') }}</option>
                        <option value="success" @selected($filters['outcome'] === 'success')>{{ __('messages.login_stats_success') }}</option>
                        <option value="failed" @selected($filters['outcome'] === 'failed')>{{ __('messages.login_stats_failed') }}</option>
                    </select>
                </div>
                @if ($isSuperAdmin)
                    <div class="form-group">
                        <label for="login-stats-company">{{ __('messages.login_stats_company') }}</label>
                        <select id="login-stats-company" name="company_id">
                            <option value="" @selected($filters['company_id'] === '')>{{ __('messages.login_stats_company_all') }}</option>
                            <option value="none" @selected($filters['company_id'] === 'none')>{{ __('messages.login_stats_company_none') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected((string) $filters['company_id'] === (string) $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="form-group">
                    <label for="login-stats-from">{{ __('messages.login_stats_from') }}</label>
                    <x-text-input id="login-stats-from" type="date" name="from" :value="$filters['from']" />
                </div>
                <div class="form-group">
                    <label for="login-stats-to">{{ __('messages.login_stats_to') }}</label>
                    <x-text-input id="login-stats-to" type="date" name="to" :value="$filters['to']" />
                </div>
                <div class="form-group">
                    <label for="login-stats-q">{{ __('messages.search') }}</label>
                    <x-text-input id="login-stats-q" type="search" name="q" :value="$filters['q']" :placeholder="__('messages.login_stats_search_placeholder')" />
                </div>
                <div class="login-stats-filter-form__actions">
                    <x-button-primary>{{ __('messages.login_stats_apply') }}</x-button-primary>
                    <a href="{{ $formAction }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
                </div>
            </form>
        </div>
    </section>

    <section class="login-stats-table" aria-labelledby="login-stats-table-heading">
        <h3 id="login-stats-table-heading" class="login-stats-table__title">{{ __('messages.login_stats_table') }}</h3>
        @if ($events->isEmpty())
            <p role="status">{{ __('messages.login_stats_empty') }}</p>
        @else
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="date">{{ __('messages.login_stats_datetime') }}</th>
                            <th scope="col">{{ __('messages.login_stats_username') }}</th>
                            <th scope="col">{{ __('messages.login_stats_ip') }}</th>
                            <th scope="col">{{ __('messages.login_stats_geo') }}</th>
                            <th scope="col">{{ __('messages.login_stats_outcome') }}</th>
                            @if ($isSuperAdmin)
                                <th scope="col">{{ __('messages.login_stats_company') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr>
                                <td class="date">{{ $event->occurred_at?->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i:s') }}</td>
                                <td>{{ $event->username }}</td>
                                <td><code>{{ $event->ip }}</code></td>
                                <td>{{ $event->geoDisplay() }}</td>
                                <td>
                                    <span @class([
                                        'login-stats-outcome',
                                        'login-stats-outcome--success' => $event->success,
                                        'login-stats-outcome--failed' => ! $event->success,
                                    ])>{{ $event->outcomeLabel() }}</span>
                                </td>
                                @if ($isSuperAdmin)
                                    <td>{{ $event->company?->name ?? __('messages.login_stats_company_none') }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-container">
                {{ $events->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
