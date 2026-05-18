<x-app-layout>
<x-slot name="header">
        <h2 class="header-title">{{ $program->name }}</h2>
    </x-slot>

    <x-module-tabs :tabs="[
        ['href' => route('dashboard'), 'label' => __('messages.workload_plan'), 'active' => request()->routeIs('dashboard', 'school.dashboard')],
        ['href' => route('school.index'), 'label' => __('messages.schools'), 'active' => request()->routeIs('school.index', 'school.list', 'school.show', 'school.create', 'school.edit', 'school.add', 'course.*')],
        ['href' => route('program.index'), 'label' => __('messages.programs'), 'active' => request()->routeIs('program.*')],
        ['href' => route('group.index'), 'label' => __('messages.groups'), 'active' => request()->routeIs('group.*')],
    ]" />

    @php
        $totalHours = $courses->sum(fn ($course) => $course->session_length * $course->sessions * $course->groups_count);
        $totalBudget = $courses->sum(fn ($course) => $course->rate * $course->session_length * $course->sessions * $course->groups_count);
    @endphp

    <section class="program-detail-card">
        <header class="program-detail-card__header">
            <div class="program-detail-card__title">
                <h3>{{ $program->name }}</h3>
                @if(Auth::user()->getMode() == "Edit")
                    <div class="program-detail-card__icon-actions">
                        <form action="{{ route('program.edit', $program->id) }}" method="get">
                            <x-button-edit />
                        </form>
                        <form action="{{ route('program.destroy', $program->id) }}" method="post">
                            @csrf
                            @method('delete')
                            <x-button-delete />
                        </form>
                    </div>
                @endif
            </div>

            @if(Auth::user()->getMode() == "Edit")
                <div class="program-detail-card__actions">
                    @if(session('school_id') !== null)
                        <a class="btn btn-secondary" href="{{ route('course.create', session('school_id')) }}">{{ __('messages.add_course') }}</a>
                    @endif
                </div>
            @endif
        </header>

        <div class="program-stats-grid">
            <article>
                <span>{{ __('messages.course_list') }}</span>
                <strong>{{ $courses->count() }}</strong>
            </article>
            <article>
                <span>{{ __('messages.total_time') }}</span>
                <strong>{{ $totalHours }}h</strong>
            </article>
            <article>
                <span>{{ __('messages.total_gain') }}</span>
                <strong>@money($totalBudget) €</strong>
            </article>
            <article>
                <span>{{ __('messages.hour_rate') }}</span>
                <strong>{{ $totalHours > 0 ? number_format($totalBudget / $totalHours, 2) : '0.00' }} €/h</strong>
            </article>
        </div>
    </section>

    <section class="program-list-section">
        <header class="program-section-header">
            <h3>{{ __('messages.associated_courses') }}</h3>
        </header>

        @if($courses->isEmpty())
            <p class="program-empty">{{ __('messages.no_course') }}</p>
        @else
            <div class="program-course-grid">
                @foreach($courses as $course)
                    <article class="program-course-item {{ Auth::user()->getMode() == 'Edit' ? 'program-course-item--editable' : '' }}">
                        <div class="program-course-item__main">
                            <a class="program-course-item__title" href="{{ route('course.show', $course->id) }}">
                                {{ $course->name }}
                            </a>
                            <span>{{ $course->school?->name }}</span>
                        </div>

                        <div class="program-course-item__meta">
                            <span>{{ __('messages.year') }} {{ $course->year }}</span>
                            <span>{{ __('messages.semester') }} {{ $course->semester }}</span>
                            <span>{{ $course->sessions }} x {{ $course->session_length }}h</span>
                            <span>{{ $course->groups_count }} {{ __('messages.groups') }}</span>
                        </div>

                        <div class="program-course-item__amount">
                            <span>@money($course->rate) €/h</span>
                            <strong>@money($course->rate * $course->session_length * $course->sessions * $course->groups_count) €</strong>
                        </div>

                        @if(Auth::user()->getMode() == "Edit")
                            <div class="program-course-item__actions">
                                <form action="{{ route('course.edit', $course->id) }}" method="get">
                                    <x-button-edit />
                                </form>
                                <form action="{{ route('course.destroy', $course->id) }}" method="post">
                                    @csrf
                                    @method('delete')
                                    <x-button-delete />
                                </form>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-app-layout>
