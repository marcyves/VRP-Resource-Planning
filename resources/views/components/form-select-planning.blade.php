@if (Auth::user()->getMode() == 'Edit')
    @php
        $monthPadded = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $dayPadded = str_pad((string) $day, 2, '0', STR_PAD_LEFT);
    @endphp

    <form action="{{ route('planning.create.start') }}" method="post" class="planning-create-form">
        @csrf

        <input
            type="date"
            id="planning-create-date"
            name="date"
            class="planning-create-form__control planning-create-form__control--date"
            value="{{ $year }}-{{ $monthPadded }}-{{ $dayPadded }}"
            aria-label="{{ __('validation.attributes.date') }}"
        >

        <label for="planning-create-course" class="planning-create-form__label">{{ __('messages.course') }}</label>
        <select
            id="planning-create-course"
            name="course"
            class="planning-create-form__control planning-create-form__control--course"
            required
            onchange="this.form.submit()"
        >
            @if ($mode == 'selected')
                <optgroup label="{{ $schools->name }}">
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" selected>
                            ({{ $course->program_name }}) {{ $course->name }}
                        </option>
                    @endforeach
                </optgroup>
            @elseif ($mode == 'single')
                <optgroup label="{{ $schools->name }}">
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}">
                            ({{ $course->program_name }}) {{ $course->name }}
                        </option>
                    @endforeach
                </optgroup>
            @else
                @foreach ($schools as $school)
                    <optgroup label="{{ $school->name }}">
                        @foreach ($courses as $course)
                            @if ($school->id == $course->school_id)
                                <option value="{{ $course->id }}">
                                    ({{ $course->program_name }}) {{ $course->name }}
                                </option>
                            @endif
                        @endforeach
                    </optgroup>
                @endforeach
            @endif
        </select>

        <button type="submit" class="planning-create-form__submit" aria-label="{{ __('actions.confirm') }}">
            Ok
        </button>
    </form>
@endif
