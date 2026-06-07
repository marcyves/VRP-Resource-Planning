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
        ['icon' => 'clock', 'label' => __('messages.time_worked'), 'value' => $monthly_hours . ' ' . __('messages.hours'), 'variant' => 'info'],
        ['icon' => 'wallet', 'label' => __('messages.monthly_gain'), 'value' => number_format($monthly_gain, 2, ',', ' ') . ' € HT', 'variant' => 'success'],
        ['icon' => 'chart', 'label' => __('messages.hour_rate'), 'value' => ($monthly_hours == 0 ? '0' : number_format($monthly_gain / $monthly_hours, 2, ',', ' ')) . ' €/h', 'variant' => 'accent'],
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

    <x-confirm-delete-modal
        name="confirm-planning-delete"
        store="planningDelete"
        entity="session"
        :hints="[
            ['field' => 'date', 'label' => __('messages.date')],
            ['field' => 'label', 'plain' => true],
        ]"
    />
</x-app-layout>