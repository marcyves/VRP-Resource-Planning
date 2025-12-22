<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalendarMapping extends Model
{
protected $fillable = ['school_id', 'ics_label', 'mappable_id', 'mappable_type'];

    public function mappable(): MorphTo
    {
        return $this->morphTo();
    }
}
