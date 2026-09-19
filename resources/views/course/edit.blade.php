<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.course_update') }}</h2>
    </x-slot>

    <section>
        @isset($course)
        <form action="{{route('course.update', $course->id)}}" method="post" class="group-form nice-form">
            @csrf
            @method('put')

            <div class="form-group">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{old('name',$course->name)}}" />
            </div>

            <x-course-identity-fields
                :programs="$programs"
                :short-name="$course->short_name"
                :program-id="$course->program_id"
                :year="$course->year"
                :semester="$course->semester"
            />

            <x-course-volume-fields
                :sessions="$course->sessions"
                :session-length="$course->session_length"
                :rate="\App\Http\Utility\Tools::hourlyRateTtc($course->rate)"
            />

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('course.show', $course->id) }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
            </div>
        </form>
        @endisset
    </section>
</x-app-layout>
