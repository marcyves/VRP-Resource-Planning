<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Support\Facades\Auth;

class Course extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'short_name','sessions', 'session_length', 'school_id', 'program_id', 'year', 'semester', 'rate'];

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

    /*

    Get all groups connected to the course

    */
    public function getGroups()
    {

        return Group::select(['groups.*'])
        ->join('group_course', 'group_id', '=', 'groups.id')
        ->where('group_course.course_id', '=', $this->id)
        ->orderBy('groups.name')
        ->get();
    }

    /*

    Get all groups available to the user not connected to the course

    */
    public function getAvailableGroups()
    {
        $company = Auth::user()->getCompany();

        return Group::where('company_id', $company->id)
            ->whereNotIn('groups.id', Group::select(['groups.id'])
                ->join('group_course', 'group_id', '=', 'groups.id')
                ->where('group_course.course_id', '=', $this->id)
                ->orderBy('groups.name')
                ->get())
            ->orderBy('name')
            ->get();
    }


    public static function getCourseDetails(String $course_id)
    {
        return Course::select('courses.*', 'programs.id as program_id', 'programs.name as program_name')
        ->join('programs', 'courses.program_id', '=', 'programs.id')
        ->where('courses.id', '=', $course_id)
        ->get()[0]
        ;

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
        $company = Auth::user()->getCompany();

        return Course::select('courses.*')
        ->join('schools', 'courses.school_id', '=', 'schools.id')
        ->where('schools.company_id', '=', $company->id)
        ->where('program_id', '=', $program_id)
        ->get()
        ;

    }


}
