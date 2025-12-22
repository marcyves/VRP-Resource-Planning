<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Group;
use Illuminate\Support\Str;

class CalendarMatcher
{
    /**
     * Tente de trouver le cours et le groupe à partir du titre ICS.
     */
    public function matchEventData(string $summary): array
    {
        // 1. Nettoyage du titre
        $summary = Str::lower($summary);

        // 2. Recherche du cours
        $allCourses = Course::all();
        $matchedCourse = $allCourses->first(function ($course) use ($summary) {
            return Str::contains($summary, Str::lower($course->name));
        });

        // 3. Recherche du groupe
        $allGroups = Group::all();
        $matchedGroup = $allGroups->first(function ($group) use ($summary) {
            return Str::contains($summary, Str::lower($group->name));
        });

        return [
            'course_id' => $matchedCourse?->id,
            'group_id'  => $matchedGroup?->id,
            'is_valid'  => $matchedCourse !== null // Valide si au moins le cours est trouvé
        ];
    }
}