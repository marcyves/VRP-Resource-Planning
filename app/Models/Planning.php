<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['begin', 'end', 'location', 'group_id', 'bill_id'];

    public function getSessionLength()
    {
        $group = Group::findOrFail($this->group_id);
        $course = Course::findOrFail($group->course_id);
        
        return $course->session_length;
    }

    public static function getPlanningByCourseAndDate($course_id, $start_date, $end_date)
    {
        return Planning::select([ 'plannings.id' ])
        ->rightJoin('groups', 'plannings.group_id', '=', 'groups.id')
        ->where(['groups.course_id' => $course_id])
        ->where('begin', '>', $start_date)
        ->where('end', '<', $end_date)
        ->get();
    }

    public static function getPlanningBySchoolAndDate($school_id, $start_date, $end_date)
    {
        return Planning::select([ 'plannings.id' ])
        ->rightJoin('groups', 'plannings.group_id', '=', 'groups.id')
        ->rightJoin('courses', 'groups.course_id', '=', 'courses.id')
        ->where(['courses.school_id' => $school_id])
        ->where('begin', '>', $start_date)
        ->where('end', '<', $end_date)
        ->get();
    }
}
