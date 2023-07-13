<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'user_id'];

    public function courses(): HasMany
    {
        return $this->HasMany(Course::class);
    }

}
