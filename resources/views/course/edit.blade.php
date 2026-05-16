<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.course_update') }}</h2>
    </x-slot>

    <section>
        @isset($course)
        <form action="{{route('course.update', $course->id)}}" method="post" class="group-form">
            @csrf
            @method('put')

            <div class="form-group">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{old('name',$course->name)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="short_name">{{ __('messages.short_name') }}</x-input-label>
                <x-text-input type="text" name="short_name" id="short_name" value="{{old('short_name',$course->short_name)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="program_id">{{ __('messages.program') }}</x-input-label>
                <select name="program_id" id="program_id" class="form-input">
                    @foreach ($programs as $program)
                    <option value="{{$program->id}}" @if($program->id==$course->program_id) selected @endif>
                        {{$program->name}}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <x-input-label for="sessions">{{ __('messages.sessions') }}</x-input-label>
                <x-text-input type="text" name="sessions" id="sessions" value="{{old('sessions',$course->sessions)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="session_length">{{ __('messages.session_length') }}</x-input-label>
                <x-text-input type="text" name="session_length" id="session_length" value="{{old('session_length',$course->session_length)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="rate">{{ __('messages.rate') }}</x-input-label>
                <x-text-input type="text" name="rate" id="rate" value="{{old('rate',$course->rate)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
                <x-text-input type="text" name="year" id="year" value="{{old('year',$course->year)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="semester">{{ __('messages.semester') }}</x-input-label>
                <x-text-input type="text" name="semester" id="semester" value="{{old('semester',$course->semester)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="recurring">{{ __('messages.recurring') }}</x-input-label>
                <input type="checkbox" name="recurring" id="recurring" value="1" @if($course->recurring) checked @endif class="form-checkbox" />
            </div>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
            </div>
        </form>
        @endisset
    </section>
</x-app-layout>