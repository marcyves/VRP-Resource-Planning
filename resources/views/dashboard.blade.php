<x-app-layout>       
    <x-slot name="header">
        <div class="flex flex-col grow md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            <div class="w-full md:w-1/2 ">
            <h2 class="inline-flex font-semibold text-xl text-gray-800 mr-4">
                {{ __('Dashboard') }}
            </h2>
            <form class="inline-flex" action="{{route('school.year')}}" method="post">
                @csrf
            <select id="current_year" name="current_year" class="rounded-md mt-4 py-0 pl-2 pr-8" onchange="this.form.submit()">
                <option value="all" @if($current_year == "all")selected @endif>All</option>
                @foreach ($years as $year)
                <option value="{{$year->year}}" @if($current_year == $year->year)selected @endif>{{$year->year}}</option>
                @endforeach                
            </select>
            </form>
            <form class="inline-flex" action="{{route('school.semester')}}" method="post">
                @csrf
            <select id="current_semester" name="current_semester" class="rounded-md mt-4 py-0 pl-2 pr-8" onchange="this.form.submit()">
                <option value="all" @if($current_year == "all")selected @endif>All</option>
                @foreach ($years as $year)
                <option value="{{$year->semester}}" @if($current_semester == $year->semester)selected @endif>{{$year->semester}}</option>
                @endforeach                
            </select>
            </form>
        </div>
        <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0  md:items-center justify-end md:space-x-3">
            <a class="p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                href="{{route('school.list')}}">Add School</a>
            <a class="p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
            href="{{route('school.create')}}">Create School</a>
        </div>
    </x-slot>
    <x-nice-box color="grey-400">
        @php
        $gross_total_time = 0;   
        $gross_total_budget = 0;
        $total_time = 0;   
        $total_budget = 0;
        $school_name = "";
        $school_count = 0;
        @endphp

        @foreach ($courses as $course)
            @if($school_name != $course->school_name)
                @if($school_count > 0)
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
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            </section>
                @endif
                @php  
                    $gross_total_time += $total_time;
                    $gross_total_budget += $total_budget;
                    $school_count += 1;
                    $total_time = 0;   
                    $total_budget = 0;
                    $school_id = $course->school_id;
                    $school_name = $course->school_name;
                @endphp
            <section class="bg-gray-50 p-3 sm:p-5">
                <div class="mx-auto max-w-screen-xl px-2 lg:px-12">
                    <div class="bg-white relative shadow-md sm:rounded-lg overflow-hidden">
                        <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                            <div class="w-full md:w-1/2">
                                <form action="{{route('school.show', $school_id)}}" method="get">
                                    @csrf
                                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                        {{$school_name}}
                                    </button>    
                                </form>
                            </div>
                            <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                                <div class="flex items-center space-x-3 w-full md:w-auto">
                                    <form action="{{route('school.edit', $school_id)}}" method="get">
                                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-green-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                              </svg>  
                                        </button>    
                                    </form>
                                    <form action="{{route('school.destroy', $school_id)}}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-red-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                              </svg>                                  
                                        </button>    
                                    </form>
                                    <a
                                    class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                                    href="{{route('course.create', $school_id)}}">Add Course</a>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-center">
                                    <tr>
                                        <th scope="col" class="px-2 py-3">Program</th>
                                        <th scope="col" class="px-2 py-3">Course name</th>
                                        <th scope="col" class="px-2 py-3 text-center">Year</th>
                                        <th scope="col" class="px-2 py-3 text-center">Semester</th>
                                        <th scope="col" class="px-2 py-3 text-center">Sessions</th>
                                        <th scope="col" class="px-2 py-3 text-center">Session length</th>
                                        <th scope="col" class="px-2 py-3 text-center">Time</th>
                                        <th scope="col" class="px-2 py-3 text-center">Groups</th>
                                        <th scope="col" class="px-2 py-3 text-center">Total time</th>
                                        <th scope="col" class="px-2 py-3 text-center">Rate</th>
                                        <th scope="col" class="px-2 py-3 text-center">Gain</th>
                                        <th scope="col" class="px-2 py-3 text-center">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
            @endif
            <td class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">{{$course->program_name}}</td>
            <th scope="row" class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">
                <form action="{{route('course.show', $course->id)}}" method="get">
                    @csrf
                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                        {{$course->name}}
                    </button>    
                </form>
            </th>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>                                  
                    </button>    
                </form>
                <form action="{{route('course.destroy', $course->id)}}" method="post">
                    @csrf
                    @method('delete')
                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-red-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>                                  
                    </button>    
                </form>

            </td>
        </tr>
        @php
            $total_time += $course->session_length*$course->sessions*$course->groups_count;   
            $total_budget += $course->rate*$course->session_length*$course->sessions*$course->groups_count; 
        @endphp
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
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            </section>
    @if($gross_total_time>0)                                
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
    @endif
    </x-nice-box>

</x-app-layout>
