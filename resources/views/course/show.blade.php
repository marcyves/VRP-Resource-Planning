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
                    <x-button-edit/>
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
        <div class="list">
        @foreach ($groups as $group)
            <div class="card">
                <div class="card-content-text">
                    <a class="card-title" 
                    href="{{route('group.show', $group->id)}}">{{$group->name}}</a>            
                    ({{$group->short_name}})         
                    {{$group->size}} students
                
                <ul class="card-subtitle">
            @foreach($occurences as $occurence)
                @if($group->id == $occurence->group_id)
                    <li class="ml-2">{{date_format(date_create($occurence->begin),'d/m/Y H:i')}}-{{date_format(date_create($occurence->end),'H:i')}}</li>
                @endif
            @endforeach
            </ul>
            </div>
                @if(Auth::user()->getMode() == "Edit")
                <div class="card-content-end">
                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <form action="{{route('group.edit', $group->id)}}" method="get">
                            <x-button-edit/>
                        </form>
                        <form action="{{route('group.unlink', $group->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <x-button-delete/>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        @endforeach
        </div>
    </section>

    <section class="nice-page">
        <h2 class="font-semibold text-xl mb-2">Available Groups</h2>
        <ul class="list">
            @if($available_groups == [])
            <li class="mx-auto max-w-screen-xl px-2 lg:px-12 bg-white shadow-md sm:rounded-lg overflow-hidden mb-2
                flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-2">
                Pas de groupe disponible
            </li>
            @else
            @foreach ($available_groups as $group)
                <li class="card">
                    <div class="card-content-text">
                        <div class="card-title">
                        {{$group->name}}
                        </div>
                    </div>
                    <form action="{{route('group.link', $group->id)}}" method="get" class="card-content-end">
                        <button class="icon green" type="submit">
                        <img src="/icons/arrow-up.svg" alt="Up">
                        </button>    
                    </form>
                </li>
            @endforeach
            @endif
        </ul>
    </section>

</x-app-layout>