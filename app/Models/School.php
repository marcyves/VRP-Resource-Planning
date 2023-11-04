<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class School extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'user_id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function courses(): HasMany
    {
        return $this->HasMany(Course::class);
    }

    public function groups(): HasManyThrough
    {
        return $this->HasManyThrough(Group::class, Course::class);
    }

     /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function newCollection(array $models = []): Collection
    {
        return new CourseCollection($models);
    }

}
