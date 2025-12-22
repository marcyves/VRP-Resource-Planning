<?php

namespace App\Services;

use ICal\ICal;
use Exception;
use App\Models\Planning;
use App\Models\Course;
use App\Models\Group;
use App\Models\CalendarSource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CalendarService
{
    /**
     * Extrait les titres uniques (SUMMARY) d'un fichier ICS pour le mapping.
     */
    public function getUniqueLabelsFromIcs(string $storagePath): array
    {
        $filePath = storage_path('app/' . $storagePath);

        if (!file_exists($filePath)) {
            throw new Exception("Le fichier est introuvable sur le serveur.");
        }

        $ical = new ICal($filePath, [
            'defaultSpan'     => 2,
            'defaultTimeZone' => 'UTC',
        ]);

        // On récupère uniquement les titres (summary) uniques
        return collect($ical->events())
            ->pluck('summary')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Parse le fichier ICS pour l'importation finale.
     */
    public function parseIcsFile(string $filename): array
    {
        $filePath = Storage::path('calendars/' . $filename);

        $ical = new ICal($filePath, [
            'defaultSpan'     => 2,
            'defaultTimeZone' => 'UTC',
        ]);

        return array_map(function ($event) {
            return [
                'summary' => $event->summary ?? 'Sans titre',
                'start'   => date('Y-m-d H:i:s', strtotime($event->dtstart)),
                'end'     => date('Y-m-d H:i:s', strtotime($event->dtend)),
            ];
        }, $ical->events());
    }

    /**
     * Exécute l'importation finale dans la table 'plannings'.
     */
    public function executeFinalImport(CalendarSource $source, array $mappings)
    {
        $events = $this->parseIcsFile($source->filename);

        return DB::transaction(function () use ($events, $source, $mappings) {
            foreach ($events as $event) {
                $label = $event['summary'];
                $target = $mappings[$label] ?? null;

                if (!$target) continue;

                // On sépare "Course:5" en ["Course", "5"]
                [$type, $id] = explode(':', $target);
                
                $courseId = null;
                $groupId = null;
                $rate = 0;

                if ($type === 'Course') {
                    $course = Course::find($id);
                    if ($course) {
                        $courseId = $course->id;
                        $rate = $course->billable_rate;
                    }
                } elseif ($type === 'Group') {
                    $group = Group::with('course')->find($id);
                    if ($group) {
                        $groupId = $group->id;
                        $courseId = $group->course_id;
                        $rate = $group->course->billable_rate ?? 0;
                    }
                }

                if ($courseId) {
                    Planning::create([
                        'school_id'          => $source->school_id,
                        'calendar_source_id' => $source->id,
                        'course_id'          => $courseId,
                        'group_id'           => $groupId,
                        'begin'              => $event['start'],
                        'end'                => $event['end'],
                        'billable_rate'      => $rate,
                    ]);
                }
            }
        });
    }
}