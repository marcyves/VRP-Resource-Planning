<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.schools_list') }} @if($current_year !== 'all'){{ $current_year }}@endif</h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <ul class="resource-grid">
            @php
            $total_amount = 0;
            $total_unbilled_amount = 0;
            $total_unbilled_hours = 0;
            $amounts = [];
            @endphp
            @foreach ($schools as $school)
            @php
            $amount = $school->amount ?? 0;
            $unbilledAmount = $school->unbilled_amount ?? 0;
            $unbilledHours = $school->unbilled_hours ?? 0;
            $total_amount += $amount;
            $total_unbilled_amount = ($total_unbilled_amount ?? 0) + $unbilledAmount;
            $total_unbilled_hours = ($total_unbilled_hours ?? 0) + $unbilledHours;
            $amounts[] = $amount;
            @endphp
            <li>
                <x-school-header :school_name="$school->name" :school_id="$school->id" />
                <dl class="school-stats">
                    <div class="school-stat school-stat--invoiced">
                        <dt class="school-stat__label">{{ __('messages.invoiced_amount_ttc') }}</dt>
                        <dd class="school-stat__value">@money($amount)</dd>
                    </div>
                    <div class="school-stat school-stat--unbilled">
                        <dt class="school-stat__label">{{ __('messages.unbilled') }}</dt>
                        <dd class="school-stat__value">
                            <span>@money($unbilledAmount) HT</span>
                            <span class="school-stat__sep" aria-hidden="true">·</span>
                            <span>{{ number_format($unbilledHours, 1, ',', ' ') }} h</span>
                        </dd>
                    </div>
                </dl>
            </li>
            @endforeach
        </ul>
    </section>

    <section>
        <dl class="school-stats-totals">
            <div class="school-stat school-stat--invoiced">
                <dt class="school-stat__label">{{ __('messages.invoiced_amount_ttc') }}</dt>
                <dd class="school-stat__value">@money($total_amount)</dd>
            </div>
            <div class="school-stat school-stat--unbilled">
                <dt class="school-stat__label">{{ __('messages.unbilled') }}</dt>
                <dd class="school-stat__value">
                    <span>@money($total_unbilled_amount) HT</span>
                    <span class="school-stat__sep" aria-hidden="true">·</span>
                    <span>{{ number_format($total_unbilled_hours, 1, ',', ' ') }} h</span>
                </dd>
            </div>
        </dl>
    </section>

    @if($total_amount > 0)
    <section
        class="school-panel"
        x-data="{
            open: true,
            chartType: localStorage.getItem('school-chart-type') || 'pie'
        }"
        x-init="$watch('chartType', value => localStorage.setItem('school-chart-type', value))"
        aria-labelledby="school-chart-heading"
    >
        @php
        $current_percent = 0;
        $gradient_parts = [];
        $chart_items = [];
        foreach($schools as $index => $school) {
        $amount = $school->amount ?? 0;
        if ($amount <= 0) {
        continue;
        }
        $percent = ($amount / $total_amount) * 100;
        $next_percent = $current_percent + $percent;
        $color_index = count($chart_items) % 9;
        $gradient_parts[] = "var(--c{$color_index}) {$current_percent}% {$next_percent}%";
        $chart_items[] = [
            'name' => html_entity_decode($school->name),
            'amount' => $amount,
            'color_index' => $color_index,
            'percent' => $percent,
            'percent_label' => number_format($percent, 1, ',', ' '),
        ];
        $current_percent = $next_percent;
        }
        $gradient_str = implode(',', $gradient_parts);
        @endphp
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 id="school-chart-heading" class="school-panel__title">{{ __('messages.invoices_by_school') }}</h3>
                <x-panel-toggle controls="school-chart-panel" />
            </header>

            <div id="school-chart-panel" x-show="open" x-transition>
                <figure class="charts">
                    <nav class="chart-type-tabs" aria-label="{{ __('messages.chart_type_label') }}">
                        <button
                            type="button"
                            class="chart-type-tabs__btn"
                            :class="{ 'chart-type-tabs__btn--active': chartType === 'pie' }"
                            :aria-pressed="chartType === 'pie'"
                            @click="chartType = 'pie'"
                        >{{ __('messages.chart_type_pie') }}</button>
                        <button
                            type="button"
                            class="chart-type-tabs__btn"
                            :class="{ 'chart-type-tabs__btn--active': chartType === 'histogram' }"
                            :aria-pressed="chartType === 'histogram'"
                            @click="chartType = 'histogram'"
                        >{{ __('messages.chart_type_histogram') }}</button>
                    </nav>

                    <div class="charts__content" x-show="chartType === 'pie'" x-cloak>
                        <div class="pie" style="background-image: conic-gradient(from 30deg, {!! $gradient_str !!});"></div>
                        <div class="chart-legend" role="list" aria-label="{{ __('messages.invoices_by_school') }}">
                            @foreach ($chart_items as $item)
                            <div class="chart-legend__item" role="listitem">
                                <span class="chart-legend__swatch" style="background-color: var(--c{{ $item['color_index'] }});" aria-hidden="true"></span>
                                <span class="chart-legend__school">{{ $item['name'] }}</span>
                                <span class="chart-legend__amount">
                                    @money($item['amount'])
                                    <span class="chart-legend__percent">{{ $item['percent_label'] }} %</span>
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="school-invoices-histogram" x-show="chartType === 'histogram'" x-cloak aria-label="{{ __('messages.invoices_by_school') }}">
                        <div class="school-invoices-histogram__plot">
                            @foreach ($chart_items as $item)
                            <div class="school-invoices-histogram__school">
                                <div class="school-invoices-histogram__bars">
                                    <div class="school-invoices-histogram__stack">
                                        <span class="school-invoices-histogram__amount">
                                            @money($item['amount'])
                                            <span class="school-invoices-histogram__percent">{{ $item['percent_label'] }} %</span>
                                        </span>
                                        <span
                                            class="school-invoices-histogram__bar"
                                            style="height: {{ max($item['percent'], 2) }}%; background-color: var(--c{{ $item['color_index'] }});"
                                            title="{{ $item['name'] }}: @money($item['amount']) ({{ $item['percent_label'] }} %)"
                                        ></span>
                                    </div>
                                </div>
                                <span class="school-invoices-histogram__label" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </figure>
            </div>
        </div>
    </section>
    @endif

    @if(Auth::user()->getMode() == "Edit")
    <section class="school-panel" x-data="{ open: true }">
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 class="school-panel__title">{{ __('messages.school_create') }}</h3>
                <x-panel-toggle controls="school-create-panel" />
            </header>

            <div id="school-create-panel" x-show="open" x-transition>
            <form action="{{route('school.store')}}" method="post" class="school-create-form nice-form nice-form--embedded">
            @csrf

            <div class="school-create-form__row school-create-form__row--name-code">
                <div class="school-form-input school-form-input--name">
                    <x-text-input type="text" name="name" id="name" placeholder="{{ __('messages.name') }}" value="{{old('name')}}" required />
                </div>
                <div class="school-form-input school-form-input--code">
                    <x-text-input type="text" name="code" id="code" placeholder="{{ __('messages.code') }}" value="{{old('code')}}" />
                </div>
            </div>

            <div class="school-create-form__row">
                <div class="school-form-input school-form-input--full">
                    <x-text-input type="text" name="address" id="address" placeholder="{{ __('messages.address') }}" value="{{old('address')}}" />
                </div>
            </div>

            <div class="school-create-form__row school-create-form__row--zip-city">
                <div class="school-form-input school-form-input--zip">
                    <x-text-input type="text" name="zip" id="zip" placeholder="{{ __('messages.zip') }}" value="{{old('zip')}}" />
                </div>
                <div class="school-form-input school-form-input--city">
                    <x-text-input type="text" name="city" id="city" placeholder="{{ __('messages.city') }}" value="{{old('city')}}" />
                </div>
            </div>

            <div class="school-create-form__row school-create-form__row--country">
                <div class="school-form-input school-form-input--country">
                    <x-text-input type="text" name="country" id="country" placeholder="{{ __('messages.country') }}" value="{{old('country', 'France')}}" />
                </div>
            </div>

            <div class="school-create-form__row school-create-form__row--submit">
                <x-button-primary>{{ __('messages.school_create') }}</x-button-primary>
            </div>
        </form>
            </div>
        </div>
    </section>
    @endif

    @if($inactiveSchools->isNotEmpty())
    <section class="school-panel schools-inactive-section" x-data="{ open: false }" aria-labelledby="schools-inactive-heading">
        <div class="school-panel__box">
            <header class="school-panel__header">
            <h3 id="schools-inactive-heading" class="school-panel__title schools-inactive-header">{{ __('messages.schools_empty_list') }}</h3>
            <x-panel-toggle controls="schools-inactive-panel" />
            </header>

            <div id="schools-inactive-panel" x-show="open" x-transition>
        <ul class="resource-grid resource-grid--inactive">
            @foreach ($inactiveSchools as $school)
            <li>
                <x-school-header :school_name="$school->name" :school_id="$school->id" />
                <p class="school-inactive-label">{{ __('messages.school_no_course') }}</p>
            </li>
            @endforeach
            </ul>
            </div>
        </div>
    </section>
    @endif
</x-app-layout>
