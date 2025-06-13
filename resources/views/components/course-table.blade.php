@props(['school_id','school_name', 'courses'])
<!-- Start course-table.blade  -->
<table class="course-table">
<x-course-table-begin/>
        @php
            $total_time = 0;   
            $total_budget = 0;
        @endphp

        @foreach ($courses as $course)
            <x-course-table-row :course=$course />
            @php
            $total_time += $course->session_length*$course->sessions*$course->groups_count;   
            $total_budget += $course->rate*$course->session_length*$course->sessions*$course->groups_count; 
            @endphp
        @endforeach
        <x-course-table-end :total_budget=$total_budget :total_time=$total_time />
    </tbody>
</table>
<!-- End course-table.blade  -->