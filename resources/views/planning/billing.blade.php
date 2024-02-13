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
        @foreach($schools as $school => $courses)
        <x-nice-box color="white">
            <div class="font-bold text-gray-800 bg-green-100 p-2 mb-2 flex justify-between">
                <h2 class="inline ml-2 pt-2">{{$school}}</h2>
            </div>
            @foreach($courses[0] as $course_name => $schedules)
                <h2 class="font-bold text-gray-800 p-2 bg-blue-100 mt-4">{{$course_name}}</h2>
                <ul>
                @foreach($schedules[0] as $planning_id => $schedule)
                    @if(Auth::user()->getMode() == "Edit")
                        <a class="text-blue-600" href="{{route('planning.edit',$planning_id, 'billing')}}">
                    @endif
                    <li class="ml-8">{{date_format(date_create($schedule['begin']),'d/m/Y H:i')}}-{{date_format(date_create($schedule['end']),'H:i')}} 
                    <span class="  
                    @if ($schedule['duration']!=$schedules[3])
                    text-red-400
                    @else
                    text-green-400
                    @endif
                    ">
                    ({{number_format($schedule['duration'],1)}} h)</span>
                    </li>
                @endforeach
                </ul>

                <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-blue-100">
                    <div class="mx-4 pt-2">
                        Time worked = {{$schedules[1]}} hours
                    </div>
                    <div class="mx-4 pt-2">
                        Total = {{number_format($schedules[2],2)}} €
                    </div>
                </div>
            @endforeach
            <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-2 py-2 bg-green-100">
                <div class="mx-4">
                    Total Time worked = {{$courses[1]}} hours
                </div>
                <div class="mx-4">
                    School Total = {{number_format($courses[2],2)}} €
                </div>
                <form action="" class="inline">
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
            </x-nice-box>
        @endforeach
        <x-nice-box color="white">
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