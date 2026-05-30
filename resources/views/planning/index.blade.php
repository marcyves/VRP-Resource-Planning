<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.planning') }} @monthName($current_month) {{$current_year}}</h2>
    </x-slot>

    <section class="planning-toolbar">
        <x-scheduling-module-tabs />
        <x-period-selector :years="$years" :months="$months" :current_year="$current_year" :current_month="$current_month" route="planning" />
    </section>

    @if (Auth::user()->getMode() == 'Edit' && session('course_id'))
        <form id="planning-create-form" action="{{ route('planning.create.start') }}" method="post" class="hidden">
            @csrf
            <input type="hidden" name="course" value="{{ session('course_id') }}">
        </form>
    @endif

    <x-kpi-grid :items="[
        ['icon' => 'clock', 'label' => __('messages.time_worked'), 'value' => $monthly_hours . ' ' . __('messages.hours')],
        ['icon' => 'wallet', 'label' => __('messages.monthly_gain'), 'value' => number_format($monthly_gain, 2, ',', ' ') . ' € HT'],
        ['icon' => 'chart', 'label' => __('messages.hour_rate'), 'value' => ($monthly_hours == 0 ? '0' : number_format($monthly_gain / $monthly_hours, 2, ',', ' ')) . ' €/h'],
    ]" />

    <section class="planning-calendar-container">
        <!-- (A) PERIOD SELECTOR & CONTROLS -->
        @php
        $firstDay = mktime(0, 0, 0, $current_month, 1, $current_year);
        $numDays = date('t', $firstDay);
        $startDay = date('N', $firstDay);
        $day = 1;
        @endphp

        <!-- (B) CALENDAR -->
        <div id="calWrap">
            <div class="calHead">
                @foreach ($weekdays as $weekday)
                <div class="calCell">{{__($weekday)}}</div>
                @endforeach
            </div>

            <div class="calBody">
                <div class="calRow">
                    @for ($i = 1; $i < $startDay; $i++)
                        <div class="calBlank calCell">
                </div>
                @endfor
                @for ($i = $startDay; $i
                <= 7; $i++)
                    <x-planning-day :planning="$planning" :day="$day" :month="$current_month" :year="$current_year" />
                @php $day++; @endphp
                @endfor
            </div>

            @while ($day <= $numDays)
                <div class="calRow">
                @for ($i = 1; $i
                <= 7 && $day <=$numDays; $i++)
                    <x-planning-day :planning="$planning" :day="$day" :month="$current_month" :year="$current_year" />
                @php $day++; @endphp
                @endfor
                @if($i <= 7)
                    @for ($j=$i; $j <=7; $j++)
                    <div class="calBlank calCell">
        </div>
        @endfor
        @endif
        </div>
        @endwhile
        </div>
        </div>
    </section>

    <x-modal name="confirm-planning-delete" focusable maxWidth="md">
        <div class="profile-modal-form">
            <h2 class="modal-title">{{ __('messages.planning_delete_confirm_title') }}</h2>
            <p class="form-hint" x-show="$store.planningDelete.date">
                <strong>{{ __('messages.date') }} :</strong>
                <span x-text="$store.planningDelete.date"></span>
            </p>
            <p class="form-hint" x-show="$store.planningDelete.label" x-text="$store.planningDelete.label"></p>
            <p class="form-hint">{{ __('messages.planning_delete_confirm_description') }}</p>
            <div class="form-actions">
                <x-button-secondary type="button" x-on:click="$dispatch('close')">
                    {{ __('messages.cancel') }}
                </x-button-secondary>
                <form x-bind:action="$store.planningDelete.url" method="post">
                    @csrf
                    @method('delete')
                    <x-button-danger type="submit">{{ __('messages.delete') }}</x-button-danger>
                </form>
            </div>
        </div>
    </x-modal>
</x-app-layout>