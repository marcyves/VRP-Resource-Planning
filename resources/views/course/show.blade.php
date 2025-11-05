<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('Détails du cours : ') }}{{$course->name}}
        </h2>
    </x-slot>

    <section class="glass-background">
        <div class="flex flew-row place-content-between bg-grey-200 p-2 rounded-md">
            <span class="justify-end">
                <form action="{{route('course.edit', $course->id)}}" method="get">
                    <x-button-edit/>
                </form>
            </span>
        </div>
        <ul class="list">
            <li class="card">
                <div class="card-content-text">
                    <label class="card-title">Program</label>
                    <a class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none"
                        href="{{route('program.show', $course->program_id)}}">{{$course->program_name}}</a>
                    <br>
                    <label class="card-title">Sessions</label>
                    {{$course->sessions}}
                    <br>
                    <label class="card-title">Session length</label>    
                    {{$course->session_length}}
                </div>
            </li>
            <li class="card">
                <div class="card-content-text">
                    <label class="card-title">Year:</label>
                    {{$course->year}}
                    <br>
                    <label class="card-title">Semester: </label>
                    {{$course->semester}}
                </div>
            </li>
            <li class="card">
                <div class="card-content-text">
                <label class="card-title">Rate: </label>
                @money($course->rate) € HT / @money($course->rate * 1.2) € TTC
                </div>
            </li>
        </ul>       
    </section>

    <section class="glass-background">
        <x-nice-title color="grey-200" title="Groups">
            @if(Auth::user()->getMode() == "Edit")
                <a
                class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                href="{{route('group.new', $course->id)}}">{{ __('messages.group_create') }}</a>
            @endif
        </x-nice-title>
        <ul class="list">
        @foreach ($groups as $group)
            <li class="card">
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
            </li>
        @endforeach
        </ul>
    </section>

    <section class="glass-background">
        <h2 class="font-semibold text-xl mb-2">{{ __('messages.groups_available')}}</h2>
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
                        <button class="cool-box icon green" type="submit">
                        <img src="/icons/arrow-up.svg" alt="Up">
                        </button>    
                    </form>
                </li>
            @endforeach
            @endif
        </ul>
    </section>

</x-app-layout>