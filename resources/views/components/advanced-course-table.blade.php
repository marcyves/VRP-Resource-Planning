@props(['school_id','school_name', 'courses'])
<!-- Start advanced-course-table.blade  -->
<div class="bg-blue-100 mb-2 p-4 space-y-3 md:space-y-0 md:space-x-4 p-2
relative shadow-md sm:rounded-lg overflow-hidden">
<x-dashboard-table-begin/>
        @php
            $total_time = 0;   
            $total_budget = 0;
        @endphp

        @foreach ($courses as $course)
        <tr>
            <td>
                <form action="{{route('program.show', $course->program_id)}}" method="get">
                    @csrf
                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                        {{$course->program_name}}
                    </button>    
                </form>
            </td>
            <td scope="row">
                <form action="{{route('course.show', $course->id)}}" method="get">
                    @csrf
                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                        {{$course->name}}
                    </button>    
                </form>
            </td>
            <td>{{$course->year}}</td>
            <td>{{$course->semester}}</td>
            <td>{{$course->sessions}}</td>
            <td>{{$course->session_length}}</td>
            <td>{{$course->session_length*$course->sessions}}</td>
            <td>{{$course->groups_count}}</td>
            <td>{{$course->groups_count*$course->session_length*$course->sessions}}</td>
            <td class="text-right">@money($course->rate)</td>
            <td class="text-right">@money($course->rate*$course->session_length*$course->sessions*$course->groups_count)</td>
            <td class="flex items-center justify-end">
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
        <x-dashboard-table-end :total_budget=$total_budget :total_time=$total_time />
    </tbody>
</table>
</div>
<!-- End advanced-course-table.blade  -->