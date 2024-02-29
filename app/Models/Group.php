<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'short_name','course_id', 'size'];

    protected $appends = ['hours_planned'];
    
    public function plannings(): HasMany
    {
        return $this->HasMany(Planning::class);
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

    public function getHoursPlanned()
    {
        $course = Course::find($this->course_id);
        $hours_scheduled = $course->sessions*$course->session_length;

        $planning = Planning::select()
                    ->where('plannings.group_id', '=', $this->id)
                    ->get();

        $hours_planned = 0;
        foreach($planning as $event)
        {
            $end      = strtotime($event->end);
            $begin    = strtotime($event->begin);
            $hours_planned += intval(($end - $begin)/60)/60;
        }

        return ['forecast' => $hours_scheduled, 'planned' => $hours_planned];
    }

    protected function hours_planned(): Attribute
    {
        return Attribute::make(
            get: $this->getHoursPlanned(),
        );
    }

    function getHoursPlannedAttribute() {
        return $this->getHoursPlanned();  
    }

    public function getTimeLeft()
    {
        $group_hours = $this->getHoursPlanned();

        return $group_hours['forecast'] - $group_hours['planned'];
    }
}
