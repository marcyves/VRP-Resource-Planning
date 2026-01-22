<x-app-layout>
    @push('styles')
    @vite(['resources/css/calendar-manage.css'])
    @endpush

    <x-slot name="header">
        <h2>
            {{ __('Gestion des Calendriers ICS') }}
        </h2>
    </x-slot>

    <section class="glass-background">
        <h3>Importer un nouveau fichier</h3>
        <p>https://openclassrooms.com/fr/calendars/7818421-da9af1c19d0c12a77e8c35ba485aef36.ics</p>
        <div class="card glass-background">
            <form action="{{ route('calendar.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="calendar-upload-grid">
                    <div class="calendar-upload-field">
                        <label>École de destination</label>
                        <select name="school_id" required class="calendar-select-field">
                            <option value="">-- Sélectionner l'école --</option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="calendar-upload-field">
                        <label>Fichier .ics</label>
                        <input type="file" name="ics_file">
                    </div>

                    <div class="calendar-input-divider">OU</div>

                    <div class="calendar-upload-field">
                        <label>Lien direct vers le fichier .ics</label>
                        <input type="url" name="ics_url" placeholder="https://..." class="calendar-url-field">
                    </div>

                    <x-button-primary>Analyser</x-button-primary>
                </div>
            </form>
        </div>
    </section>

    <section class="glass-background">
        <h3>Historique des fichiers importés</h3>
        <div class="history-container">
            <table class="history-table">
                <thead class="history-header">
                    <tr>
                        <th>Date</th>
                        <th>École</th>
                        <th>Fichier d'origine</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="history-body">
                    @forelse($sources as $source)
                    <tr>
                        <td class="history-td-date">{{ $source->created_at->format('d/m/Y H:i') }}</td>
                        <td class="history-td-school">{{ $source->school->name }}</td>
                        <td class="history-td-file">
                            @if($source->url)
                            <a href="{{ $source->url }}" target="_blank" class="text-blue-600 hover:underline">Flux distant</a>
                            @else
                            {{ $source->filename }}
                            @endif
                        </td>
                        <td class="history-td-actions">
                            <div class="action-buttons-group">
                                <form action="{{ route('calendar.reimport', $source) }}" method="POST" onsubmit="return confirm('Relancer l\'importation mettra à jour le planning avec les mappings existants. Continuer ?')">
                                    @csrf
                                    <x-button-secondary type="submit">Relancer l'import</x-button-secondary>
                                </form>

                                <form action="{{ route('calendar.destroy', $source) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet historique ?')">
                                    @csrf @method('DELETE')
                                    <x-button-danger type="submit">Supprimer l'import</x-button-danger>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="history-empty-state">Aucun fichier importé pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>