<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Support\Facades\Lang;

/**
 * School-scoped terminology (e.g. mentoring) without changing the app locale.
 * Only use on school detail screens — never for global UI.
 */
class SchoolContext
{
    public const EDUCATION = 'education';

    public const MENTORING = 'mentoring';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            self::EDUCATION,
            self::MENTORING,
        ];
    }

    public static function isMentoring(?School $school): bool
    {
        return $school !== null && $school->context === self::MENTORING;
    }

    /**
     * Mentoring schools allow a group/student on several courses/activities.
     * Education schools: one group ↔ one course (UI + soft validation).
     */
    public static function allowsMultiCourseLink(?School $school): bool
    {
        return self::isMentoring($school);
    }

    /**
     * Translate a messages.* key, applying mentoring overlays when relevant.
     */
    public static function msg(?School $school, string $key, array $replace = []): string
    {
        if (self::isMentoring($school) && Lang::has("school_mentoring.{$key}")) {
            return __("school_mentoring.{$key}", $replace);
        }

        return __("messages.{$key}", $replace);
    }
}
