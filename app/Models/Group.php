<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'short_name', 'size', 'company_id', 'active', 'year'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function plannings(): HasMany
    {
        return $this->HasMany(Planning::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'group_course');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function getCourses()
    {
        return $this->courses()->orderBy('courses.name')->get();
    }

    /**
     * @param  iterable<int>  $groupIds
     */
    public static function planningOccurrencesForIds(iterable $groupIds, int|string $year = 'all'): \Illuminate\Support\Collection
    {
        $ids = collect($groupIds)->filter()->unique()->values()->all();

        if ($ids === []) {
            return collect();
        }

        $query = Planning::whereIn('group_id', $ids)
            ->select(['plannings.id as planning_id', 'group_id', 'courses.name as course_name', 'begin', 'end'])
            ->join('courses', 'plannings.course_id', '=', 'courses.id')
            ->orderBy('begin', 'asc');

        if ($year !== 'all') {
            $query->whereYear('begin', $year);
        }

        return $query->get();
    }

     /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function newCollection(array $models = []): Collection
    {
        return new PlanningCollection($models);
    }

}
