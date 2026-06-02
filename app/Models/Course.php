<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Course extends Model
{
    use HasFactory;

    /** @var list<string> */
    public const PROGRAM_SCHOOL_SELECT = [
        'courses.*',
        'schools.name as school_name',
        'programs.name as program_name',
        'programs.short_description as program_short_description',
    ];

    public $timestamps = false;
    public $fillable = ['name', 'short_name', 'sessions', 'session_length', 'school_id', 'program_id', 'year', 'semester', 'rate'];

    protected $withCount = [
        'groups',
    ];

    public function groups(): HasMany
    {
        return $this->HasMany(GroupCourse::class);
    }

    public function program(): BelongsTo
    {
        return $this->BelongsTo(Program::class);
    }

    public function school(): BelongsTo
    {
        return $this->BelongsTo(School::class);
    }

    public function getSchool()
    {

        return School::find($this->school_id);
    }
    /**
     * Groups linked to this course via group_course.
     *
     * @param  bool|null  $active  true = active only, false = inactive only, null = all linked
     */
    public function getLinkedGroups(?bool $active = true)
    {
        $query = Group::select(['groups.*', 'courses.sessions'])
            ->join('group_course', 'group_id', '=', 'groups.id')
            ->join('courses', 'courses.id', '=', 'group_course.course_id')
            ->where('courses.id', '=', $this->id)
            ->orderBy('groups.name');

        if ($active !== null) {
            $query->where('groups.active', $active);
        }

        return $query->get();
    }

    /** @deprecated Use getLinkedGroups() */
    public function getGroups()
    {
        return $this->getLinkedGroups();
    }

    /**
     * Active company groups not yet linked to this course (for reuse across courses).
     */
    public function getAvailableGroups()
    {
        $company = Auth::user()->company;

        $linkedIds = GroupCourse::where('course_id', $this->id)->pluck('group_id');

        return Group::where('company_id', $company->id)
            ->where('active', true)
            ->when($linkedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('groups.id', $linkedIds))
            ->orderBy('name')
            ->get();
    }


    public static function getCourseDetails(String $course_id)
    {
        return Course::select([
                'courses.*',
                'programs.id as program_id',
                'programs.name as program_name',
                'programs.short_description as program_short_description',
            ])
            ->join('programs', 'courses.program_id', '=', 'programs.id')
            ->where('courses.id', '=', $course_id)
            ->get()[0];
    }

    public function programListLabel(): string
    {
        return Program::labelFrom(
            $this->program_short_description ?? null,
            $this->program_name ?? $this->program?->name,
        );
    }

    public static function getCoursesForSchool(String $school_id)
    {
        return Course::select('courses.*')
            ->join('schools', 'courses.school_id', '=', 'schools.id')
            ->where('schools.id', '=', $school_id)
            ->get()[0]
        ;
    }

    public static function getProgramCoursesForCompany(String $program_id)
    {
        $company = Auth::user()->company;

        return Course::select('courses.*')
            ->join('schools', 'courses.school_id', '=', 'schools.id')
            ->where('schools.company_id', '=', $company->id)
            ->where('program_id', '=', $program_id)
            ->get()
        ;
    }
}
