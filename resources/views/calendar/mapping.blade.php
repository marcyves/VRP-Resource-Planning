<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('Configuration du Mapping') }}
        </h2>
    </x-slot>

    <section class="glass-background">
        @if($source->url)
        Lien : {{ $source->url }}<br>
        @endif
        Fichier : {{ $source->filename }} |
        École : {{ $source->school->name }}
    </section>

    <section class="glass-background">
        <h4>
            Analyse du fichier : Exemple d'événement trouvé
        </h4>
        <table>
            <tr>
                <td>
                    Titre (Summary)
                </td>
                <td>
                    {{ $exampleEvent['summary'] }}
                </td>
            </tr>
            <tr>
                <td>
                    Horaires
                </td>
                <td>
                    Du {{ $exampleEvent['start'] }} au {{ $exampleEvent['end'] }}
                </td>
            </tr>
            <tr>
                <td>
                    Lieu
                </td>
                <td>
                    {{ $exampleEvent['location'] ?: 'Non renseigné' }}
                </td>
            </tr>
            <tr>
                <td>
                    Description
                </td>
                <td>
                    {{ Str::limit($exampleEvent['description'], 80) }}
                </td>
            </tr>
        </table>
        <form action="{{ route('calendar.import') }}" method="POST">
            @csrf
            <input type="hidden" name="source_id" value="{{ $source->id }}">

            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <label class="block text-sm font-bold text-blue-800 mb-2">
                    Quel champ de l'ICS contient le nom du cours/groupe ?
                </label>
                <select name="ics_source_field" class="rounded border-blue-300">
                    @foreach ($icsFields as $key => $label)
                    <option value="{{ $key }}">{{ $label }} (Ex:
                        "{{ $exampleEvent[$key] ?? 'vide' }}")</option>
                    @endforeach
                </select>
            </div>

            <table class="mapping-table">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 border text-left">Texte détecté</th>
                        <th class="p-3 border text-left text-blue-700">Cours (Tarification)</th>
                        <th class="p-3 border text-left text-green-700">Groupe (Optionnel)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($labels as $label)
                    <tr>
                        <td class="p-3 border font-mono text-xs bg-gray-50">{{ $label }}</td>

                        <td class="p-3 border">
                            <select name="mappings[{{ $label }}][course_id]"
                                class="w-full text-sm border-gray-300 rounded">
                                <option value="">-- Aucun cours --</option>
                                @foreach ($courses as $course)
                                <option value="{{ $course->id }}">
                                    {{ $course->name }}
                                    ({{ number_format($course->rate, 2, ',', ' ') }} €)
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td class="p-3 border">
                            <select name="mappings[{{ $label }}][group_id]"
                                class="w-full text-sm border-gray-300 rounded">
                                <option value="">-- Aucun groupe --</option>
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


            <div class="mt-8 flex items-center justify-between border-t pt-6">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Les liens que vous créez seront mémorisés pour les prochains imports de cette école.
                </p>
                <div class="flex space-x-4">
                    <a href="{{ route('calendar.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        Annuler
                    </a>
                    <x-button-primary>Valider et Importer le Planning</x-button-primary>
                </div>
            </div>
        </form>

        </div>
        </div>
        </div>
</x-app-layout>