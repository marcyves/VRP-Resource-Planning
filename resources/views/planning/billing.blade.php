<x-app-layout>
    <x-slot name="header" class="print:hidden">
        <h2 class="print:hidden font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Preparation') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        <div class="flex flex-row font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 py-4 bg-gray-200">
            <div class="mx-4">
                {{$current_month}}
            </div>
            <div class="mx-4">
                {{$current_year}}
            </div>
        </div>
        @php
            $current_school = "";
            $current_course = "";
            $current_group = "";
            $school_time = 0;
            $school_total = 0;
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
                <h2 class="font-bold text-gray-800 bg-green-100 p-2 mb-2">{{$event->school_name}}</h2>
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
            <li class="ml-8">{{$event->begin}} ({{number_format($event->session_length,1)}} h)</li>
            @php
                $sub_total += $event->session_length*$event->rate;
                $school_total += $event->session_length*$event->rate;
                $group_time += $event->session_length;
                $school_time += $event->session_length;
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
</x-app-layout>