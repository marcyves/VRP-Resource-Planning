<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Facades\Auth;

class Group extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'short_name', 'size', 'company_id'];
    
    public function plannings(): HasMany
    {
        return $this->HasMany(Planning::class);
    }

    public function getCourses()
    {
//        $company = Auth::user()->getCompany();

        return Course::select(['courses.*'])
        ->join('group_course', 'course_id', '=', 'courses.id')
        ->where('group_course.group_id', '=', $this->id)
        ->orderBy('courses.name')
        ->get();
    }

     /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function newCollection(array $models = []): Collection
    {
        return new PlanningCollection($models);
    }

}
