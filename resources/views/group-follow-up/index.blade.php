<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.group_follow_up') }}</h2>
    </x-slot>

    <p class="form-hint">{{ __('messages.group_follow_up_intro', [
        'schools' => __('messages.schools'),
        'groups' => __('messages.groups'),
        'group' => __('messages.group'),
    ]) }}</p>

    <x-kpi-grid :items="[
        ['icon' => 'users', 'label' => __('messages.group_follow_up_roster_total'), 'value' => (string) $totalMembers, 'variant' => 'info'],
        ['icon' => 'person', 'label' => __('messages.group_follow_up_connected'), 'value' => (string) $connectedCount, 'variant' => 'success'],
        ['icon' => 'clock', 'label' => __('messages.group_follow_up_idle'), 'value' => (string) $idleCount, 'variant' => 'warning'],
    ]" />

    <section class="login-stats-chart school-panel" aria-labelledby="group-follow-up-chart-heading">
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 id="group-follow-up-chart-heading" class="school-panel__title">{{ __('messages.group_follow_up_chart_title') }}</h3>
            </header>
            @if ($totalMembers === 0)
                <p role="status">{{ __('messages.group_follow_up_empty_roster') }}</p>
            @else
                @php
                    $connectedLabel = number_format($connectedPercent, 1, ',', ' ');
                    $idleLabel = number_format($idlePercent, 1, ',', ' ');
                    $gradient = $connectedPercent <= 0
                        ? 'var(--login-stats-idle) 0% 100%'
                        : ($idlePercent <= 0
                            ? 'var(--login-stats-success) 0% 100%'
                            : 'var(--login-stats-success) 0% '.$connectedPercent.'%, var(--login-stats-idle) '.$connectedPercent.'% 100%');
                @endphp
                <figure class="charts login-stats-chart__figure">
                    <div class="charts__content">
                        <div
                            class="pie"
                            style="background-image: conic-gradient(from 30deg, {!! $gradient !!});"
                            role="img"
                            aria-label="{{ __('messages.group_follow_up_chart_title') }}"
                        ></div>
                        <div class="chart-legend" role="list" aria-label="{{ __('messages.group_follow_up_chart_title') }}">
                            <div class="chart-legend__item" role="listitem">
                                <span class="chart-legend__swatch login-stats-swatch login-stats-swatch--success" aria-hidden="true"></span>
                                <span class="chart-legend__school">{{ __('messages.group_follow_up_connected') }}</span>
                                <span class="chart-legend__amount">
                                    {{ $connectedCount }}
                                    <span class="chart-legend__percent">{{ $connectedLabel }} %</span>
                                </span>
                            </div>
                            <div class="chart-legend__item" role="listitem">
                                <span class="chart-legend__swatch login-stats-swatch login-stats-swatch--idle" aria-hidden="true"></span>
                                <span class="chart-legend__school">{{ __('messages.group_follow_up_idle') }}</span>
                                <span class="chart-legend__amount">
                                    {{ $idleCount }}
                                    <span class="chart-legend__percent">{{ $idleLabel }} %</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </figure>
            @endif
        </div>
    </section>

    <section class="login-stats-filters school-panel" aria-labelledby="group-follow-up-filters-heading">
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 id="group-follow-up-filters-heading" class="school-panel__title">{{ __('messages.login_stats_filters') }}</h3>
            </header>
            <form action="{{ $formAction }}" method="get" class="login-stats-filter-form">
                <div class="form-group">
                    <label for="group-follow-up-school">{{ __('messages.school') }}</label>
                    <select id="group-follow-up-school" name="school_id">
                        <option value="" @selected($filters['school_id'] === '')>{{ __('messages.group_follow_up_school_all') }}</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" @selected($filters['school_id'] === (string) $school->id)>{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="group-follow-up-from">{{ __('messages.login_stats_from') }}</label>
                    <x-text-input id="group-follow-up-from" type="date" name="from" :value="$filters['from']" />
                </div>
                <div class="form-group">
                    <label for="group-follow-up-to">{{ __('messages.login_stats_to') }}</label>
                    <x-text-input id="group-follow-up-to" type="date" name="to" :value="$filters['to']" />
                </div>
                <div class="form-group">
                    <label for="group-follow-up-q">{{ __('messages.search') }}</label>
                    <x-text-input id="group-follow-up-q" type="search" name="q" :value="$filters['q']" :placeholder="__('messages.group_follow_up_search_placeholder')" />
                </div>
                <div class="login-stats-filter-form__actions">
                    <x-button-primary>{{ __('messages.login_stats_apply') }}</x-button-primary>
                    <a href="{{ $formAction }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
                </div>
            </form>
        </div>
    </section>

    <section class="login-stats-table" aria-labelledby="group-follow-up-roster-heading">
        <h3 id="group-follow-up-roster-heading" class="login-stats-table__title">{{ __('messages.group_follow_up_roster') }}</h3>
        <p class="form-hint">{{ __('messages.group_follow_up_interactions_hint') }}</p>
        @if ($members->isEmpty())
            <p role="status">{{ __('messages.group_follow_up_empty_roster') }}</p>
        @else
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.name') }}</th>
                            <th scope="col">{{ __('messages.login_stats_username') }}</th>
                            <th scope="col">{{ __('messages.group_follow_up_presence') }}</th>
                            <th scope="col" class="date">{{ __('messages.group_follow_up_last_login') }}</th>
                            <th scope="col">{{ __('messages.group_follow_up_success_count') }}</th>
                            <th scope="col">{{ __('messages.group_follow_up_failed_count') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    <span @class([
                                        'login-stats-outcome',
                                        'login-stats-outcome--success' => $member->connected,
                                        'login-stats-outcome--idle' => ! $member->connected,
                                    ])>
                                        {{ $member->connected ? __('messages.group_follow_up_connected') : __('messages.group_follow_up_idle') }}
                                    </span>
                                </td>
                                <td class="date">
                                    @if ($member->last_success_at)
                                        {{ \Illuminate\Support\Carbon::parse($member->last_success_at)->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i') }}
                                    @else
                                        {{ __('messages.group_follow_up_never') }}
                                    @endif
                                </td>
                                <td>{{ $member->success_count }}</td>
                                <td>{{ $member->failed_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="login-stats-table" aria-labelledby="group-follow-up-groups-heading">
        <h3 id="group-follow-up-groups-heading" class="login-stats-table__title">{{ __('messages.group_follow_up_groups', ['groups' => __('messages.groups')]) }}</h3>
        <p class="form-hint">{{ __('messages.group_follow_up_groups_hint', ['groups' => __('messages.groups')]) }}</p>
        @if ($groups->isEmpty())
            <p role="status">{{ __('messages.group_follow_up_empty_groups', ['groups' => __('messages.groups')]) }}</p>
        @else
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.group') }}</th>
                            <th scope="col">{{ __('messages.school') }}</th>
                            <th scope="col">{{ __('messages.course') }}</th>
                            <th scope="col">{{ __('messages.group_follow_up_sessions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $group)
                            @php
                                $courseNames = $group->courses->pluck('name')->unique()->filter()->implode(', ');
                                $schoolNames = $group->courses->map(fn ($course) => $course->school?->name)->unique()->filter()->implode(', ');
                            @endphp
                            <tr>
                                <td>{{ $group->name }}</td>
                                <td>{{ $schoolNames !== '' ? $schoolNames : '—' }}</td>
                                <td>{{ $courseNames !== '' ? $courseNames : '—' }}</td>
                                <td>{{ $group->sessions_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-app-layout>
