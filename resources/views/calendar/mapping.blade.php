<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.mapping_configuration') }}</h2>
    </x-slot>

    <x-scheduling-module-tabs active="calendar" />

    <section class="calendar-mapping-meta">
        <div class="company-show-grid">
            <article class="company-show-card">
                <h3>{{ __('messages.source_file') }}</h3>
                <ul>
                    @if ($source->url)
                        <li>
                            {{ __('messages.link') }}:
                            <a href="{{ $source->url }}" target="_blank" rel="noopener noreferrer">{{ __('messages.remote_feed') }}</a>
                        </li>
                    @endif
                    <li>{{ __('messages.file') }}: {{ $source->filename }}</li>
                    <li>{{ __('messages.school') }}: {{ $source->school->name }}</li>
                </ul>
            </article>
        </div>
    </section>

    <section class="calendar-mapping-section">
        <header class="program-section-header">
            <h3>{{ __('messages.file_analysis_event_example') }}</h3>
        </header>

        <div class="data-table data-table--flat">
            <table>
                <tbody>
                    <tr>
                        <th scope="row">{{ __('messages.summary_title') }}</th>
                        <td>{{ $exampleEvent['summary'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('messages.schedule') }}</th>
                        <td>{{ __('messages.from') }} {{ $exampleEvent['start'] }} {{ __('messages.to') }} {{ $exampleEvent['end'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('messages.location') }}</th>
                        <td>{{ $exampleEvent['location'] ?: __('messages.not_provided') }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('messages.description') }}</th>
                        <td>{{ Str::limit($exampleEvent['description'], 80) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="calendar-mapping-section">
        <form action="{{ route('calendar.import') }}" method="POST" class="calendar-mapping-form nice-form nice-form--wide">
            @csrf
            <input type="hidden" name="source_id" value="{{ $source->id }}">

            <div class="form-group">
                <x-input-label for="ics_source_field">{{ __('messages.ics_source_field_question') }}</x-input-label>
                <select name="ics_source_field" id="ics_source_field" class="form-input">
                    @foreach ($icsFields as $key => $label)
                        <option value="{{ $key }}">{{ $label }} ({{ __('messages.example_abbr') }}: "{{ $exampleEvent[$key] ?? __('messages.empty') }}")</option>
                    @endforeach
                </select>
            </div>

            <header class="program-section-header">
                <h3>{{ __('messages.mapping_configuration') }}</h3>
            </header>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.detected_text') }}</th>
                            <th>{{ __('messages.course_pricing') }}</th>
                            <th>{{ __('messages.group_optional') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($labels as $label)
                            @php
                                $existing = $existingMappings[$label] ?? null;
                                $selectedCourse = old("mappings.{$label}.course_id", $existing && $existing->mappable_type === \App\Models\Course::class ? $existing->mappable_id : '');
                                $selectedGroup = old("mappings.{$label}.group_id", $existing && $existing->mappable_type === \App\Models\Group::class ? $existing->mappable_id : '');
                            @endphp
                            <tr>
                                <td class="calendar-mapping-label">{{ $label }}</td>
                                <td>
                                    <select name="mappings[{{ $label }}][course_id]" class="form-input">
                                        <option value="">{{ __('messages.no_course') }}</option>
                                        @foreach ($courses as $course)
                                            <option value="{{ $course->id }}" @selected($selectedCourse == $course->id)>
                                                {{ $course->name }} ({{ number_format($course->rate, 2, ',', ' ') }} €)
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="mappings[{{ $label }}][group_id]" class="form-input">
                                        <option value="">{{ __('messages.no_group') }}</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}" @selected($selectedGroup == $group->id)>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="form-hint calendar-mapping-form__notice">{{ __('messages.calendar_mapping_memory_notice') }}</p>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('calendar.index') }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.validate_and_import_planning') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
