<x-app-layout>
    <x-slot name="header">
        <h2>{{ $group->name }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    @php
        $sessionCount = $occurences->count();
    @endphp

    <section class="program-detail-card">
        <header class="program-detail-card__header">
            <div class="program-detail-card__title">
                <h3>{{ $group->name }} <span class="group-detail-card__short">({{ $group->short_name }})</span></h3>
                @if (Auth::user()->getMode() == 'Edit')
                    <div class="program-detail-card__icon-actions">
                        <form action="{{ route('group.edit', $group->id) }}" method="get">
                            <x-button-edit />
                        </form>
                        <form action="{{ route('group.destroy', $group->id) }}" method="post">
                            @csrf
                            @method('delete')
                            <x-button-delete />
                        </form>
                    </div>
                @endif
            </div>
        </header>

        <div class="program-stats-grid">
            <article>
                <span>{{ __('messages.size') }}</span>
                <strong>{{ $group->size }} {{ __('messages.students') }}</strong>
            </article>
            <article>
                <span>{{ __('messages.year') }}</span>
                <strong>{{ $group->year }}</strong>
            </article>
            <article>
                <span>{{ __('messages.sessions') }}</span>
                <strong>{{ $sessionCount }}</strong>
            </article>
            <article>
                <span>{{ __('messages.course_list') }}</span>
                <strong>{{ $courses->count() }}</strong>
            </article>
        </div>
    </section>

    <section class="program-list-section">
        <header class="program-section-header">
            <h3>{{ __('messages.associated_courses') }}</h3>
        </header>

        @if ($courses->isEmpty())
            <p class="program-empty" role="status">{{ __('messages.no_course') }}</p>
        @else
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.year') }}</th>
                            <th>{{ __('messages.semester') }}</th>
                            @if (Auth::user()->getMode() == 'Edit')
                                <th>{{ __('messages.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                            <tr>
                                <td>
                                    <a href="{{ route('course.show', $course->id) }}">{{ $course->name }}</a>
                                </td>
                                <td class="date">{{ $course->year }}</td>
                                <td class="date">{{ $course->semester }}</td>
                                @if (Auth::user()->getMode() == 'Edit')
                                    <td class="card-actions">
                                        <form action="{{ route('course.edit', $course->id) }}" method="get">
                                            <x-button-edit />
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($occurences->isNotEmpty())
        <section class="program-list-section">
            <header class="program-section-header">
                <h3>{{ __('messages.sessions') }}</h3>
            </header>
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.course') }}</th>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.end') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($occurences as $occurence)
                            <tr>
                                <td>
                                    <a href="{{ route('planning.edit', $occurence->planning_id) }}">{{ $occurence->course_name }}</a>
                                </td>
                                <td class="date">{{ \Carbon\Carbon::parse($occurence->begin)->format('d/m/Y H:i') }}</td>
                                <td class="date">{{ \Carbon\Carbon::parse($occurence->end)->format('H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-app-layout>
