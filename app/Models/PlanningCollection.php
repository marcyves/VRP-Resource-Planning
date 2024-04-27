<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;

class PlanningCollection extends Collection
{
    use HasFactory;

    public function getGroupOccurences()
    {
        $list = $this->map(function(Group $group){
            return $group->id;
        });
       
        return Planning::whereIn('group_id', $list)
        ->select(['group_id', 'begin', 'end'])
        ->orderBy('begin', 'asc')
        ->get();

    }
}
