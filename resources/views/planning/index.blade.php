<x-app-layout>
    @push('styles')
    @vite(['resources/css/plannings.css', 'resources/css/calendar.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.planning') }} @monthName($current_month) {{$current_year}}</h2>
    </x-slot>

    <section class="planning-calendar-container">
        <!-- (A) PERIOD SELECTOR & CONTROLS -->
        @php
        $firstDay = mktime(0, 0, 0, $current_month, 1, $current_year);
        $numDays = date('t', $firstDay);
        $startDay = date('N', $firstDay);
        $day = 1;
        @endphp

        <div class="planning-controls">
            <x-period-selector :years="$years" :months="$months" :current_year="$current_year" :current_month="$current_month" route="planning" />
            <x-form-select-planning :mode="$mode" :schools="$schools" :courses="$courses" :planning="$planning" :day="$current_day" :month="$current_month" :year="$current_year" />
        </div>

        <!-- (B) CALENDAR -->
        <div id="calWrap" class="glass-background">
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

        <div class="planning-summary">
            <div class="planning-summary-item">
                {{ __('messages.time_worked') }} = {{$monthly_hours}} {{ __('messages.hours') }}
            </div>
            <div class="planning-summary-item">
                {{ __('messages.monthly_gain') }} = {{number_format($monthly_gain,2)}} € HT / {{number_format($monthly_gain*1.2,2)}} € TTC
            </div>
            <div class="planning-summary-item">
                {{ __('messages.hour_rate') }} = @if ($monthly_hours == 0) 0 @else {{number_format($monthly_gain/$monthly_hours,2)}} @endif€
            </div>
        </div>
    </section>
</x-app-layout>