<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.group_edit') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    @if(Auth::user()->getMode() == "Edit")
    <section>
        <form action="{{ route('group.update', $group->id) }}" method="post" class="group-form nice-form">
            @csrf
            @method('put')

            <div class="form-group">
                <x-input-label for="course_id">{{ __('messages.course') }}</x-input-label>
                <select name="course_id" id="course_id" class="form-input">
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" @selected($group->course_id == $course->id)>
                        ({{ $course->school->name }}) {{ $course->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="form-group">
                <x-input-label for="short_name">{{ __('messages.short_name') }}</x-input-label>
                <x-text-input type="text" name="short_name" id="short_name" value="{{ old('short_name', $group->short_name) }}" />
                <x-input-error :messages="$errors->get('short_name')" />
            </div>

            <div class="form-group">
                <x-input-label for="size">{{ __('messages.size') }}</x-input-label>
                <x-text-input type="text" name="size" id="size" value="{{ old('size', $group->size) }}" />
                <x-input-error :messages="$errors->get('size')" />
            </div>

            <div class="form-group">
                <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
                <x-text-input type="text" name="year" id="year" value="{{ old('year', $group->year) }}" />
                <x-input-error :messages="$errors->get('year')" />
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('course.show', $group->course_id) }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
            </div>
        </form>
    </section>
    @endif
</x-app-layout>
