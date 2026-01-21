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
     * Extrait les titres uniques (SUMMARY, DESCRIPTION, etc.) d'un fichier ICS pour le mapping.
     */
    public function getUniqueLabelsFromIcs(CalendarSource $source, string $field = 'summary'): array
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
            ->pluck($field)
            ->unique()
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Parse le fichier ICS pour l'importation finale.
     */
    public function parseIcsFile($source): array
    {
        if ($source instanceof CalendarSource) {
            if ($source->url) {
                $target = $source->url;
            } else {
                $target = storage_path('app/' . $source->storage_path);
            }
        } else {
            // Backward compatibility for string paths
            if (filter_var($source, FILTER_VALIDATE_URL)) {
                $target = $source;
            } else {
                $target = Storage::path('calendars/' . $source);
            }
        }

        if (!filter_var($target, FILTER_VALIDATE_URL) && !file_exists($target)) {
            throw new \Exception("Fichier introuvable : " . $target);
        }

        $ical = new ICal($target, [
            'defaultSpan'     => 2,
            'defaultTimeZone' => 'UTC',
        ]);

        return array_map(function ($event) {
            return [
                'summary'     => $event->summary ?? 'Sans titre',
                'description' => $event->description ?? '',
                'location'    => $event->location ?? '',
                'start'       => date('Y-m-d H:i:s', strtotime($event->dtstart)),
                'end'         => date('Y-m-d H:i:s', strtotime($event->dtend)),
            ];
        }, $ical->events());
    }

    /**
     * Exécute l'importation finale dans la table 'plannings'.
     */
    public function executeFinalImport(CalendarSource $source, array $mappings, string $sourceField = 'summary')
    {
        $events = $this->parseIcsFile($source);

        return DB::transaction(function () use ($events, $source, $mappings, $sourceField) {
            $stats = ['created' => 0, 'skipped' => 0];

            foreach ($events as $event) {
                // Determine the label used for mapping based on user choice
                $label = $event[$sourceField] ?? $event['summary'];
                $mapping = $mappings[$label] ?? null;

                if (!$mapping) {
                    $stats['skipped']++;
                    continue;
                }

                // Mapping is now an array: ['course_id' => X, 'group_id' => Y]
                $courseId = $mapping['course_id'] ?? null;
                $groupId  = $mapping['group_id'] ?? null;

                if (!$courseId && !$groupId) {
                    $stats['skipped']++;
                    continue;
                }

                $rate = 0;
                $sessionLength = 0;
                if ($courseId) {
                    $course = Course::find($courseId);
                    if ($course) {
                        $rate = $course->rate ?? 0;
                        $sessionLength = $course->session_length ?? 0;
                    }
                }

                if ($groupId && !$courseId) {
                    $group = Group::find($groupId);
                    if ($group) {
                        $firstCourse = $group->getCourses()->first();
                        if ($firstCourse) {
                            $courseId = $firstCourse->id;
                            $rate = $firstCourse->rate ?? 0;
                            $sessionLength = $firstCourse->session_length ?? 0;
                        }
                    }
                }

                if ($courseId) {
                    // Collision Detection
                    $exists = Planning::where(function ($query) use ($courseId, $groupId) {
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
                            'calendar_source_id' => $source->id,
                            'course_id'          => $courseId,
                            'group_id'           => $groupId,
                            'begin'              => $event['start'],
                            'end'                => $event['end'],
                            'location'           => $event['location'] ?? '',
                            'billable_rate'      => $rate,
                        ]);
                        $stats['created']++;
                    } else {
                        $stats['skipped']++;
                    }
                } else {
                    $stats['skipped']++;
                }
            }
            return $stats;
        });
    }
}
