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

                <form action="{{ route('calendar.import') }}" method="POST">
                    @csrf
                    {{-- On passe l'ID de la source pour identifier le fichier en base --}}
                    <input type="hidden" name="source_id" value="{{ $source->id }}">

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Libellé détecté dans l'ICS
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Lier à un Cours ou un Groupe
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($labels as $label)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <code class="text-sm font-mono text-blue-700 bg-blue-50 px-2 py-1 rounded">
                                                {{ $label }}
                                            </code>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                // Vérification si un mapping existe déjà pour ce label et cette école
                                                $saved = $existingMappings[$label] ?? null;
                                                $savedValue = $saved ? (str_replace('App\\Models\\', '', $saved->mappable_type) . ':' . $saved->mappable_id) : '';
                                            @endphp

                                            <select name="mappings[{{ $label }}]" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                <option value="">-- Ignorer cet élément --</option>
                                                
                                                <optgroup label="Cours et tarif associé">
                                                    @foreach($courses as $course)
                                                        <option value="Course:{{ $course->id }}" {{ $savedValue == "Course:{$course->id}" ? 'selected' : '' }}>
                                                            {{ $course->name }} ({{ number_format($course->rate, 2, ',', ' ') }} €)
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="Groupes (Lié à un cours)">
                                                    @foreach($groups as $group)
                                                        <option value="Group:{{ $group->id }}" {{ $savedValue == "Group:{$group->id}" ? 'selected' : '' }}>
                                                            Groupe : {{ $group->name }} ({{ $group->course->name ?? 'Pas de cours lié' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex items-center justify-between border-t pt-6">
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Les liens que vous créez seront mémorisés pour les prochains imports de cette école.
                        </p>
                        <div class="flex space-x-4">
                            <a href="{{ route('calendar.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                                Annuler
                            </a>
                            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-150 ml-4">
                                Valider et Importer le Planning
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>