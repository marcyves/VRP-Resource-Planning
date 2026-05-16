<x-app-layout>
<x-slot name="header">
        <h2>
            {{ __('messages.mapping_configuration') }}
        </h2>
    </x-slot>

    <section>
        @if($source->url)
        {{ __('messages.link') }} : {{ $source->url }}<br>
        @endif
        {{ __('messages.file') }} : {{ $source->filename }} |
        {{ __('messages.school') }} : {{ $source->school->name }}
    </section>

    <section>
        <h4>
            {{ __('messages.file_analysis_event_example') }}
        </h4>
        <table class="mapping-example-table">
            <tr>
                <td>
                    {{ __('messages.summary_title') }}
                </td>
                <td>
                    {{ $exampleEvent['summary'] }}
                </td>
            </tr>
            <tr>
                <td>
                    {{ __('messages.schedule') }}
                </td>
                <td>
                    {{ __('messages.from') }} {{ $exampleEvent['start'] }} {{ __('messages.to') }} {{ $exampleEvent['end'] }}
                </td>
            </tr>
            <tr>
                <td>
                    {{ __('messages.location') }}
                </td>
                <td>
                    {{ $exampleEvent['location'] ?: __('messages.not_provided') }}
                </td>
            </tr>
            <tr>
                <td>
                    {{ __('messages.description') }}
                </td>
                <td>
                    {{ Str::limit($exampleEvent['description'], 80) }}
                </td>
            </tr>
        </table>
        <form action="{{ route('calendar.import') }}" method="POST">
            @csrf
            <input type="hidden" name="source_id" value="{{ $source->id }}">

            <div class="mapping-source-container">
                <label class="mapping-source-label">
                    {{ __('messages.ics_source_field_question') }}
                </label>
                <select name="ics_source_field" class="mapping-source-select">
                    @foreach ($icsFields as $key => $label)
                    <option value="{{ $key }}">{{ $label }} ({{ __('messages.example_abbr') }}:
                        "{{ $exampleEvent[$key] ?? __('messages.empty') }}")</option>
                    @endforeach
                </select>
            </div>

            <table class="mapping-table">
                <thead class="mapping-config-header">
                    <tr>
                        <th>{{ __('messages.detected_text') }}</th>
                        <th class="text-course">{{ __('messages.course_pricing') }}</th>
                        <th class="text-group">{{ __('messages.group_optional') }}</th>
                    </tr>
                </thead>
                <tbody class="mapping-body">
                    @foreach ($labels as $label)
                    <tr>
                        <td class="label-cell">{{ $label }}</td>

                        <td>
                            <select name="mappings[{{ $label }}][course_id]"
                                class="mapping-select">
                                <option value="">{{ __('messages.no_course') }}</option>
                                @foreach ($courses as $course)
                                <option value="{{ $course->id }}">
                                    {{ $course->name }}
                                    ({{ number_format($course->rate, 2, ',', ' ') }} €)
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <select name="mappings[{{ $label }}][group_id]"
                                class="mapping-select">
                                <option value="">{{ __('messages.no_group') }}</option>
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}">
                                    {{ $group->name }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>


            <div class="mapping-footer">
                <p class="footer-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ __('messages.calendar_mapping_memory_notice') }}
                </p>
                <div class="footer-actions">
                    <a href="{{ route('calendar.index') }}"
                        class="btn-cancel">
                        {{ __('messages.cancel') }}
                    </a>
                    <x-button-primary>{{ __('messages.validate_and_import_planning') }}</x-button-primary>
                </div>
            </div>
        </form>
    </section>
</x-app-layout>