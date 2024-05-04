<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        return $this->HasMany(Group::class);
    }

    public function program(): BelongsTo
    {
        return $this->BelongsTo(Program::class);
    }

    public function school(): BelongsTo
    {
        return $this->BelongsTo(School::class);
    }

    public function getGroups()
    {
        return Group::where(['course_id' => $this->id])->orderBy('name', 'asc')->get();
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

}
