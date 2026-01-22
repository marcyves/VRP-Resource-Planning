<x-app-layout>
    @push('styles')
    @vite(['resources/css/courses.css', 'resources/css/groups.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('Détails du cours : ') }}{{$course->name}}</h2>
    </x-slot>

    <section class="course-details-section glass-background">
        <div class="header-actions mb-4">
            <form action="{{route('course.edit', $course->id)}}" method="get">
                <x-button-edit />
            </form>
        </div>

        <div class="course-info-grid">
            <div class="course-card glass-background">
                <div class="card-content-text">
                    <span class="card-label">Program</span>
                    <a class="nav-link font-bold" href="{{route('program.show', $course->program_id)}}">{{$course->program_name}}</a>
                </div>
                <div class="card-content-text mt-2">
                    <span class="card-label">Sessions</span>
                    <span>{{$course->sessions}}</span>
                </div>
                <div class="card-content-text mt-2">
                    <span class="card-label">Session length</span>
                    <span>{{$course->session_length}}</span>
                </div>
            </div>

            <div class="course-card glass-background">
                <div class="card-content-text">
                    <span class="card-label">Year:</span>
                    <span>{{$course->year}}</span>
                </div>
                <div class="card-content-text mt-2">
                    <span class="card-label">Semester: </span>
                    <span>{{$course->semester}}</span>
                </div>
            </div>

            <div class="course-card glass-background">
                <div class="card-content-text">
                    <span class="card-label">Rate: </span>
                    <span>@money($course->rate) € HT / @money($course->rate * 1.2) € TTC</span>
                </div>
            </div>
        </div>
    </section>

    <section class="course-groups-section glass-background">
        <x-nice-title color="grey-200" title="Groups">
            @if(Auth::user()->getMode() == "Edit")
            <a class="btn-secondary" href="{{route('group.new', $course->id)}}">{{ __('messages.group_create') }}</a>
            @endif
        </x-nice-title>

        <div class="group-grid">
            @foreach ($groups as $group)
            <div class="group-card glass-background">
                <div class="card-content-text">
                    <a class="group-title" href="{{route('group.show', $group->id)}}">{{$group->name}}</a>
                    <span class="group-meta">({{$group->short_name}}) {{$group->size}} students</span>

                    <ul class="flex-list mt-2">
                        @foreach($occurences as $occurence)
                        @if($group->id == $occurence->group_id)
                        <li class="text-sm">{{ \Carbon\Carbon::parse($occurence->begin)->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($occurence->end)->format('H:i') }}</li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @if(Auth::user()->getMode() == "Edit")
                <div class="card-actions mt-4">
                    <form action="{{route('group.edit', $group->id)}}" method="get">
                        <x-button-edit />
                    </form>
                    <form action="{{route('group.unlink', $group->id)}}" method="post">
                        @csrf
                        @method('delete')
                        <x-button-delete />
                    </form>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </section>

    <section class="course-available-groups glass-background mt-8">
        <h2 class="card-subtitle mb-4">{{ __('messages.groups_available')}}</h2>
        <div class="group-grid">
            @if(empty($available_groups))
            <div class="alert alert-info glass-background">
                Pas de groupe disponible
            </div>
            @else
            @foreach ($available_groups as $group)
            <div class="group-card glass-background">
                <div class="card-content-text">
                    <span class="group-title">{{$group->name}}</span>
                </div>
                <div class="card-actions">
                    <form action="{{route('group.link', $group->id)}}" method="get">
                        <x-button-secondary type="submit" class="btn-icon">
                            <img src="/icons/arrow-up.svg" alt="Up" class="icon-svg">
                        </x-button-secondary>
                    </form>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </section>
</x-app-layout>