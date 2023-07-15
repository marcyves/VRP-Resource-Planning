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
        @foreach ($schools as $school)
        <x-advanced-course-table school="{{$school->name}}" school_id="{{$school->id}}">
            @php
             $total_time = 0;   
             $total_budget = 0;   
            @endphp
            @foreach ($courses as $course)
            @if($course->school_id==$school->id)
            @php
             $total_time += $course->session_length*$course->sessions;   
             $total_budget += $course->rate*$course->session_length*$course->sessions;   
            @endphp
            <tr class="border-b">
                <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{$course->name}}</th>
                <td class="px-4 py-3">{{$course->year}}</td>
                <td class="px-4 py-3">{{$course->semester}}</td>
                <td class="px-4 py-3">{{$course->sessions}}</td>
                <td class="px-4 py-3">{{$course->session_length}}</td>
                <td class="px-4 py-3">{{$course->session_length*$course->sessions}}</td>
                <td class="px-4 py-3">{{$course->rate}}</td>
                <td class="px-4 py-3">{{$course->rate*$course->session_length*$course->sessions}}</td>
                <td class="px-4 py-3 flex items-center justify-end">
                    <form action="{{route('course.edit', $course->id)}}" method="get">
                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            Edit
                        </button>    
                    </form>
                    <form action="{{route('course.destroy', $course->id)}}" method="post">
                        @csrf
                        @method('delete')
                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            Delete
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
                <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">Total</th>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3">{{$total_time}}</td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3">{{$total_budget}}</td>
                <td class="px-4 py-3 flex items-center justify-end">
                </td>
            </tr>
        </x-advanced-course-table>
    @endforeach
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-3"></th>
                <th scope="col" class="px-4 py-3"></th>
                <th scope="col" class="px-4 py-3"></th>
                <th scope="col" class="px-4 py-3"></th>
                <th scope="col" class="px-4 py-3"></th>
                <th scope="col" class="px-4 py-3">Total time</th>
                <th scope="col" class="px-4 py-3"></th>
                <th scope="col" class="px-4 py-3">Gain</th>
                <th scope="col" class="px-4 py-3">
                    <span class="sr-only">Actions</span>
                </th>
            </tr>
        </thead>
        <tbody>
    <tr class="border-b">
        <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">Total</th>
        <td class="px-4 py-3"></td>
        <td class="px-4 py-3"></td>
        <td class="px-4 py-3"></td>
        <td class="px-4 py-3"></td>
        <td class="px-4 py-3">{{$gross_total_time}}</td>
        <td class="px-4 py-3"></td>
        <td class="px-4 py-3">{{$gross_total_budget}}</td>
        <td class="px-4 py-3"></td>
    </tr>
        </tbody>
    </table>

    </x-nice-box>

</x-app-layout>
