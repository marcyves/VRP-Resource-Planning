@props(['school_id','school_name', 'courses'])
<!-- Start simple-course-table.blade  -->
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
                <th scope="col" class="px-2 py-3 text-center">
                    <span class="sr-only">Actions</span>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_time = 0;   
                $total_budget = 0;
            @endphp
    
            @foreach ($courses as $course)
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
                <td class="px-2 py-3 flex items-center justify-end">
                @if(Auth::user()->getMode() == "Edit")
                    <form action="{{route('course.edit', $course->id)}}" method="get">
 <x-button-edit/>
                    </form>
                    <form action="{{route('course.destroy', $course->id)}}" method="post">
                        @csrf
                        @method('delete')
<x-button-delete/>
                    </form>
                @endif
                </td>
            </tr>

            @php
            $total_time += $course->session_length*$course->sessions*$course->groups_count;   
            $total_budget += $course->rate*$course->session_length*$course->sessions*$course->groups_count; 
            @endphp

            @endforeach
            <tr class="border-b">
                <th scope="row" class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">Total</th>
                <td colspan="5"></td>
                <td class="px-2 py-3 text-center">{{$total_time}}</td>
                <td  colspan="3"></td>
                <td class="px-2 py-3 text-right">@money($total_budget)</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
<!-- End simple-course-table.blade  -->