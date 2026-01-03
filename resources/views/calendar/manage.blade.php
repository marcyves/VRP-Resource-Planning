<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Gestion des Calendriers ICS</h2>

                <div class="mb-10 p-6 bg-blue-50 border border-blue-100 rounded-xl">
                    <h3 class="text-lg font-semibold mb-4 text-blue-800">Importer un nouveau fichier</h3>
                    <form action="{{ route('calendar.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">École de destination</label>
                                <select name="school_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Sélectionner l'école --</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Fichier .ics</label>
                                <input type="file" name="ics_file">
                            </div>

                            <div class="text-center font-bold text-gray-400">OU</div>

                            <div>
                                <label class="block text-sm font-medium">Lien URL (ICS)</label>
                                <input type="url" name="ics_url" placeholder="https://..." class="...">
                            </div>

                            <button type="submit" class="bg-blue-600 text-white p-2 rounded">Analyser</button>
                        </div>
                    </form>
                </div>

                <h3 class="text-lg font-semibold mb-4">Historique des fichiers importés</h3>
                <div class="overflow-hidden border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
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
                                    <td class="px-6 py-4 text-sm text-gray-600 italic">{{ $source->original_filename }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <form action="{{ route('calendar.destroy', $source->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:text-red-900">Supprimer l'import</button>
                                        </form>
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
            </div>
        </div>
    </div>
</x-app-layout>
