<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.group_create') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    @if($linkCourseId && $linkCourseName)
        <p class="form-hint form-hint--emphasis">{{ __('messages.group_will_link_session_course', ['name' => $linkCourseName]) }}</p>
    @endif

    <section>
        <form action="{{ route('group.save', $course_id) }}" method="post" class="group-form nice-form">
            @csrf
            <x-form-group-create :details-row="true" />
            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('course.show', $course_id) }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.create') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
