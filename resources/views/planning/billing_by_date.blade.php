<x-app-layout>
    <x-slot name="header">
        <h2 class="print:hidden">{{ __('messages.billing_preparation') }} @monthName($current_month) {{$current_year}}</h2>
    </x-slot>

    <section class="planning-controls print:hidden">
        <x-period-selector :years="$years" :months="$months" :current_year="$current_year" :current_month="$current_month" route="billing" />
    </section>

    <section class="planning-calendar-container">
        @if($monthly_hours == 0)
        <div class="alert alert-warning">
            {{ __('messages.no_hours_logged_this_month') }}
        </div>
        @else
        @foreach($schools as $school => $courses)
        <div class="card-wide">
            <h2 class="school-section-header">{{ $school }}</h2>
            @foreach($courses['courses'] as $course_name => $schedules)
            @php
            $current_group = "";
            @endphp
            <div class="cool-box">
                <h3 class="card-subtitle"> - {{ $course_name }}</h3>
                <ul class="flex-list">
                    @foreach($schedules['schedule'] as $planning_id => $schedule)
                    @if($current_group != $schedule['group'])
                    @php $current_group = $schedule['group']; @endphp
                    <li class="group-title">{{ $current_group }}</li>
                    @endif
                    <li class="ml-4">
                        @if(Auth::user()->getMode() == "Edit")
                        <a class="nav-link" href="{{route('planning.edit',$planning_id, 'billing')}}">
                            @endif
                            {{ \Carbon\Carbon::parse($schedule['begin'])->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($schedule['end'])->format('H:i') }}
                            <span class="status-indicator {{ $schedule['duration'] != $schedules['duration'] ? 'text-danger' : 'text-success' }}">
                                ({{ number_format($schedule['duration'], 1) }} h)
                            </span>
                            @if(Auth::user()->getMode() == "Edit")
                        </a>
                        @endif
                        {{ $schedule['bill'] }}
                    </li>
                    @endforeach
                </ul>
                <div class="total-line">
                    <span>{{ __('messages.time_worked') }}: {{ number_format($schedules['hours'], 2) }} {{ __('messages.hours') }}</span>
                    <span>{{ __('messages.total') }}: {{ number_format($schedules['gain'], 2) }} €</span>
                </div>
            </div>
            @endforeach

            <div class="total-line border-t pt-4 mt-4">
                <span>{{ __('messages.total_time_worked') }}: {{ number_format($courses['hours'], 2) }} {{ __('messages.hours') }}</span>
                <span>{{ __('messages.school_total') }}: {{ number_format($courses['gain'], 2) }} €</span>

                <div class="header-actions">
                    <form action="{{route('billing.setBill')}}" class="nav-form" method="post">
                        @csrf
                        <input type="hidden" name="school_id" value="{{$courses['school_id']}}">
                        <input type="hidden" name="course_id" value="{{$schedules['course_id']}}">
                        <input type="hidden" name="month" value="{{$current_month}}">
                        <input type="hidden" name="year" value="{{$current_year}}">
                        <label for="invoice_id">{{ __('messages.bill') }}:</label>
                        <select name="invoice_id" id="invoice_id" class="form-input">
                            @foreach ($bills as $bill)
                            <option value="{{$bill->id}}">{{$bill->id}}</option>
                            @endforeach
                        </select>
                        <x-button-secondary type="submit">{{ __('messages.save') }}</x-button-secondary>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        <div class="planning-summary">
            <div class="planning-summary-item">
                {{ __('messages.time_worked') }} = {{ number_format($monthly_hours, 2) }} {{ __('messages.hours') }}
            </div>
            <div class="planning-summary-item">
                {{ __('messages.monthly_gain') }} = {{ number_format($monthly_gain, 2) }} €
            </div>
            <div class="planning-summary-item">
                {{ __('messages.hour_rate') }} = @if ($monthly_hours > 0) {{ number_format($monthly_gain/$monthly_hours, 2) }} @else 0 @endif €
            </div>
        </div>
        @endif
    </section>
</x-app-layout>