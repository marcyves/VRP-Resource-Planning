<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;

class PlanningCollection extends Collection
{
    use HasFactory;

    public function getGroupOccurences($year = 'all')
    {
        return Group::planningOccurrencesForIds(
            $this->map(fn (Group $group) => $group->id),
            $year
        );
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
