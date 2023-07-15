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
                    ->where(['year' => $year])->orderBy('semester')->get();
//        return Course::whereIn('school_id', $list )->get();
    }
}
