<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
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
                $current_course = "";
                $current_group = "";
            @endphp
            @foreach ($planning as $event)
                @if($current_course != $event->course_name)
                    @if($current_course != "")
                        </ul>
                        <div class="ml-4 my-4">
                            Total = {{number_format($sub_total,2)}} €
                        </div>
                    </div>  
                    @endif
                    @php
                        $current_course = $event->course_name;
                        $sub_total = 0;
                    @endphp
                    <div class="p-2 m-2 border border-gray-400 rounded-md">
                    <h2 class="font-bold text-gray-800">{{$current_course}}</h2>
                    <ul>
                @endif
                @if($current_group != $event->group_name)
                    @php
                        $current_group = $event->group_name;
                    @endphp
                    <h3 class="font-semibold text-gray-800 ml-4">{{$current_group}}</h3>
                @endif
                <li class="ml-8">{{$event->begin}}</li>
                @php
                    $sub_total += $event->session_length*$event->rate;
                @endphp
            @endforeach  
            <div class="ml-4 my-4">
                Total = {{number_format($sub_total,2)}} €
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