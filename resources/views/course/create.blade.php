<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.course_create') }} {{$school->name}}</h2>
    </x-slot>

    <section>
        <form action="{{route('course.store', $school->id)}}" method="post" class="group-form nice-form">
            @csrf

            <div class="form-group">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="{{ __('messages.name') }}" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <x-course-identity-fields
                :programs="$programs"
                :year="now()->format('Y')"
                :semester="\App\Http\Utility\Tools::defaultCourseSemester()"
            />

            <x-course-volume-fields />

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('school.show', $school->id) }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.create') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
