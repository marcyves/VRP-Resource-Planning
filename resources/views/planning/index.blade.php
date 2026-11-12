<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.planning') }} {{ $periodTitle }}</h2>
    </x-slot>

    @php
        $unbilledSchools = $billingSchools->filter(fn (array $school) => ($school['unbilled_sessions'] ?? 0) > 0);
    @endphp
    <p class="page-meta">
        <span>{{ $monthly_hours }} {{ __('messages.hours') }} · {{ number_format($monthly_gain * 1.2, 2, ',', ' ') }} € TTC</span>
        @if ($unbilledSchools->isNotEmpty())
            <a class="planning-unbilled" href="{{ route('billing.prepare') }}">
                {{ __('messages.planning_unbilled_link', [
                    'count' => $unbilledSchools->count(),
                    'schools' => mb_strtolower(__('messages.schools')),
                    'amount' => number_format($unbilledSchools->sum('unbilled_amount_ttc'), 2, ',', ' '),
                ]) }}
            </a>
        @endif
    </p>

    <section class="page-toolbar planning-toolbar">
        <div class="page-toolbar__period planning-toolbar__period">
            <x-planning-view-toggle :view="$planningView" />
            <x-period-selector :years="$years" :months="$months" :current_year="$current_year" :current_month="$current_month" route="planning" />
        </div>
        <div class="page-toolbar__actions">
            <a class="btn btn-secondary" href="{{ route('calendar.index') }}">{{ __('messages.planning_import_calendar') }}</a>
            @if (Auth::user()->getMode() == 'Edit' && session('course_id'))
                <form action="{{ route('planning.create.start') }}" method="post">
                    @csrf
                    <input type="hidden" name="course" value="{{ session('course_id') }}">
                    <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                    <input type="hidden" name="hour" value="8">
                    <input type="hidden" name="minutes" value="0">
                    <button type="submit" class="btn btn-primary">{{ __('messages.planning_new_session') }}</button>
                </form>
            @endif
        </div>
    </section>

    @if (Auth::user()->getMode() == 'Edit' && session('course_id'))
        <form id="planning-create-form" action="{{ route('planning.create.start') }}" method="post" class="hidden">
            @csrf
            <input type="hidden" name="course" value="{{ session('course_id') }}">
            <input type="hidden" name="hour" value="8">
            <input type="hidden" name="minutes" value="0">
        </form>
    @endif

    <section class="planning-calendar-container">
        <!-- (A) PERIOD SELECTOR & CONTROLS -->
        @php
        $firstDay = mktime(0, 0, 0, $current_month, 1, $current_year);
        $numDays = date('t', $firstDay);
        $startDay = date('N', $firstDay);
        $day = 1;
        @endphp

        <!-- (B) CALENDAR -->
        @if ($planningView === 'week')
            <x-planning-week-agenda
                :days="$calendarDays"
                :planning="$planning"
                :current-month="$current_month"
            />
        @else
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
        @endif
    </section>

    <x-planning-delete-dialog />
    <x-planning-duplicate-dialog />
</x-app-layout>