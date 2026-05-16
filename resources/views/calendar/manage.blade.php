<x-app-layout>
<x-slot name="header">
        <h2>
            {{ __('messages.ics_calendar_management') }}
        </h2>
    </x-slot>

    <section>
        <h3>{{ __('messages.import_new_file') }}</h3>
        <p>https://openclassrooms.com/fr/calendars/7818421-da9af1c19d0c12a77e8c35ba485aef36.ics</p>
        <div class="card">
            <form action="{{ route('calendar.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="calendar-upload-grid">
                    <div class="calendar-upload-field">
                        <label>{{ __('messages.destination_school') }}</label>
                        <select name="school_id" required class="calendar-select-field">
                            <option value="">{{ __('messages.select_school') }}</option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="calendar-upload-field">
                        <label>{{ __('messages.ics_file') }}</label>
                        <input type="file" name="ics_file">
                    </div>

                    <div class="calendar-input-divider">{{ __('messages.or') }}</div>

                    <div class="calendar-upload-field">
                        <label>{{ __('messages.direct_ics_link') }}</label>
                        <input type="url" name="ics_url" placeholder="https://..." class="calendar-url-field">
                    </div>

                    <x-button-primary>{{ __('messages.analyze') }}</x-button-primary>
                </div>
            </form>
        </div>
    </section>

    <section>
        <h3>{{ __('messages.import_history') }}</h3>
        <div class="history-container">
            <table class="history-table">
                <thead class="history-header">
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.school') }}</th>
                        <th>{{ __('messages.source_file') }}</th>
                        <th class="text-right">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="history-body">
                    @forelse($sources as $source)
                    <tr>
                        <td class="history-td-date">{{ $source->created_at->format('d/m/Y H:i') }}</td>
                        <td class="history-td-school">{{ $source->school->name }}</td>
                        <td class="history-td-file">
                            @if($source->url)
                            <a href="{{ $source->url }}" target="_blank" class="text-blue-600 hover:underline">{{ __('messages.remote_feed') }}</a>
                            @else
                            {{ $source->filename }}
                            @endif
                        </td>
                        <td class="history-td-actions">
                            <div class="action-buttons-group">
                                <form action="{{ route('calendar.reimport', $source) }}" method="POST" onsubmit="return confirm(@js(__('messages.calendar_reimport_confirm')))">
                                    @csrf
                                    <x-button-secondary type="submit">{{ __('messages.reimport') }}</x-button-secondary>
                                </form>

                                <form action="{{ route('calendar.destroy', $source) }}" method="POST" onsubmit="return confirm(@js(__('messages.calendar_delete_confirm')))">
                                    @csrf @method('DELETE')
                                    <x-button-danger type="submit">{{ __('messages.delete_import') }}</x-button-danger>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="history-empty-state">{{ __('messages.no_imported_file') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>