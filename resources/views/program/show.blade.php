<x-app-layout>
<x-slot name="header">
        <h2 class="header-title">{{ $program->name }}</h2>
    </x-slot>

    <x-workload-module-tabs />

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
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.school') }}</th>
                            <th>{{ __('messages.year') }}</th>
                            <th>{{ __('messages.semester') }}</th>
                            <th>{{ __('messages.sessions') }}</th>
                            <th>{{ __('messages.groups') }}</th>
                            <th>{{ __('messages.rate') }}</th>
                            <th>{{ __('messages.total') }}</th>
                            @if (Auth::user()->getMode() == 'Edit')
                                <th>{{ __('messages.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                            @php
                                $courseTotal = $course->rate * $course->session_length * $course->sessions * $course->groups_count;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('course.show', $course->id) }}">{{ $course->name }}</a>
                                </td>
                                <td>{{ $course->school?->name }}</td>
                                <td class="date">{{ $course->year }}</td>
                                <td class="date">{{ $course->semester }}</td>
                                <td class="date">{{ $course->sessions }} × {{ $course->session_length }}h</td>
                                <td class="date">{{ $course->groups_count }}</td>
                                <td class="money">@money($course->rate) €/h</td>
                                <td class="money">@money($courseTotal) €</td>
                                @if (Auth::user()->getMode() == 'Edit')
                                    <td class="card-actions">
                                        <form action="{{ route('course.edit', $course->id) }}" method="get">
                                            <x-button-edit />
                                        </form>
                                        <form action="{{ route('course.destroy', $course->id) }}" method="post">
                                            @csrf
                                            @method('delete')
                                            <x-button-delete />
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
</x-app-layout>
