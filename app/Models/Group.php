<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'course_id', 'size'];
    
    public function plannings(): HasMany
    {
        return $this->HasMany(Planning::class);
    }
}
