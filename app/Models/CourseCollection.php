<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

class CourseCollection extends Collection
{
    public function getCourses(String $year)
    {
        $list = $this->map(function(School $school){
            return $school->id;
        });

        return Course::whereIn('school_id', $list)
            ->select(['courses.*', 'schools.name as school_name', 'programs.name as program_name'])
            ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
            ->leftJoin('schools', 'courses.school_id', '=', 'schools.id')
            ->withCount('groups')
            ->where(['year' => $year])
            ->orderBy('semester', 'asc')
            ->orderBy('school_name', 'asc')
            ->orderBy('program_name', 'asc')
            ->get();
    }

        
    public function getYears()
    {
        $list = $this->map(function(School $school){
            return $school->id;
        });

        return Course::whereIn('school_id', $list)
            ->select(['year'])
            ->distinct()
            ->get();
    }
}
