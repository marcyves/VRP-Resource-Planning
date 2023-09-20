<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('School Details') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        
<ul>
    <li class="mb-2 text-sm font-medium text-blue-800 hover:text-gray-800 rounded-lg focus:outline-none"><a href="{{route('school.show', $school->id)}}">{{$school->name}}</a></li>       
    @foreach ($courses as $course)
        @if($school->name == $course->school_name)
        <ul>
        <li  class="ml-4 mb-2 text-sm font-medium text-blue-400 hover:text-gray-800 rounded-lg focus:outline-none">
        {{$course->name}}  (@money($course->rate) €)<br>
        {{$course->short_name}}<br>
        Sessions: {{$course->sessions}} Length: {{$course->session_length}} ({{$course->year}} - {{$course->semester}})<br>

        {{$course->program_name}}
        {{$course->groups_count}}
        </li>
    </ul>
    @endif
@endforeach
</ul>

    </x-nice-box>
</x-app-layout>