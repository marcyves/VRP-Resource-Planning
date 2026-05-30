<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.ics_calendar_management') }}</h2>
    </x-slot>

    <x-scheduling-module-tabs />

    <section class="calendar-upload-section">
        <header class="program-section-header">
            <h3>{{ __('messages.import_new_file') }}</h3>
        </header>

        <form action="{{ route('calendar.upload') }}" method="POST" enctype="multipart/form-data" class="calendar-upload-form nice-form nice-form--wide">
            @csrf

            <div class="calendar-upload-form__grid">
                <div class="form-group">
                    <x-input-label for="school_id">{{ __('messages.destination_school') }}</x-input-label>
                    <select name="school_id" id="school_id" required class="form-input">
                        <option value="">{{ __('messages.select_school') }}</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <x-input-label for="ics_file">{{ __('messages.ics_file') }}</x-input-label>
                    <input type="file" name="ics_file" id="ics_file" class="form-input calendar-upload-form__file" accept=".ics,text/calendar">
                </div>

                <p class="calendar-upload-form__divider" aria-hidden="true">{{ __('messages.or') }}</p>

                <div class="form-group">
                    <x-input-label for="ics_url">{{ __('messages.direct_ics_link') }}</x-input-label>
                    <x-text-input type="url" name="ics_url" id="ics_url" value="{{ old('ics_url') }}" placeholder="https://..." />
                </div>
            </div>

            <div class="form-actions">
                <x-button-primary type="submit">{{ __('messages.analyze') }}</x-button-primary>
            </div>
        </form>
    </section>

    <section class="calendar-history-section">
        <header class="program-section-header">
            <h3>{{ __('messages.import_history') }}</h3>
        </header>

        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.school') }}</th>
                        <th>{{ __('messages.source_file') }}</th>
                        @if (Auth::user()->getMode() == 'Edit')
                            <th>{{ __('messages.actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sources as $source)
                        <tr>
                            <td class="date">{{ $source->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $source->school->name }}</td>
                            <td>
                                @if ($source->url)
                                    <a href="{{ $source->url }}" target="_blank" rel="noopener noreferrer">{{ __('messages.remote_feed') }}</a>
                                @else
                                    {{ $source->filename }}
                                @endif
                            </td>
                            @if (Auth::user()->getMode() == 'Edit')
                                <td class="card-actions">
                                    <form action="{{ route('calendar.reimport', $source) }}" method="POST" onsubmit="return confirm(@js(__('messages.calendar_reimport_confirm')))">
                                        @csrf
                                        <x-button-secondary type="submit">{{ __('messages.reimport') }}</x-button-secondary>
                                    </form>
                                    <form action="{{ route('calendar.destroy', $source) }}" method="POST" onsubmit="return confirm(@js(__('messages.calendar_delete_confirm')))">
                                        @csrf
                                        @method('DELETE')
                                        <x-button-danger type="submit">{{ __('messages.delete_import') }}</x-button-danger>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->getMode() == 'Edit' ? 4 : 3 }}" class="calendar-history-empty">{{ __('messages.no_imported_file') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
