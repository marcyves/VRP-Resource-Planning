<?php

namespace App\Support;

use App\Models\Course;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SchoolCourseOptions
{
    /**
     * Schools that have at least one usable (non-archived) course.
     */
    public static function schoolsFor(User $user): Collection
    {
        return static::schoolsQuery($user)->get();
    }

    public static function schoolsQuery(User $user): Builder
    {
        return School::query()
            ->where('schools.company_id', $user->company_id)
            ->whereHas('courses', fn (Builder $query) => $query->active())
            ->orderBy('schools.name');
    }

    /**
     * Usable courses for a school dropdown, sorted like the school course table.
     */
    public static function coursesForSchool(School $school, User $user, ?int $includeCourseId = null): Collection
    {
        if ((int) $school->company_id !== (int) $user->company_id) {
            return new Collection;
        }

        return static::coursesQuery($user, $school->id, $includeCourseId)->get();
    }

    /**
     * Usable courses for company-wide dropdowns (planning edit).
     */
    public static function coursesForCompany(User $user, ?int $includeCourseId = null): Collection
    {
        return static::coursesQuery($user, null, $includeCourseId)->get();
    }

    public static function coursesQuery(User $user, ?int $schoolId = null, ?int $includeCourseId = null): Builder
    {
        $query = Course::query()
            ->select(Course::PROGRAM_SCHOOL_SELECT)
            ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
            ->leftJoin('schools', 'courses.school_id', '=', 'schools.id')
            ->where('schools.company_id', $user->company_id)
            ->where(function (Builder $courseQuery) use ($includeCourseId) {
                $courseQuery->active();

                if ($includeCourseId) {
                    $courseQuery->orWhere('courses.id', $includeCourseId);
                }
            });

        if ($schoolId) {
            $query->where('courses.school_id', $schoolId);
        }

        return Course::applyListOrder($query);
    }
}
