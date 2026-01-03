<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Configuration du Mapping</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Fichier : <span class="font-semibold">{{ $source->original_filename }}</span> |
                        École : <span class="font-semibold text-blue-600">{{ $source->school->name }}</span>
                    </p>
                </div>

                <div class="mb-8 p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg">
                    <h4 class="text-amber-800 font-bold mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Analyse du fichier : Exemple d'événement trouvé
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="block text-gray-500 uppercase text-xs font-semibold">Titre (Summary)</span>
                            <span class="font-mono font-bold text-blue-700">{{ $exampleEvent['summary'] }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 uppercase text-xs font-semibold">Horaires</span>
                            <span>Du {{ $exampleEvent['start'] }} au {{ $exampleEvent['end'] }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 uppercase text-xs font-semibold">Lieu</span>
                            <span class="italic text-gray-600">{{ $exampleEvent['location'] ?: 'Non renseigné' }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 uppercase text-xs font-semibold">Description</span>
                            <span class="text-xs truncate block" title="{{ $exampleEvent['description'] }}">
                                {{ Str::limit($exampleEvent['description'], 50) }}
                            </span>
                        </div>
                    </div>
                </div>

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

                    <table class="w-full border-collapse bg-white shadow-sm rounded-lg overflow-hidden">
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
                            <button type="submit"
                                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-150 ml-4">
                                Valider et Importer le Planning
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
