<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';
    protected $fillable =   [
        'id',
        'description',
        'paid_at',
        'company_id'
        ];

    public function getPlanning()
    {
        return Bill::select([
            'plannings.id as planning_id',
            'begin',
            'end',
            'location',
            'groups.name as group_name',
            'groups.short_name as group_short_name',
            'schools.id as school_id',
            'schools.name as school_name',
            'courses.name as course_name',
            'courses.id as course_id',
            'courses.short_name as short_name',
            'rate',
            'session_length',
            ])
        ->join('plannings', 'bills.id', '=', 'plannings.bill_id' )
        ->join('groups', 'group_id', '=', 'groups.id')
        ->join('courses', 'course_id', '=', 'courses.id')
        ->join('schools', 'schools.id', '=', 'school_id')
        ->where('bills.id', '=' , $this->id)
        ->orderBy('school_name', 'asc')
        ->orderBy('course_name', 'asc')
        ->orderBy('group_name', 'asc')
        ->get();

        // SELECT * FROM bills B LEFT JOIN plannings P on B.id = P.bill_id WHERE B.id = '$bill->id';
        return Bill::select('plannings.*')
            ->join('plannings', 'bills.id', '=', 'plannings.bill_id' )
            ->where('bills.id', '=' , $this->id)
            ->get();
    }
}
