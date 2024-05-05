@props(['school_id','school_name', 'courses'])
<!-- Start advanced-course-table.blade  -->
<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-center">
            <tr>
                <th scope="col" class="px-2 py-3">{{__('messages.program')}}</th>
                <th scope="col" class="px-2 py-3">{{__('messages.course')}}</th>
                <th scope="col" class="px-2 py-3 text-center">{{__('messages.year')}}</th>
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
                <td scope="row" class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">
                    <form action="{{route('course.show', $course->id)}}" method="get">
                        @csrf
                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            {{$course->name}}
                        </button>    
                    </form>
                </td>
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
<!-- End advanced-course-table.blade  -->