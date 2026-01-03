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
    public function getFirstEventDetails(CalendarSource $source): array
    {
        $target = $source->url ?: storage_path('app/' . $source->storage_path);
        $ical = new \ICal\ICal($target);
        $event = $ical->events()[0] ?? null;

        if (!$event) return [];

        return [
            'summary'     => $event->summary,
            'description' => $event->description,
            'location'    => $event->location,
            'start'       => date('d/m/Y H:i', strtotime($event->dtstart)),
            'end'         => date('d/m/Y H:i', strtotime($event->dtend)),
        ];
    }
    /**
     * Extrait les titres uniques (SUMMARY) d'un fichier ICS pour le mapping.
     */
    public function getUniqueLabelsFromIcs(CalendarSource $source): array
    {
        if ($source->url) {
            $target = $source->url;
        } else {
            // On construit le chemin absolu vers le dossier storage
            $target = storage_path('app/' . $source->storage_path);

            if (!file_exists($target)) {
                throw new \Exception("Le fichier physique est introuvable à l'adresse : " . $target);
            }
        }

        $ical = new ICal($target, [
            'defaultSpan'     => 2,
            'defaultTimeZone' => 'UTC',
        ]);

        return collect($ical->events())
            ->pluck('summary')
            ->unique()
            ->filter()
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
    public function executeFinalImport(CalendarSource $source, array $mappings, string $sourceField = 'summary')
    {
        $events = $this->parseIcsFile($source->filename);

        return DB::transaction(function () use ($events, $source, $mappings, $sourceField) {
            $stats = ['created' => 0, 'skipped' => 0];

            foreach ($events as $event) {
                // Determine the label used for mapping based on user choice
                $label = $event[$sourceField] ?? $event['summary']; 
                $mapping = $mappings[$label] ?? null;

                if (!$mapping) continue;

                // Mapping is now an array: ['course_id' => X, 'group_id' => Y]
                $courseId = $mapping['course_id'] ?? null;
                $groupId  = $mapping['group_id'] ?? null;
                
                if (!$courseId && !$groupId) continue;

                $rate = 0;
                if ($courseId) {
                    $course = Course::find($courseId);
                    if ($course) {
                        $rate = $course->billable_rate;
                    }
                }
                // Determine rate from group if course not explicitly set but group is? 
                // Usually group implies course.
                if ($groupId && !$courseId) {
                    $group = Group::with('course')->find($groupId);
                    if ($group) {
                        $courseId = $group->course_id;
                        $rate = $group->course->billable_rate ?? 0;
                    }
                }

                if ($courseId) {
                    // Collision Detection
                    $exists = Planning::where('school_id', $source->school_id)
                        ->where(function ($query) use ($courseId, $groupId) {
                             if ($groupId) {
                                 $query->where('group_id', $groupId);
                             } else {
                                 $query->where('course_id', $courseId);
                             }
                        })
                        ->where('begin', '<', $event['end'])
                        ->where('end', '>', $event['start'])
                        ->exists();

                    if (!$exists) {
                        Planning::create([
                            'school_id'          => $source->school_id,
                            'calendar_source_id' => $source->id,
                            'course_id'          => $courseId,
                            'group_id'           => $groupId,
                            'begin'              => $event['start'],
                            'end'                => $event['end'],
                            'billable_rate'      => $rate,
                        ]);
                        $stats['created']++;
                    } else {
                        $stats['skipped']++;
                    }
                }
            }
            return $stats;
        });
    }
}
