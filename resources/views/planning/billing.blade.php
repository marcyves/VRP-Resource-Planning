<x-app-layout>
    <x-slot name="header" class="print:hidden">
        <h2 class="print:hidden font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Preparation') }} {{$current_month}}/{{$current_year}}
        </h2>
    </x-slot>

    <section  class="nice-page">
        <!-- (A) PERIOD SELECTOR -->
        <div id="calPeriod">
            <form class="inline-flex" id="calYear" action="{{route('planning.billing')}}" method="get">
                @csrf
                <select id="current_year" name="current_year" class="rounded-md mt-4 py-0 pl-2 pr-8" onchange="this.form.submit()">
                    @foreach ($years as $year)
                        <option value="{{$year->year}}" @if($current_year == $year->year)selected @endif>{{$year->year}}</option>
                    @endforeach                
                </select>
            </form>

            <form class="inline-flex" action="{{route('planning.billing')}}" method="get">
                @csrf
                <select id="current_month" name="current_month" onchange="this.form.submit()">
                    @foreach ($months as $index => $month)
                        <option value="{{$index}}" @if($index==$current_month-1) selected @endif>{{$month}}</option>                    
                    @endforeach
                </select>
            </form>
        </div>

    @if($monthly_hours == 0)
        <div class="font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 p-4 bg-red-100">
            No hours logged this month
        </div>
    </section>
    @else
        @foreach($schools as $school => $courses)
            <div class="font-bold text-gray-800 bg-green-100 p-2 mb-2 flex flex-col justify-between border border-gray-300 rounded-md ">
                <h2 class="inline ml-2 pt-2">{{$school}}</h2>
            @foreach($courses['courses'] as $course_name => $schedules)
                <h2 class="font-bold text-gray-800 p-2 bg-blue-200 mt-4"> - {{$course_name}}</h2>
                <ul>
                    @php
                    $current_group = "";
                    @endphp
                @foreach($schedules['schedule'] as $planning_id => $schedule)
                    @if($current_group != $schedule['group'])
                    @php
                        $current_group = $schedule['group'];
                    @endphp
                    <h3 class="font-semibold text-gray-800 ml-4">{{$current_group}}</h3>
                    @endif
                    <li class="ml-8">
                    @if(Auth::user()->getMode() == "Edit")
                        <a class="text-blue-600" href="{{route('planning.edit',$planning_id, 'billing')}}">
                    @endif    
                    {{date_format(date_create($schedule['begin']),'d/m/Y H:i')}}-{{date_format(date_create($schedule['end']),'H:i')}} 
                    <span class="  
                    @if ($schedule['duration']!=$schedules['duration'])
                    text-red-400
                    @else
                    text-green-400
                    @endif
                    ">
                    ({{number_format($schedule['duration'],1)}} h)</span>
                    @if(Auth::user()->getMode() == "Edit")
                        </a>
                    @endif
                    {{$schedule['bill']}}
                    </li>
                @endforeach
                </ul>

                <div class="flex flex-row justify-between font-semibold text-gray-600 mt-2 py-2 bg-blue-200">
                    <div class="mx-4 pt-2">
                        Time worked = {{$schedules['hours']}} hours
                    </div>
                    <div class="mx-4 pt-2">
                        Total = {{number_format($schedules['gain'],2)}} €
                    </div>
                </div>
            @endforeach
</div>
            <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-green-100">
                <div class="mx-4">
                    Total Time worked = {{$courses['hours']}} hours
                </div>
                <div class="mx-4">
                    School Total = {{number_format($courses['gain'],2)}} €
                </div>
                <form action="{{route('planning.setBill')}}" class="inline" method="post">
                    @csrf
                    <input type="hidden" name="school_id" value="{{$courses['school_id']}}">
                    <input type="hidden" name="course_id" value="{{$schedules['course_id']}}">
                    <input type="hidden" name="month" value="{{$current_month}}">
                    <input type="hidden" name="year" value="{{$current_year}}">
                    <label for="bill_id">Bill:</label>
                    <select name="bill_id" id="bill_id"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach ($bills as $bill)
                        <option value="{{$bill->id}}">{{$bill->id}}</option>
                    @endforeach
                    </select>
                    <input type="submit" value="Save"
                    class="border border-gray-400 bg-white rounded-md px-4 mr-4">
                    </form>
            </div>
            </section>
        @endforeach
        <section  class="nice-page">
        <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 py-4 bg-gray-200">
            <div class="mx-4">
                Time worked = {{$monthly_hours}} hours
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