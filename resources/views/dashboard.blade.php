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
                <option value="all" @if($current_year == "all")selected @endif>{{ __('actions.select_all')}}</option>
                @foreach ($years as $year)
                <option value="{{$year->year}}" @if($current_year == $year->year)selected @endif>{{$year->year}}</option>
                @endforeach                
            </select>
            </form>
            <form class="inline-flex" action="{{route('school.semester')}}" method="post">
                @csrf
            <select id="current_semester" name="current_semester" class="rounded-md mt-4 py-0 pl-2 pr-8" onchange="this.form.submit()">
                <option value="all" @if($current_year == "all")selected @endif>{{ __('actions.select_all')}}</option>
                @foreach ($years as $year)
                <option value="{{$year->semester}}" @if($current_semester == $year->semester)selected @endif>{{$year->semester}}</option>
                @endforeach                
            </select>
            </form>
        </div>
    </x-slot>

    <section class="nice-page">
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
                    <tr class="font-semibold">
                        <th scope="row" class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">Total</th>
                        <td class="px-2 py-3" colspan="6"></td>
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
    </article>
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
            <article class="mx-auto max-w-screen-xl px-2 lg:px-12 mb-4">
                <div class="bg-blue-100 mb-2 space-y-3 md:space-y-0 md:space-x-4 p-2
                relative shadow-md sm:rounded-lg overflow-hidden">
                <x-school-header :school_name=$school_name :school_id=$school_id/>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-center">
                            <tr>
                                <th scope="col" class="px-2 py-3">{{__('messages.program')}}</th>
                                <th scope="col" class="px-2 py-3">{{__('messages.course')}}</th>
                                <th scope="col" class="px-2 py-3 text-center">{{__('messages.semester')}}</th>
                                <th scope="col" class="px-2 py-3 text-center">{{__('messages.sessions')}}</th>
                                <th scope="col" class="px-2 py-3 text-center">{{__('messages.session_length')}}</th>
                                <th scope="col" class="px-2 py-3 text-center">{{__('messages.time')}}</th>
                                <th scope="col" class="px-2 py-3 text-center">{{__('messages.groups')}}</th>
                                <th scope="col" class="px-2 py-3 text-center">{{__('messages.total_time')}}</th>
                                <th scope="col" class="px-2 py-3 text-center">{{__('messages.rate')}}</th>
                                <th scope="col" class="px-2 py-3 text-center">{{__('messages.gain')}}</th>
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
            @endif
            <tr>
                <td class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">
                <form action="{{route('program.show', $course->program_id)}}" method="get">
                        @csrf
                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            {{$course->program_name}}
                        </button>    
                    </form>
                </td>
                <th scope="row" class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">
                    <form action="{{route('course.show', $course->id)}}" method="get">
                        @csrf
                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            {{$course->name}}
                        </button>    
                    </form>
                </th>
                <td class="px-2 py-3 text-center">{{$course->semester}}</td>
                <td class="px-2 py-3 text-center">{{$course->sessions}}</td>
                <td class="px-2 py-3 text-center">{{$course->session_length}}</td>
                <td class="px-2 py-3 text-center">{{$course->session_length*$course->sessions}}</td>
                <td class="px-2 py-3 text-center">{{$course->groups_count}}</td>
                <td class="px-2 py-3 text-center">{{$course->groups_count*$course->session_length*$course->sessions}}</td>
                <td class="px-2 py-3 text-right">@money($course->rate)</td>
                <td class="px-2 py-3 text-right">@money($course->rate*$course->session_length*$course->sessions*$course->groups_count)</td>
                @if(Auth::user()->getMode() == "Edit")
                <td class="px-2 py-3 flex items-center justify-end">
                    <form action="{{route('course.edit', $course->id)}}" method="get">
                        <x-button-edit/>
                    </form>
                    <form action="{{route('course.destroy', $course->id)}}" method="post">
                        @csrf
                        @method('delete')
                        <x-button-delete/> 
                    </form>
                </td>
                @endif
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
        <td class="px-2 py-3 text-center">{{__('messages.total_time')}}</td>
        <td class="px-2 py-3"></td>
        <td class="px-2 py-3 text-center">@money($total_budget)</td>
        <td class="px-2 py-3 flex items-center justify-end">
        </td>
    </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </article>
    @if($gross_total_time>0)                                
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-600 uppercase bg-gray-50">
            <tr>
                <th scope="col" class="px-2 py-3"></th>
                <th scope="col" class="px-2 py-3"></th>
                <th scope="col" class="px-2 py-3">{{__('messages.total_time')}}</th>
                <th scope="col" class="px-2 py-3">{{$gross_total_time}}</th>
                <th scope="col" class="px-2 py-3">{{__('messages.total_gain')}}</th>
                <th scope="col" class="px-2 py-3">@money($gross_total_budget)</th>
                <th scope="col" class="px-2 py-3">{{__('messages.hour_rate')}}</th>
                <th scope="col" class="px-2 py-3">@money($gross_total_budget/$gross_total_time)</th>
                <th scope="col" class="px-2 py-3"></th>
            </tr>
        </thead>
    </table>
    @endif
</section>

</x-app-layout>
