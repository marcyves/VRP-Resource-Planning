<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.planning') }} @monthName($current_month) {{$current_year}}</h2>
    </x-slot>

    <x-scheduling-module-tabs />

    <section class="planning-controls">
        <x-period-selector :years="$years" :months="$months" :current_year="$current_year" :current_month="$current_month" route="planning" />
        <x-form-select-planning :mode="$mode" :schools="$schools" :courses="$courses" :planning="$planning" :day="$current_day" :month="$current_month" :year="$current_year" />
    </section>

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
                    <x-planning-day :mode="$mode" :schools="$schools" :courses="$courses" :planning="$planning" :i="$i" :day="$day" :month="$current_month" :year="$current_year" />
                @php $day++; @endphp
                @endfor
            </div>

            @while ($day <= $numDays)
                <div class="calRow">
                @for ($i = 1; $i
                <= 7 && $day <=$numDays; $i++)
                    <x-planning-day :mode="$mode" :schools="$schools" :courses="$courses" :planning="$planning" :i="$i" :day="$day" :month="$current_month" :year="$current_year" />
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
</x-app-layout>