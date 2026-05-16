<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.course_create') }} {{$school->name}}</h2>
    </x-slot>

    <section>
        <form action="{{route('course.store', $school->id)}}" method="post" class="group-form">
            @csrf

            <div class="form-group">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" placeholder="{{ __('messages.name') }}" />
            </div>

            <div class="form-group">
                <x-input-label for="short_name">{{ __('messages.short_name') }}</x-input-label>
                <x-text-input type="text" name="short_name" id="short_name" placeholder="{{ __('messages.short_name') }}" />
            </div>

            <div class="form-group">
                <x-input-label for="program_id">{{ __('messages.program') }}</x-input-label>
                <select name="program_id" id="program_id" class="form-input">
                    @foreach ($programs as $program)
                    <option value="{{$program->id}}">{{$program->name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <x-input-label for="sessions">{{ __('messages.number_of_sessions') }}</x-input-label>
                <x-text-input type="text" name="sessions" id="sessions" placeholder="{{ __('messages.number_of_sessions') }}" />
            </div>

            <div class="form-group">
                <x-input-label for="session_length">{{ __('messages.session_length') }}</x-input-label>
                <x-text-input type="text" name="session_length" id="session_length" placeholder="{{ __('messages.session_length') }}" />
            </div>

            <div class="form-group">
                <x-input-label for="rate">{{ __('messages.rate') }}</x-input-label>
                <x-text-input type="text" name="rate" id="rate" placeholder="{{ __('messages.rate') }}" />
            </div>

            <div class="form-group">
                <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
                <x-text-input type="text" name="year" id="year" value="{{now()->format('Y')}}" placeholder="{{ __('messages.year') }}" />
            </div>

            <div class="form-group">
                <x-input-label for="semester">{{ __('messages.semester') }}</x-input-label>
                <x-text-input type="text" name="semester" id="semester" placeholder="{{ __('messages.semester') }}" />
            </div>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.create') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>