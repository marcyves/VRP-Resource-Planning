<x-app-layout>
<x-slot name="header">
        <h2 class="header-title">{{ __('messages.workload_plan') }}</h2>
    </x-slot>

    <x-module-tabs :tabs="[
        ['href' => route('dashboard'), 'label' => __('messages.workload_plan'), 'active' => request()->routeIs('dashboard', 'school.dashboard')],
        ['href' => route('school.index'), 'label' => __('messages.schools'), 'active' => request()->routeIs('school.index', 'school.list', 'school.show', 'school.create', 'school.edit', 'school.add', 'course.*')],
        ['href' => route('program.index'), 'label' => __('messages.programs'), 'active' => request()->routeIs('program.*')],
        ['href' => route('group.index'), 'label' => __('messages.groups'), 'active' => request()->routeIs('group.*')],
    ]" />

    <section class="dashboard-container">
        @php
        $gross_total_time = 0;
        $gross_total_budget = 0;
        @endphp

        <div class="school-sections">
            @foreach ($schools as $school)
            @if($school->courses->count() > 0)
            <article class="school-card">
                <x-school-header :school_name="$school->name" :school_id="$school->id" />

                @php
                $school_courses = $courses->where('school_name', $school->name);
                @endphp

                <div class="course-table-container mt-4">
                    <x-course-table :courses="$school_courses" :school_name="$school->name" :school_id="$school->id" />
                </div>

                @php
                // These totals are actually calculated inside x-course-table and x-course-table-end,
                // but we need them here too for the gross total.
                // For now, let's recalculate or ensure they are captured.
                foreach($school_courses as $course) {
                $gross_total_time += $course->session_length * $course->sessions * $course->groups_count;
                $gross_total_budget += $course->rate * $course->session_length * $course->sessions * $course->groups_count;
                }
                @endphp
            </article>
            @endif
            @endforeach
        </div>

        @if ($gross_total_time > 0)
        <div class="summary-container mt-8">
            <div class="summary-item">
                <span>{{ __('messages.total_time') }}:</span>
                <strong>{{ $gross_total_time }}h</strong>
            </div>
            <div class="summary-item">
                <span>{{ __('messages.total_gain') }}:</span>
                <strong>@money($gross_total_budget) €</strong>
            </div>
            <div class="summary-item total">
                <span>{{ __('messages.hour_rate') }}:</span>
                <strong>@money($gross_total_budget / $gross_total_time) €/h</strong>
            </div>
        </div>
        @endif
    </section>
</x-app-layout>