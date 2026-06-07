<x-app-layout>
<x-slot name="header">
        <h2 class="header-title">{{ __('messages.workload_plan') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    @php
        $gross_total_time = 0;
        $gross_total_budget = 0;
        $active_schools = 0;

        foreach ($courses as $course) {
            $gross_total_time += $course->session_length * $course->sessions * $course->groups_count;
            $gross_total_budget += $course->rate * $course->session_length * $course->sessions * $course->groups_count;
        }

        foreach ($schools as $school) {
            if ($courses->where('school_name', $school->name)->isNotEmpty()) {
                $active_schools++;
            }
        }

        $hour_rate = $gross_total_time > 0 ? $gross_total_budget / $gross_total_time : 0;
    @endphp

    @if ($gross_total_time > 0)
        <x-kpi-grid :items="[
            ['icon' => 'clock', 'label' => __('messages.total_time'), 'value' => $gross_total_time . ' h', 'variant' => 'info'],
            ['icon' => 'wallet', 'label' => __('messages.total_gain'), 'value' => number_format($gross_total_budget, 2, ',', ' ') . ' €', 'variant' => 'success'],
            ['icon' => 'chart', 'label' => __('messages.hour_rate'), 'value' => number_format($hour_rate, 2, ',', ' ') . ' €/h', 'variant' => 'accent'],
            ['icon' => 'school', 'label' => __('messages.active_schools'), 'value' => (string) $active_schools, 'hint' => $current_year !== 'all' ? (string) $current_year : __('actions.select_all'), 'variant' => 'total'],
        ]" />
    @endif

    <section class="dashboard-container">
        <div class="school-sections">
            @foreach ($schools as $school)
            @if($school->courses->count() > 0)
            @php
                $school_courses = $courses->where('school_name', $school->name);
            @endphp
            @if($school_courses->isNotEmpty())
            <article class="school-card">
                <x-school-header :school_name="$school->name" :school_id="$school->id" />

                <x-course-table :courses="$school_courses" :school_name="$school->name" :school_id="$school->id" />
            </article>
            @endif
            @endif
            @endforeach
        </div>
    </section>
</x-app-layout>
