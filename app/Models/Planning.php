<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Planning extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['begin', 'end', 'location', 'group_id', 'course_id', 'bill_id'];

    public function getSessionLength()
    {
        $group = Group::findOrFail($this->group_id);
        $course = Course::findOrFail($this->course_id);
        
        return $course->session_length;
    }

    public static function getDetails(String $year, String $month)
    {
        $start_date =  trim($year)."-".substr("0".trim($month),-2)."-0 00:00:00";
        $month++;
        $end_year = $year;
        if($month == "13"){
            $month = "01";
            $end_year++;
        }
        $end_date   =  trim($end_year)."-".substr("0".trim($month),-2)."-0 00:00:00";

        $schools = Auth::user()->getSchools();
        $list = $schools->map(function(School $school){
            return $school->id;
        });

        return Planning::select([
            'plannings.id as id',
            'schools.name as school_name',
            'begin',
            'end',
            'location',
            'courses.name as course_name',
            'courses.short_name as short_name',
            'rate',
            'session_length',
            'bill_id',
            'groups.name as group_name',
            'groups.short_name as group_short_name'
            ])
        ->join('groups', 'groups.id', '=', 'plannings.group_id')
        ->join('courses', 'courses.id', '=', 'plannings.course_id')
        ->join('schools', 'schools.id', '=', 'school_id')
        ->whereIn('school_id', $list)
        ->where(['courses.year' => $year])
        ->where('begin', '>', $start_date)
        ->where('end', '<', $end_date)
        ->orderBy('begin', 'asc')
        ->get();

    }
    public static function getPlanningByCourseAndDate($course_id, $start_date, $end_date)
    {
        return Planning::select([ 'plannings.id' ])
        ->rightJoin('groups', 'plannings.group_id', '=', 'groups.id')
        ->where(['plannings.course_id' => $course_id])
        ->where('begin', '>', $start_date)
        ->where('end', '<', $end_date)
        ->orderBy('begin', 'asc')
        ->get();
    }

    public static function getPlanningBySchoolAndDate($school_id, $start_date, $end_date)
    {
        return Planning::select([ 
            'plannings.id',
            'courses.name as course_name',
            'groups.name as group_name',
            'courses.rate as rate',
            'courses.session_length as session_length'
        ])
        ->rightJoin('groups', 'plannings.group_id', '=', 'groups.id')
        ->rightJoin('courses', 'plannings.course_id', '=', 'courses.id')
        ->where(['courses.school_id' => $school_id])
        ->where('begin', '>', $start_date)
        ->where('end', '<', $end_date)
        ->orderBy('courses.name', 'asc')
        ->orderBy('begin', 'asc')
        ->get();
    }
}
