<x-app-layout>
    @push('styles')
    @vite(['resources/css/calendar-mapping.css'])
    @endpush

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
        <table class="mapping-example-table">
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

            <div class="mapping-source-container">
                <label class="mapping-source-label">
                    Quel champ de l'ICS contient le nom du cours/groupe ?
                </label>
                <select name="ics_source_field" class="mapping-source-select">
                    @foreach ($icsFields as $key => $label)
                    <option value="{{ $key }}">{{ $label }} (Ex:
                        "{{ $exampleEvent[$key] ?? 'vide' }}")</option>
                    @endforeach
                </select>
            </div>

            <table class="mapping-table">
                <thead class="mapping-config-header">
                    <tr>
                        <th>Texte détecté</th>
                        <th class="text-course">Cours (Tarification)</th>
                        <th class="text-group">Groupe (Optionnel)</th>
                    </tr>
                </thead>
                <tbody class="mapping-body">
                    @foreach ($labels as $label)
                    <tr>
                        <td class="label-cell">{{ $label }}</td>

                        <td>
                            <select name="mappings[{{ $label }}][course_id]"
                                class="mapping-select">
                                <option value="">-- Aucun cours --</option>
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


            <div class="mapping-footer">
                <p class="footer-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Les liens que vous créez seront mémorisés pour les prochains imports de cette école.
                </p>
                <div class="footer-actions">
                    <a href="{{ route('calendar.index') }}"
                        class="btn-cancel">
                        Annuler
                    </a>
                    <x-button-primary>Valider et Importer le Planning</x-button-primary>
                </div>
            </div>
        </form>
    </section>
</x-app-layout>