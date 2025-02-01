<x-app-layout>
    <x-slot name="header" class="print:hidden">
        <h2 class="print:hidden font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Preparation') }} @monthName($current_month) {{$current_year}}
        </h2>
    </x-slot>

    <section  class="section-box">
        <div id="calPeriod">
            <x-period-selector :years=$years :months=$months current_year={{$current_year}} current_month={{$current_month}} route="billing"/>
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
            @isset($courses['courses'])
            @foreach($courses['courses'] as $course_name => $schedules)
                @php
                $current_group = "";
                @endphp
                <h2 class="font-bold text-gray-800 p-2 bg-blue-200 mt-4"> - {{$course_name}}</h2>
                <ul>
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
            @endisset

            @empty($schedules['course_id'])
                @php
                    $schedules['course_id'] = 0;
                @endphp
            @endempty

        </div>
            <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-green-100">
                <div class="mx-4">
                    Total Time worked = {{$courses['hours']}} hours
                </div>
                <div class="mx-4">
                    School Total = {{number_format($courses['gain'],2)}} €
                </div>
                <form action="{{route('billing.setBill')}}" class="inline" method="post">
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
        @endforeach
    </section>

        <section  class="section-box">
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