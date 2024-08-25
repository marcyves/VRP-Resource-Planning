<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCourse extends Model
{
    use HasFactory;

    public $fillable = ['course_id', 'group_id'];
    protected $table = 'group_course';


}
