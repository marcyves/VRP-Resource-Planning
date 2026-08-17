@props([
    'programs',
    'shortName' => '',
    'programId' => '',
    'year' => '',
    'semester' => '',
])

@php
    $shortName = old('short_name', $shortName);
    $programId = old('program_id', $programId);
    $year = old('year', $year);
    $semester = old('semester', $semester);
@endphp

<div class="form-group course-identity">
    <div class="course-form-row">
        <div class="course-form-row__cluster">
            <div class="course-form-row__field">
                <x-input-label for="short_name">{{ __('messages.short_name') }}</x-input-label>
                <x-text-input
                    type="text"
                    name="short_name"
                    id="short_name"
                    class="course-identity-line__short"
                    value="{{ $shortName }}"
                    placeholder="{{ __('messages.short_name') }}"
                />
            </div>
            <div class="course-form-row__field course-form-row__field--grow">
                <x-input-label for="program_id">{{ __('messages.program') }}</x-input-label>
                <select name="program_id" id="program_id" class="form-input course-identity-line__program">
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" title="{{ $program->name }}" @selected((string) $programId === (string) $program->id)>
                            {{ $program->listLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="course-form-row__cluster">
            <div class="course-form-row__field">
                <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
                <x-text-input
                    type="text"
                    inputmode="numeric"
                    name="year"
                    id="year"
                    class="course-period-line__year"
                    maxlength="8"
                    value="{{ $year }}"
                    placeholder="{{ __('messages.year') }}"
                />
            </div>
            <div class="course-form-row__field">
                <x-input-label for="semester">{{ __('messages.semester') }}</x-input-label>
                <x-text-input
                    type="text"
                    inputmode="numeric"
                    name="semester"
                    id="semester"
                    class="course-period-line__semester"
                    maxlength="4"
                    value="{{ $semester }}"
                    placeholder="{{ __('messages.semester') }}"
                />
            </div>
        </div>
    </div>
    <x-input-error :messages="$errors->get('short_name')" />
    <x-input-error :messages="$errors->get('program_id')" />
    <x-input-error :messages="$errors->get('year')" />
    <x-input-error :messages="$errors->get('semester')" />
</div>
