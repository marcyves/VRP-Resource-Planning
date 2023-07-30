<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'sessions', 'session_length', 'school_id', 'year', 'semester', 'rate'];

    protected $withCount = [
        'groups',
    ];
    
    public function groups(): HasMany
    {
        return $this->HasMany(Group::class);
    }

    public function program(): HasOne
    {
        return $this->hasOne(Program::class);
    }

    public function getGroups()
    {
        return Group::where(['course_id' => $this->id])->get();
    }

    public static function getCourseDetails(String $course_id)
    {
        return Course::select('courses.*', 'programs.id as program_id', 'programs.name as program_name')
        ->join('programs', 'courses.program_id', '=', 'programs.id')
        ->where('courses.id', '=', $course_id)
        ->get()[0]
        ;

    }

}
