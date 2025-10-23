<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class School extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['name', 'code', 'company_id', 'address', 'city', 'zip', 'country', 'phone', 'email', 'website', 'logo', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->BelongsTo(Company::class);
    }

    public function courses(): HasMany
    {
        return $this->HasMany(Course::class);
    }

    public function groups(): HasManyThrough
    {
        return $this->HasManyThrough(Group::class, Course::class);
    }

    public function countCourses()
    {
        return Course::where('school_id', $this->id)
            ->count();
    }

    public function getCourses(String $year = 'all')
    {
        if($year == 'all'){
            return Course::where('school_id', $this->id)
            ->select(['courses.*', 'schools.name as school_name', 'programs.name as program_name'])
            ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
            ->leftJoin('schools', 'courses.school_id', '=', 'schools.id')
            ->withCount('groups')
            ->orderBy('year', 'asc')
            ->orderBy('semester', 'asc')
            ->orderBy('school_name', 'asc')
            ->orderBy('program_name', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        }else{
        return Course::where('school_id', $this->id)
            ->select(['courses.*', 'schools.name as school_name', 'programs.name as program_name'])
            ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
            ->leftJoin('schools', 'courses.school_id', '=', 'schools.id')
            ->withCount('groups')
            ->where('year', '=', $year)
            ->orderBy('semester', 'asc')
            ->orderBy('school_name', 'asc')
            ->orderBy('program_name', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        }
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

    public function getDocuments(String $year = 'all')
    {
        return Document::select()->where('school_id', '=', $this->id)->get();

    }

    public function getInvoices(String $year = 'all')
    {
        return Invoice::select()->where('school_id', '=', $this->id)->get();
    }
}
