<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        <div class="flex flew-row place-content-between bg-grey-200 p-2 rounded-md">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{$course->name}}</h2>
            <span class="justify-end">
                <form action="{{route('course.edit', $course->id)}}" method="get">
                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-green-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>                                  
                    </button>    
                </form>
            </span>
        </div>
        <ul class="mx-4">
            <li>Program: {{$course->program_name}}</li>
            <li>Sessions: {{$course->sessions}}</li>
            <li>Session length: {{$course->session_length}}</li>
            <li>Rate: {{$course->rate}}</li>
            <li>Year: {{$course->year}}</li>
            <li>Semester: {{$course->semester}}</li>
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