<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'sessions', 'session_length', 'school_id', 'year', 'semester', 'rate'];

    public function groups(): HasMany
    {
        return $this->HasMany(Group::class);
    }

}
