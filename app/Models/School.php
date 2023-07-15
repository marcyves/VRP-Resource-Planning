<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
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

    public function getCourses(String $year)
    {
        return Course::where(['school_id' => $this->id,
                'year' => $year])->sortBy('semester')->get();
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
