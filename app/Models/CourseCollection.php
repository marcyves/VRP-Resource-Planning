<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CourseCollection extends Collection
{
    public function getCourses(String $year, String $semester)
    {
        $list = $this->map(function(School $school){
            return $school->id;
        });

        if ($year == 'all'){
            if ($semester == 'all'){
                return Course::whereIn('school_id', $list)
            ->select(['courses.*', 'schools.name as school_name', 'programs.name as program_name'])
            ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
            ->leftJoin('schools', 'courses.school_id', '=', 'schools.id')
            ->withCount('groups')
            ->orderBy('year', 'asc')
            ->orderBy('semester', 'asc')
            ->orderBy('school_name', 'asc')
            ->orderBy('program_name', 'asc')
            ->get();
            }else{
                return Course::whereIn('school_id', $list)
                ->select(['courses.*', 'schools.name as school_name', 'programs.name as program_name'])
                ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
                ->leftJoin('schools', 'courses.school_id', '=', 'schools.id')
                ->withCount('groups')
                ->where(['semester' => $semester])
                ->orderBy('semester', 'asc')
                ->orderBy('school_name', 'asc')
                ->orderBy('program_name', 'asc')
                ->get();
            }
        }else{
            if ($semester == 'all'){
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
            }else{
                return Course::whereIn('school_id', $list)
                ->select(['courses.*', 'schools.name as school_name', 'programs.name as program_name'])
                ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
                ->leftJoin('schools', 'courses.school_id', '=', 'schools.id')
                ->withCount('groups')
                ->where(['semester' => $semester])
                ->where(['year' => $year])
                ->orderBy('semester', 'asc')
                ->orderBy('school_name', 'asc')
                ->orderBy('program_name', 'asc')
                ->get();
            }
        }
    }

    public function getNoCourse()
    {
        $list = $this->map(function(School $school){
            return $school->id;
        });
        $schools_without_course = School::whereIn('schools.id', $list)
        ->whereDoesntHave('courses', function (Builder $query){})->get();
        return $schools_without_course;
    }
        
    public function getYears()
    {
        $list = $this->map(function(School $school){
            return $school->id;
        });

        return Course::whereIn('school_id', $list)
            ->select(['year', 'semester'])
            ->distinct()
            ->get();
    }
}
