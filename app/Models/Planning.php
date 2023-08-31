<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['begin', 'end', 'location', 'group_id'];

    public function getSessionLength()
    {
        $group = Group::findOrFail($this->group_id);
        $course = Course::findOrFail($group->course_id);
        
        return $course->session_length;
    }
}
