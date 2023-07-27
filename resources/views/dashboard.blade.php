<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-nice-box color="grey-400">
        @php
        $gross_total_time = 0;   
        $gross_total_budget = 0;   
        @endphp
        @isset($schools)
            
        @foreach ($schools as $school)
        <x-advanced-course-table school="{{$school->name}}" school_id="{{$school->id}}">
            @php
             $total_time = 0;   
             $total_budget = 0;   
            @endphp
            @foreach ($courses as $course)
                @if($course->school_id==$school->id)
                @php
                $total_time += $course->session_length*$course->sessions*$course->groups_count;   
                $total_budget += $course->rate*$course->session_length*$course->sessions*$course->groups_count;   
                @endphp
                <tr class="border-b">
                    <th scope="row" class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">{{$course->name}}</th>
                    <td class="px-2 py-3 text-center">{{$course->year}}</td>
                    <td class="px-2 py-3 text-center">{{$course->semester}}</td>
                    <td class="px-2 py-3 text-center">{{$course->sessions}}</td>
                    <td class="px-2 py-3 text-center">{{$course->session_length}}</td>
                    <td class="px-2 py-3 text-center">{{$course->session_length*$course->sessions}}</td>
                    <td class="px-2 py-3 text-center">{{$course->groups_count}}</td>
                    <td class="px-2 py-3 text-center">{{$course->groups_count*$course->session_length*$course->sessions}}</td>
                    <td class="px-2 py-3 text-right">@money($course->rate)</td>
                    <td class="px-2 py-3 text-right">@money($course->rate*$course->session_length*$course->sessions*$course->groups_count)</td>
                    <td class="px-2 py-3 flex items-center justify-end">
                        <form action="{{route('course.edit', $course->id)}}" method="get">
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-green-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                Edit
                            </button>    
                        </form>
                        <form action="{{route('course.destroy', $course->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-red-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                Delete
                            </button>    
                        </form>
                        <form action="{{route('course.show', $course->id)}}" method="get">
                            @csrf
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                View
                            </button>    
                        </form>
                    </td>
                </tr>
                @endif                              
            @endforeach

            @php
                $gross_total_time += $total_time;
                $gross_total_budget += $total_budget;
            @endphp
            <tr class="border-b">
                <th scope="row" class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">Total</th>
                <td class="px-2 py-3"></td>
                <td class="px-2 py-3"></td>
                <td class="px-2 py-3"></td>
                <td class="px-2 py-3"></td>
                <td class="px-2 py-3"></td>
                <td class="px-2 py-3"></td>
                <td class="px-2 py-3 text-center">{{$total_time}}</td>
                <td class="px-2 py-3"></td>
                <td class="px-2 py-3 text-center">@money($total_budget)</td>
                <td class="px-2 py-3 flex items-center justify-end">
                </td>
            </tr>
        </x-advanced-course-table>
    @endforeach
    @endisset

    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-600 uppercase bg-gray-50">
            <tr>
                <th scope="col" class="px-2 py-3"></th>
                <th scope="col" class="px-2 py-3"></th>
                <th scope="col" class="px-2 py-3">Total time:</th>
                <th scope="col" class="px-2 py-3">{{$gross_total_time}}</th>
                <th scope="col" class="px-2 py-3">Total Gain:</th>
                <th scope="col" class="px-2 py-3">@money($gross_total_budget)</th>
                <th scope="col" class="px-2 py-3">Hour Rate:</th>
                <th scope="col" class="px-2 py-3">@money($gross_total_budget/$gross_total_time)</th>
                <th scope="col" class="px-2 py-3"></th>
            </tr>
        </thead>
    </table>

    </x-nice-box>

</x-app-layout>
