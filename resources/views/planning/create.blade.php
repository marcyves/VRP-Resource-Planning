<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.group_planning') }}: {{ old('date', $date) }}</h2>
    </x-slot>

    <x-scheduling-module-tabs />

    <section>
        <form action="{{ route('planning.store') }}" method="post" class="group-form nice-form planning-session-create-form">
            @csrf
            <input type="hidden" name="date" value="{{ old('date', $date) }}">
            <input type="hidden" name="course" value="{{ old('course', $course->id) }}">
            <input type="hidden" name="session_length" value="{{ old('session_length', $session_length) }}">

            <div class="planning-session-create-form__assignments">
                <div class="form-group planning-session-create-form__assignment">
                    <label for="group" class="form-label">{{ __('messages.group') }}</label>
                    <select id="group" name="group" class="form-input">
                        <option value="0" @selected((string) old('group', '0') === '0')>{{ __('messages.new_group_below') }}</option>
                        @foreach ($groups as $group)
                        @if($group->sessions == 0 or $group->sessions == $course->sessions)
                        <option value="{{ $group->id }}" @selected((string) old('group', '0') === (string) $group->id)>
                            {{ $group->name }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('group')" />
                </div>

                <div class="form-group planning-session-create-form__assignment">
                    <label for="begin-hour" class="form-label">{{ __('messages.begin') }}</label>
                    <div class="planning-time-fields">
                        <select id="begin-hour" name="hour" class="form-input">
                            @for ($h = 8; $h < 20; $h++)
                                <option value="{{ $h }}" @selected((string) old('hour', '8') === (string) $h)>{{ $h }}</option>
                            @endfor
                        </select>
                        <select name="minutes" class="form-input" aria-label="{{ __('messages.begin') }}">
                            @for ($m = 0; $m < 60; $m += 5)
                                <option value="{{ $m }}" @selected((string) old('minutes', '0') === (string) $m)>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                    <x-input-error :messages="$errors->get('hour')" />
                    <x-input-error :messages="$errors->get('minutes')" />
                </div>
            </div>

            <div class="planning-session-create-form__fields">
                <x-form-group-create :course_id="$course->id" :details-row="true" />
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('planning.index') }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.plan') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
