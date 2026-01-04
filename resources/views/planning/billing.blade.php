<x-app-layout>
    <x-slot name="header" class="print:hidden">
        <h2 class="print:hidden header-title">
            {{ __('Billing Preparation') }} @monthName($current_month) {{$current_year}}
        </h2>
    </x-slot>

    <section>
        <div id="calPeriod" class="glass-background">
            <x-period-selector :years=$years :months=$months current_year={{$current_year}} current_month={{$current_month}} route="billing" />
        </div>
        @if($monthly_hours == 0)
        <div class="glass-background alert">
            No hours logged this month
        </div>
    </section>
    @else
    @foreach($schools as $school => $courses)
    <div class="card-wide glass-background">
        <h2>{{$school}}</h2>
        @foreach($courses['courses'] as $course_id => $schedules)
        @php
        $current_group = "";
        @endphp
        <div class="cool-box">
            <h3> - {{$schedules['course_name']}}</h2>
                <ul>
                    @foreach($schedules['schedule'] as $planning_id => $schedule)
                    @if($current_group != $schedule['group'])
                    @php
                    $current_group = $schedule['group'];
                    @endphp
                    <h4 class="font-semibold text-gray-800 ml-4">{{$current_group}}</h4>
                    @endif
                    <li class="ml-8">
                        @if(Auth::user()->getMode() == "Edit")
                        <a class="text-blue-600" href="{{route('planning.edit',$planning_id, 'billing')}}">
                            @endif
                            {{date_format(date_create($schedule['begin']),'d/m/Y H:i')}}-{{date_format(date_create($schedule['end']),'H:i')}}
                            <span class="  
                    @if ($schedule['duration']!=$schedules['duration'])
                        red
                    @else
                        green
                    @endif
                    ">
                                @if($schedule['billable_rate'] != 1)
                                {{@number_format($schedule['billable_rate'],2)}}
                                @endif
                                ({{number_format($schedule['duration'],1)}} h)</span>
                            @if(Auth::user()->getMode() == "Edit")
                        </a>
                        @endif
                        {{$schedule['bill']}}
                    </li>
                    @endforeach
                </ul>
                <div class="total-line">
                    <div>
                        Time worked = {{number_format($schedules['hours'],2)}} hours
                    </div>
                    <div>
                        Total = {{number_format($schedules['gain'],2)}} € HT / {{number_format($schedules['gain']*1.2,2)}} € TTC
                    </div>
                </div>
        </div>
        @endforeach

        <div class="total-line">
            <div>
                Total Time worked = {{number_format($courses['hours'],2)}} hours
            </div>
            <div>
                School Total = {{number_format($courses['gain'],2)}} € HT / {{number_format($courses['gain']*1.2,2)}} € TTC
            </div>

            @if($schedule['bill'] != "")
            <div class="mx-4 text-green-600">
                Invoice already assigned
            </div>
            @else
            <form action="{{route('billing.setBill')}}" class="inline" method="post">
                @csrf
                <input type="hidden" name="school_id" value="{{$courses['school_id']}}">
                <input type="hidden" name="course_id" value="{{$course_id}}">
                <input type="hidden" name="month" value="{{$current_month}}">
                <input type="hidden" name="year" value="{{$current_year}}">
                <label for="invoice_id">Assign:</label>
                <select name="invoice_id" id="invoice_id"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach ($bills as $bill)
                    <option value="{{$bill->id}}">{{$bill->id}}</option>
                    @endforeach
                </select>
                <input type="submit" value="Save"
                    class="border border-gray-400 bg-white rounded-md px-4 mr-4">
            </form>
            <form action="{{route('invoice.create')}}" class="inline" method="get">
                @csrf
                <input type="hidden" name="school_id" value="{{$courses['school_id']}}">
                <input type="hidden" name="course_id" value="{{$course_id}}">
                <input type="hidden" name="month" value="{{$current_month}}">
                <input type="hidden" name="year" value="{{$current_year}}">
                <input type="date" name="bill_date" id="bill_date" value="{{date('Y-m-d')}}">
                <input type="hidden" name="cmd" value="detailed">
                <input type="submit" value="Create" class="border border-gray-400 bg-white rounded-md px-4 mr-4">
            </form>
            @endif
        </div>
    </div>

    @endforeach
    </section>

    <section>
        <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 py-4 bg-gray-200">
            <div class="mx-4">
                Time worked = {{number_format($monthly_hours,2)}} hours
            </div>
            <div class="mx-4">
                Monthly gain = {{number_format($monthly_gain,2)}} €
            </div>
            <div class="mx-4">
                Average Rate = {{number_format($monthly_gain/$monthly_hours,2)}} €
            </div>
        </div>
    </section>

    @endif
</x-app-layout>