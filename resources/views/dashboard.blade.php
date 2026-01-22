<x-app-layout>
    @push('styles')
    @vite(['resources/css/schools.css', 'resources/css/bills.css'])
    @endpush

    <x-slot name="header">
        <h2 class="header-title">{{ __('messages.dashboard') }}</h2>

        <div class="dashboard-stats mt-4">
            @foreach ($histograms as $histogram)
            <x-histogram :values="$histogram" />
            @endforeach
        </div>
    </x-slot>

    <section class="dashboard-container">
        @php
        $gross_total_time = 0;
        $gross_total_budget = 0;
        @endphp

        <div class="school-sections">
            @foreach ($schools as $school)
            @if($school->courses->count() > 0)
            <article class="school-card glass-background">
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
        <div class="summary-container glass-background mt-8">
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