<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CourseCollection extends Collection
{
    public function getPlanning(String $year, String $month)
    {
        $list = $this->map(function(School $school){
            return $school->id;
        });
       
        $start_date =  trim($year)."-".substr("0".trim($month),-2)."-0 00:00:00";
        $end_date   =  trim($year)."-".substr("0".trim($month+1),-2)."-0 00:00:00";
        

        return Course::whereIn('school_id', $list)
        ->select(['plannings.id as id', 'begin', 'end', 'location', 'courses.name as course_name','courses.short_name as short_name', 'groups.name as group_name', 'groups.short_name as group_short_name'])
        ->leftJoin('groups', 'courses.id', '=', 'groups.course_id')
        ->rightJoin('plannings', 'plannings.group_id', '=', 'groups.id')
        ->where(['year' => $year])
        ->where('begin', '>', $start_date)
        ->where('end', '<', $end_date)
        ->orderBy('begin', 'asc')
        ->orderBy('course_name', 'asc')
        ->orderBy('group_name', 'asc')
        ->get();

    }
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
