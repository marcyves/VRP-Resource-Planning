<x-app-layout>
    <x-slot name="header" class="print:hidden">
        <h2 class="print:hidden font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Preparation') }} {{$current_month}}/{{$current_year}}
        </h2>
    </x-slot>

    @if($monthly_hours == 0)
    <x-nice-box color="white">
    <div class="flex flex-row font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 p-4 bg-red-100">
        No hours logged this month
    </div>
    </x-nice-box>
    @else
    <x-nice-box color="white">
        @php
            $current_school = "";
            $current_course = "";
            $current_group = "";
            $school_time = 0;
            $school_total = 0;
            $group_time = 0;
            $sub_total = 0;
        @endphp
        @foreach ($planning as $event)
            @if($current_school != $event->school_name)
                @if($current_course != "")
                </ul>
                <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-blue-100">
                    <div class="mx-4">
                        Time worked = {{$group_time}} hours
                    </div>
                    <div class="mx-4">
                        Total = {{number_format($sub_total,2)}} €
                    </div>
                </div> 
                @endif

                @if($current_school != "")
                    </ul>
                    <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-green-100">
                        <div class="mx-4">
                            Total Time worked = {{$school_time}} hours
                        </div>
                        <div class="mx-4">
                            School Total = {{number_format($school_total,2)}} €
                        </div>
                    </div>
                    </div>
                    @php
                        $school_total = 0;
                        $school_time = 0;
                    @endphp 
                @endif
                <div class="p-2 m-2 border border-gray-400 rounded-md">
                <div class="font-bold text-gray-800 bg-green-100 p-2 mb-2 flex justify-between">
                    <h2 class="inline ml-2 pt-2">{{$event->school_name}}</h2>
                    <form action="" class="inline">
                    <label for="bill_id">Bill:</label>
                    <input type="text" size="10" name="bill_id" id="bill_id"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <label for="bill_date">Date:</label>
                    <input type="date" name="bill_date" id="bill_date"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <input type="submit" value="Save"
                    class="border border-gray-400 bg-white rounded-md px-4 mr-4">
                    </form>
                </div>
                <ul>
                @php
                    $current_school = $event->school_name;
                    $current_course = "";
                @endphp
            @endif

            @if($current_course != $event->course_name)
                @if($current_course != "")
                </ul>
                <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-blue-100">
                    <div class="mx-4">
                        Time worked = {{$group_time}} hours
                    </div>
                    <div class="mx-4">
                        Total = {{number_format($sub_total,2)}} €
                    </div>
                </div>  
                @endif
                @php
                    $current_course = $event->course_name;
                    $sub_total = 0;
                    $group_time = 0;
                @endphp
                <h2 class="font-bold text-gray-800 p-2 bg-blue-100 mt-4">{{$current_course}}</h2>
                <ul>
            @endif

            @if($current_group != $event->group_name)
                @php
                    $current_group = $event->group_name;
                @endphp
                <h3 class="font-semibold text-gray-800 ml-4">{{$current_group}}</h3>
            @endif
            @php
                $end   = strtotime($event->end);
                $begin = strtotime($event->begin);
                $duration = intval(($end - $begin)/60)/60;
            @endphp
            @if(Auth::user()->getMode() == "Edit")
            <a class="text-blue-600" href="{{route('planning.edit',$event->id, 'billing')}}">
            @endif
            <li class="ml-8">{{date_format(date_create($event->begin),'d/m/Y H:i')}}-{{date_format(date_create($event->end),'H:i')}} 
            <span class="  
                @if ($duration!=$event->session_length)
                text-red-400
                @else
                text-green-400
                @endif
                ">
                ({{number_format($duration,1)}} h)</span>
            </li>
            @if(Auth::user()->getMode() == "Edit")
            </a>
            @endif
            @php
                $sub_total += $duration*$event->rate;
                $school_total += $duration*$event->rate;
                $group_time += $duration;
                $school_time += $duration;
            @endphp
        @endforeach

        <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-blue-100">
            <div class="mx-4">
                Time worked = {{$group_time}} hours
            </div>
            <div class="mx-4">
                Total = {{number_format($sub_total,2)}} €
            </div>
        </div>  
        <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-green-100">
            <div class="mx-4">
                Total Time worked = {{$school_time}} hours
            </div>
            <div class="mx-4">
                School Total = {{number_format($school_total,2)}} €
            </div>
        </div>
        </div>
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
    </x-nice-box>
    @endif
</x-app-layout>