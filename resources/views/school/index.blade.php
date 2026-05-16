<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.schools_list') }} @if($current_year !== 'all'){{ $current_year }}@endif</h2>
    </x-slot>

    <section>
        <ul class="school-grid">
            @php
            $total_amount = 0;
            $amounts = [];
            @endphp
            @foreach ($schools as $school)
            @php
            $amount = $school->amount ?? 0;
            $total_amount += $amount;
            $amounts[] = $amount;
            @endphp
            <li>
                <x-school-header :school_name="$school->name" :school_id="$school->id" />
                <div class="school-stats">
                    @money($amount) €
                </div>
            </li>
            @endforeach
        </ul>
    </section>

    <section>
        <div class="total-line">
            <span>{{ __('messages.total_invoices') }}:</span>
            <span>@money($total_amount)€</span>
        </div>
    </section>

    @if($total_amount > 0)
    <section>
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
        ];
        $current_percent = $next_percent;
        }
        $gradient_str = implode(',', $gradient_parts);
        @endphp
        <figure class="charts">
            <div class="charts__content">
                <div class="pie" style="background-image: conic-gradient(from 30deg, {!! $gradient_str !!});"></div>
                <div class="chart-legend" role="list" aria-label="{{ __('messages.invoices_by_school') }}">
                    @foreach ($chart_items as $item)
                    <div class="chart-legend__item" role="listitem">
                        <span class="chart-legend__swatch" style="background-color: var(--c{{ $item['color_index'] }});" aria-hidden="true"></span>
                        <span class="chart-legend__school">{{ $item['name'] }}</span>
                        <span class="chart-legend__amount">@money($item['amount']) €</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <figcaption>{{ __('messages.invoices_by_school') }}</figcaption>
        </figure>
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
            <form action="{{route('school.store')}}" method="post" class="school-create-form">
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
        <ul class="school-grid school-grid--inactive">
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
