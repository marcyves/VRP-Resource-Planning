<x-app-layout>
    @push('styles')
    @vite(['resources/css/plannings.css', 'resources/css/bills.css'])
    @endpush

    <x-slot name="header">
        <h2 class="print:hidden">{{ __('Billing Preparation') }} @monthName($current_month) {{$current_year}}</h2>
    </x-slot>

    <section class="planning-calendar-container">
        <div class="planning-controls glass-background">
            <x-period-selector :years="$years" :months="$months" :current_year="$current_year" :current_month="$current_month" route="billing" />
        </div>

        @if($monthly_hours == 0)
        <div class="alert alert-warning glass-background">
            No hours logged this month
        </div>
        @else
        @foreach($schools as $school => $courses)
        <div class="card-wide glass-background">
            <h2 class="school-section-header">{{ $school }}</h2>
            @foreach($courses['courses'] as $course_id => $schedules)
            @php
            $current_group = "";
            @endphp
            <div class="cool-box">
                <h3 class="card-subtitle"> - {{ $schedules['course_name'] }}</h3>
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
                                @if($schedule['billable_rate'] != 1)
                                ({{ number_format($schedule['billable_rate'], 2) }})
                                @endif
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
                    <span>Time worked: {{ number_format($schedules['hours'], 2) }} hours</span>
                    <span>Total: {{ number_format($schedules['gain'], 2) }} € HT / {{ number_format($schedules['gain']*1.2, 2) }} € TTC</span>
                </div>
            </div>
            @endforeach

            <div class="total-line border-t pt-4 mt-4">
                <span>Total Time worked: {{ number_format($courses['hours'], 2) }} hours</span>
                <span>School Total: {{ number_format($courses['gain'], 2) }} € HT / {{ number_format($courses['gain']*1.2, 2) }} € TTC</span>

                <div class="header-actions">
                    @if(isset($schedule['bill']) && $schedule['bill'] != "")
                    <span class="text-success">Invoice already assigned</span>
                    @else
                    <form action="{{route('billing.setBill')}}" class="nav-form" method="post">
                        @csrf
                        <input type="hidden" name="school_id" value="{{$courses['school_id']}}">
                        <input type="hidden" name="course_id" value="{{$course_id}}">
                        <input type="hidden" name="month" value="{{$current_month}}">
                        <input type="hidden" name="year" value="{{$current_year}}">
                        <label for="invoice_id">Assign:</label>
                        <select name="invoice_id" id="invoice_id" class="form-input">
                            @foreach ($bills as $bill)
                            <option value="{{$bill->id}}">{{$bill->id}}</option>
                            @endforeach
                        </select>
                        <x-button-secondary type="submit">Save</x-button-secondary>
                    </form>
                    <form action="{{route('invoice.create')}}" class="nav-form" method="get">
                        @csrf
                        <input type="hidden" name="school_id" value="{{$courses['school_id']}}">
                        <input type="hidden" name="course_id" value="{{$course_id}}">
                        <input type="hidden" name="month" value="{{$current_month}}">
                        <input type="hidden" name="year" value="{{$current_year}}">
                        <x-text-input type="date" name="bill_date" id="bill_date" value="{{date('Y-m-d')}}" />
                        <input type="hidden" name="cmd" value="detailed">
                        <x-button-primary type="submit">Create</x-button-primary>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
        @endif

        <div class="planning-summary">
            <div class="planning-summary-item">
                Time worked = {{ number_format($monthly_hours, 2) }} hours
            </div>
            <div class="planning-summary-item">
                Monthly gain = {{ number_format($monthly_gain, 2) }} €
            </div>
            <div class="planning-summary-item">
                Average Rate = @if ($monthly_hours > 0) {{ number_format($monthly_gain/$monthly_hours, 2) }} @else 0 @endif €
            </div>
        </div>
    </section>
</x-app-layout>