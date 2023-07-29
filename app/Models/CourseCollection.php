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
            ->select(['courses.*', 'programs.name as program_name'])
            ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
            ->withCount('groups')
            ->where(['year' => $year])
            ->orderBy('semester', 'asc')
            ->orderBy('program_id', 'asc')
            ->get();
//        return Course::whereIn('school_id', $list )->get();
    }
}
