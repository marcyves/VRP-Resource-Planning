<?php

namespace App\Http\Controllers;

use App\Services\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\CalendarSource;
use App\Models\CalendarMapping;
use App\Models\Course;
use App\Models\Group;

class CalendarFileController extends Controller
{
    protected $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function index()
    {
        // On récupère tous les fichiers .ics du dossier
        $files = Storage::files('calendars');
        $schools = Auth::user()->getSchools();
        $sources = CalendarSource::with('school')->latest()->get();

        return view('calendar.manage', compact('files', 'schools', 'sources'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'ics_file'  => 'required|file|mimes:ics,txt'
        ]);

        // 1. Stockage physique dans storage/app/calendars
        $path = $request->file('ics_file')->store('calendars');

        // 2. Enregistrement dans la table calendar_sources
        $source = CalendarSource::create([
            'school_id'         => $request->school_id,
            'filename' => $request->file('ics_file')->getClientOriginalName()
        ]);

        // 3. Analyse du contenu pour le mapping
        $labels = $this->calendarService->getUniqueLabelsFromIcs($path);

        // Récupérer les mappings existants pour cette école
        $existingMappings = CalendarMapping::where('school_id', $request->school_id)
            ->whereIn('ics_label', $labels)
            ->get()
            ->keyBy('ics_label');

        // 4. Redirection vers la vue de mapping en passant l'ID de la source
        return view('calendar.mapping', [
            'source'   => $source,
            'labels'   => $labels,
            'existingMappings' => $existingMappings,
            'courses'  => Course::where('school_id', $source->school_id)->get(),
            'groups'   => Auth::user()->getGroups(),
        ]);

        /*
        $request->validate(['ics_file' => 'required|file']);
        $path = $request->file('ics_file')->storeAs('calendars', $request->file('ics_file')->getClientOriginalName());
        return back()->with('success', 'Fichier transféré avec succès.');
        */
    }

    /*     public function import($filename)
    {
        try {
            // On délègue au service l'importation avec matching automatique
            $events = $this->calendarService->parseIcsFile($filename);
            $results = $this->calendarService->importWithAutoMatching($events);

            return back()->with('success', "Importation réussie : {$results['imported']} sessions ajoutées.");
        } catch (\Exception $e) {
            session()->flash('danger', "Erreur lors de l'importation du fichier ICS : ".$e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    } */

    public function import(Request $request)
    {
        // 1. Validation de base
        $request->validate([
            'source_id' => 'required|exists:calendar_sources,id',
            'mappings'  => 'required|array'
        ]);

        // 2. Récupération de la source (le fichier et l'école)
        $source = CalendarSource::with('school')->findOrFail($request->source_id);

        // 3. Extraction et sauvegarde des mappings pour le futur
        // On boucle sur les choix de l'utilisateur pour enrichir la table 'calendar_mappings'
        foreach ($request->mappings as $label => $mappingValue) {
            if (empty($mappingValue)) continue;

            // On décompose "Course:5" ou "Group:12"
            [$type, $id] = explode(':', $mappingValue);
            $modelClass = "App\\Models\\" . $type;

            // On enregistre ce lien : la prochaine fois que ce label apparaît pour cette école, 
            // on pourra pré-remplir le formulaire ou automatiser.
            \App\Models\CalendarMapping::updateOrCreate(
                [
                    'school_id' => $source->school_id,
                    'ics_label' => $label
                ],
                [
                    'mappable_type' => $modelClass,
                    'mappable_id'   => $id
                ]
            );
        }

        // 4. Appel au service pour créer les enregistrements dans 'plannings'
        try {
            $this->calendarService->executeFinalImport($source, $request->mappings);

            return redirect()->route('calendar.index')
                ->with('success', "L'importation du fichier [{$source->filename}] a été réalisée avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', "Une erreur est survenue lors de l'insertion en base : " . $e->getMessage());
        }
    }


    public function destroy(CalendarSource $source)
    {
        try {
            // 1. Suppression du fichier physique
            if (Storage::exists('calendars/' . $source->filename)) {
                Storage::delete('calendars/' . $source->filename);
            }

            // 2. Suppression de l'entrée en base
            $deleted = $source->delete();

            if ($deleted) {
                return redirect()->route('calendar.index')
                    ->with('success', 'Le fichier et toutes les sessions associées ont été supprimés.');
            }

            return redirect()->back()->with('error', 'La suppression a échoué en base de données.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}
