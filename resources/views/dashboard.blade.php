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
            <tr class="border-b dark:border-gray-700">
                <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{$course->name}}</th>
                <td class="px-4 py-3">{{$course->year}}</td>
                <td class="px-4 py-3">{{$course->semester}}</td>
                <td class="px-4 py-3">{{$course->sessions}}</td>
                <td class="px-4 py-3">{{$course->session_length}}</td>
                <td class="px-4 py-3">{{$course->session_length*$course->sessions}}</td>
                <td class="px-4 py-3">{{$course->rate}}</td>
                <td class="px-4 py-3">{{$course->rate*$course->session_length*$course->sessions}}</td>
                <td class="px-4 py-3 flex items-center justify-end">
                    <button id="school-{{$school->id}}-dropdown-button" data-dropdown-toggle="school-{{$school->id}}-dropdown" class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100" type="button">
                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                    <div id="school-{{$school->id}}-dropdown" class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="school-{{$school->id}}-dropdown-button">
                            <li>
                                <a href="#" class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Show</a>
                            </li>
                            <li>
                                <a href="#" class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Edit</a>
                            </li>
                        </ul>
                        <div class="py-1">
                            <a href="#" class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Delete</a>
                        </div>
                    </div>
                </td>
            </tr>
            @endif                              
            @endforeach
            @php
                $gross_total_time += $total_time;
                $gross_total_budget += $total_budget;
            @endphp
            <tr class="border-b dark:border-gray-700">
                <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">Total</th>
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
    <tr class="border-b dark:border-gray-700">
        <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">Total</th>
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
