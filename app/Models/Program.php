<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name'];

    public function courses(): HasMany
    {
        return $this->HasMany(Course::class);
    }
}
