<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;

class PlanningCollection extends Collection
{
    use HasFactory;

    public function getGroupOccurences($year = 'all')
    {
        $list = $this->map(function(Group $group){
            return $group->id;
        });

        $query = Planning::whereIn('group_id', $list)
        ->select(['plannings.id as planning_id', 'group_id', 'courses.name as course_name', 'begin', 'end'])
        ->join('courses', 'plannings.course_id', '=', 'courses.id')
        ->orderBy('begin', 'asc');

        if ($year !== 'all') {
            $query->whereYear('begin', $year);
        }

        return $query->get();
    }

    public function countGroupOccurences()
    {
        $list = $this->map(function(Group $group){
            return $group->id;
        });
       
        return Planning::whereIn('group_id', $list)
        ->select(['group_id', count('id')])
        ->groupBy('group_id')
        ->get();

    }
}
