<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use App\Http\Utility\Tools;

class School extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'name',
        'code',
        'siren',
        'siret',
        'vat_number',
        'company_id',
        'address',
        'address2',
        'city',
        'zip',
        'country',
        'phone',
        'email',
        'website',
        'logo',
        'description',
    ];

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

    public function getCourses(string $year = 'all')
    {
        if ($year == 'all') {
            return Course::where('school_id', $this->id)
                ->select(Course::PROGRAM_SCHOOL_SELECT)
                ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
                ->leftJoin('schools', 'courses.school_id', '=', 'schools.id')
                ->withCount('groups')
                ->orderBy('year', 'asc')
                ->orderBy('semester', 'asc')
                ->orderBy('school_name', 'asc')
                ->orderBy('program_name', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        } else {
            return Course::where('school_id', $this->id)
                ->select(Course::PROGRAM_SCHOOL_SELECT)
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

    public function getDocuments(string $year = 'all')
    {
        return Document::select()->where('school_id', '=', $this->id)->get();

    }

    public function getInvoices(string $year = 'all')
    {
        return Invoice::select(['invoices.*', 'schools.name as school'])
            ->join('schools', 'schools.id', '=', 'invoices.school_id')
            ->where([
                ['school_id', '=', $this->id],
                ['bill_date', '>=', $year.'-01-01'],
                ['bill_date', '<=', $year.'-12-31'],
            ])->get();
    }

    public function getBillingPlanning(string $year, string $month)
    {
        return $this->newCollection([$this])->getBillingPlanning($year, $month);
    }

    public function hasUnbilledSessionsInMonth(int $year, int $month): bool
    {
        [$startDate, $endDate] = Tools::billingPeriodBounds($year, $month);

        return DB::table('plannings')
            ->join('courses', 'courses.id', '=', 'plannings.course_id')
            ->where('courses.school_id', $this->id)
            ->where(function ($query) {
                $query->whereNull('plannings.invoice_id')
                    ->orWhere('plannings.invoice_id', '');
            })
            ->where('begin', '>', $startDate)
            ->where('end', '<', $endDate)
            ->exists();
    }

    public function findPreviousUnbilledPeriod(int $year, int $month): ?array
    {
        $year = (int) $year;
        $month = (int) $month;

        $month--;
        if ($month < 1) {
            $month = 12;
            $year--;
        }

        for ($i = 0; $i < 120; $i++) {
            if ($this->hasUnbilledSessionsInMonth($year, $month)) {
                return ['year' => $year, 'month' => $month];
            }

            $month--;
            if ($month < 1) {
                $month = 12;
                $year--;
            }

            if ($year < 1970) {
                break;
            }
        }

        return null;
    }

    public function hasPreviousUnbilledPeriod(int $year, int $month): bool
    {
        return $this->findPreviousUnbilledPeriod($year, $month) !== null;
    }
}
