<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.course_details') }}: {{ $course->name }}</h2>
    </x-slot>

    <section class="course-details-section">
        <header class="header-actions">
            <form action="{{ route('course.edit', $course->id) }}" method="get">
                <x-button-edit />
            </form>
        </header>

        <div class="course-info-grid">
            <article>
                <p>
                    <span class="card-label">{{ __('messages.program') }}</span>
                    <a href="{{ route('program.show', $course->program_id) }}">{{ $course->program_name }}</a>
                </p>
                <p>
                    <span class="card-label">{{ __('messages.sessions') }}</span>
                    <span>{{ $course->sessions }}</span>
                </p>
                <p>
                    <span class="card-label">{{ __('messages.session_length') }}</span>
                    <span>{{ $course->session_length }}</span>
                </p>
            </article>

            <article>
                <p>
                    <span class="card-label">{{ __('messages.year') }}</span>
                    <span>{{ $course->year }}</span>
                </p>
                <p>
                    <span class="card-label">{{ __('messages.semester') }}</span>
                    <span>{{ $course->semester }}</span>
                </p>
            </article>

            <article>
                <p>
                    <span class="card-label">{{ __('messages.rate') }}</span>
                    <span>@money($course->rate) € HT / @money($course->rate * 1.2) € TTC</span>
                </p>
            </article>
        </div>
    </section>

    <section class="course-groups-section">
        <header>
            <h2>{{ __('messages.groups') }}</h2>
            @if (Auth::user()->getMode() == 'Edit')
            <a class="btn btn-secondary" href="{{ route('group.new', $course->id) }}">{{ __('messages.group_create') }}</a>
            @endif
        </header>

        <div class="group-grid">
            @foreach ($groups as $group)
            <article>
                <header>
                    <h3><a href="{{ route('group.show', $group->id) }}">{{ $group->name }}</a></h3>
                    <p>({{ $group->short_name }}) {{ $group->size }} {{ __('messages.students') }}</p>
                </header>
                <ul>
                    @foreach ($occurences as $occurence)
                    @if ($group->id == $occurence->group_id)
                    <li>{{ \Carbon\Carbon::parse($occurence->begin)->format('d/m/Y H:i') }} – {{ \Carbon\Carbon::parse($occurence->end)->format('H:i') }}</li>
                    @endif
                    @endforeach
                </ul>
                @if (Auth::user()->getMode() == 'Edit')
                <footer>
                    <form action="{{ route('group.edit', $group->id) }}" method="get">
                        <x-button-edit />
                    </form>
                    <form action="{{ route('group.unlink', $group->id) }}" method="post">
                        @csrf
                        @method('delete')
                        <x-button-delete />
                    </form>
                </footer>
                @endif
            </article>
            @endforeach
        </div>
    </section>

    <section class="course-available-groups">
        <h2>{{ __('messages.groups_available') }}</h2>
        <div class="group-grid">
            @if (empty($available_groups))
            <p role="status">{{ __('messages.no_available_group') }}</p>
            @else
            @foreach ($available_groups as $group)
            <article>
                <h3>{{ $group->name }}</h3>
                <footer>
                    <form action="{{ route('group.link', $group->id) }}" method="get">
                        <x-button-secondary type="submit">
                            <img src="/icons/arrow-up.svg" alt="{{ __('messages.add') }}">
                        </x-button-secondary>
                    </form>
                </footer>
            </article>
            @endforeach
            @endif
        </div>
    </section>
</x-app-layout>
