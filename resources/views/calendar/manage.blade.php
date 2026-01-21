<x-app-layout>
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label>École de destination</label>
                        <select name="school_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Sélectionner l'école --</option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Fichier .ics</label>
                        <input type="file" name="ics_file">
                    </div>

                    <div class="text-center font-bold text-gray-400">OU</div>

                    <div>
                        <label>Lien direct vers le fichier .ics</label>
                        <input type="url" name="ics_url" placeholder="https://..." class="...">
                    </div>

                    <x-button-primary>Analyser</x-button-primary>
                </div>
            </form>
        </div>
    </section>

    <section class="glass-background">
        <h3>Historique des fichiers importés</h3>
        <div class="overflow-hidden border border-gray-200 rounded-lg">
            <table class="mapping-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">École</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fichier
                            d'origine</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sources as $source)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $source->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm font-medium">{{ $source->school->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 italic">
                            @if($source->url)
                            <a href="{{ $source->url }}" target="_blank" class="text-blue-600 hover:underline">Flux distant</a>
                            @else
                            {{ $source->filename }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
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
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">Aucun fichier
                            importé pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>