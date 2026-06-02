<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.course_details') }}: {{ $course->name }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    <section class="course-details-section">
        <header class="header-actions">
            @if (Auth::user()->getMode() == 'Edit')
            <form action="{{ route('course.edit', $course->id) }}" method="get">
                <x-button-edit />
            </form>
            @endif
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
        <p class="form-hint">{{ __('messages.groups_course_help') }}</p>

        @if ($groups->isEmpty())
            <p class="program-empty" role="status">{{ __('messages.no_group') }}</p>
        @else
            <x-course-groups-table :groups="$groups" :occurences="$occurences" :show-archive="true" />
        @endif
    </section>

    @if ($inactive_linked_groups->isNotEmpty())
    <section class="course-groups-section course-groups-section--inactive">
        <header>
            <h2>{{ __('messages.inactive_groups_on_course') }}</h2>
        </header>
        <p class="form-hint">{{ __('messages.inactive_groups_on_course_help') }}</p>
        <x-course-groups-table :groups="$inactive_linked_groups" :occurences="$occurences" :show-archive="true" :archived="true" />
    </section>
    @endif

    @if (Auth::user()->getMode() == 'Edit')
    <section class="course-available-groups">
        <header class="program-section-header">
            <h2>{{ __('messages.groups_available') }}</h2>
        </header>
        <p class="form-hint">{{ __('messages.groups_available_help') }}</p>
        <x-course-available-groups-table :groups="$available_groups" />
    </section>
    @endif
</x-app-layout>
