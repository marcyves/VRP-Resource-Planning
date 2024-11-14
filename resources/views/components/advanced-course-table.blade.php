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
            <x-dashboard-table-row :course=$course />
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