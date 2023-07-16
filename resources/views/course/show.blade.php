<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        <x-nice-title color="grey-200" title="{{$course->name}}"/>
        <ul class="mx-4">
    <li>Sessions: {{$course->sessions}}
    </li>
    <li>Session length: {{$course->session_length}}
    </li>
    <li>Rate: {{$course->rate}}
    </li>
    <li>Year: {{$course->year}}
    </li>
    <li>Semester: {{$course->semester}}
    </li>
</ul>
    </x-nice-box>

    <x-nice-box color="white">
        <x-nice-title color="grey-200" title="Groups">
            <a
            class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
            href="{{route('group.create', $course->id)}}">New Group</a>
        </x-nice-title>
        <ul>
        @foreach ($groups as $group)
        <li class="mx-4 my-2">
            <a
            class="m-4 inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
            href="{{route('planning.create', $group->id)}}">Plan</a>            
            {{$group->name}} ({{$group->size}}) 
        </li> 
        @endforeach
        </ul>
  
    </x-nice-box>

</x-app-layout>