<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <section class="nice-page">
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
            <li>
                <label class="font-semibold text-gray-800 leading-tight">
                Program: 
                </label>
                <a class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none"
                    href="{{route('program.show', $course->program_id)}}">
                    {{$course->program_name}}
                </a>
            </li>
            <li>
            <label class="font-semibold text-gray-800 leading-tight">
                Sessions: 
            </label>
            {{$course->sessions}}</li>
            <li>
            <label class="font-semibold text-gray-800 leading-tight">
            Session length: 
            </label>    
            {{$course->session_length}}</li>
            <li>
            <label class="font-semibold text-gray-800 leading-tight">
            Rate: 
            </label>
            {{$course->rate}}</li>
            <li>
            <label class="font-semibold text-gray-800 leading-tight">
            Year: 
            </label>
            {{$course->year}}</li>
            <li>
            <label class="font-semibold text-gray-800 leading-tight">
            Semester: 
            </label>
            {{$course->semester}}
            </li>
        </ul>       
    </section>

    <section class="nice-page">
        <x-nice-title color="grey-200" title="Groups">
            @if(Auth::user()->getMode() == "Edit")
                <a
                class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                href="{{route('group.new', $course->id)}}">{{ __('messages.group_create') }}</a>
            @endif
        </x-nice-title>
        @foreach ($groups as $group)
            <div class="flex flex-col md:flex-row items-center justify-between gap-2">
                <div class="w-full md:w-1/2 border border-gray-300 rounded-md bg-white ">
                    <a class="inline-flex items-center p-2 text-sm font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                    href="{{route('group.show', $group->id)}}">{{$group->name}}</a>            
                    ({{$group->short_name}})         
                    {{$group->size}} students
                </div>
                @if(Auth::user()->getMode() == "Edit")
                <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <form action="{{route('group.edit', $group->id)}}" method="get">
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-green-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>  
                            </button>    
                        </form>
                        <form action="{{route('group.unlink', $group->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-red-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>                                  
                            </button>    
                        </form>
                    </div>
                </div>
                @endif
            </div>
            <ol class="bg-gray-100 p-2">
            @foreach($occurences as $occurence)
                @if($group->id == $occurence->group_id)
                    <li class="ml-2">{{date_format(date_create($occurence->begin),'d/m/Y H:i')}}-{{date_format(date_create($occurence->end),'H:i')}}</li>
                @endif
            @endforeach
            </ol>
        @endforeach
    </section>

    <section class="nice-page">
        <h2 class="font-semibold text-xl mb-2">Available Groups</h2>
        <ul>
            @if($available_groups == [])
            <li class="mx-auto max-w-screen-xl px-2 lg:px-12 bg-white shadow-md sm:rounded-lg overflow-hidden mb-2
                flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-2">
                Pas de groupe disponible
            </li>
            @else
            @foreach ($available_groups as $group)
                <li class="mx-auto max-w-screen-xl px-2 lg:px-12 bg-white shadow-md sm:rounded-lg overflow-hidden mb-2
                    flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-2">
                    <a href="{{route('group.link', $group->id)}}">
                    {{$group->name}}
                    </a>
                </li>
            @endforeach
            @endif
        </ul>
    </section>

</x-app-layout>