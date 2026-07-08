<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Utility\Tools;
use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'website',
        'password',
        'status_id',
        'company_id',
        'mode',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class);
    }

    public function company(): BelongsTo
    {
        return $this->BelongsTo(Company::class);
    }

    public function status(): BelongsTo
    {
        return $this->BelongsTo(Status::class);
    }

    public function getSchools($year = 'all')
    {
        $company_id = $this->company_id;

        if ($year == 'all') {
            return School::select(['schools.*'])->where('schools.company_id', '=', $company_id)
                ->orderBy('schools.name')
                ->get();
        } else {
            return School::select(['schools.*'])->where('schools.company_id', '=', $company_id)
                ->join('courses', 'courses.school_id', '=', 'schools.id')
                ->where('courses.year', '=', $year)
                ->distinct()
                ->orderBy('schools.name')
                ->get();
        }
    }

    public function getSchoolsAndBudget($year = 'all')
    {
        $company_id = $this->company_id;

        $query = School::query()
            ->select(['schools.*'])
            ->where('schools.company_id', '=', $company_id)
            ->whereHas('courses', function ($courseQuery) use ($year) {
                if ($year != 'all') {
                    $courseQuery->where('year', $year);
                }
            })
            ->orderBy('schools.name');

        if ($year != 'all') {
            $query
                ->addSelect(DB::raw('COALESCE(SUM(invoices.amount), 0) as amount'))
                ->leftJoin('invoices', function ($join) use ($year) {
                    $join->on('invoices.school_id', '=', 'schools.id')
                        ->whereYear('invoices.bill_date', $year);
                })
                ->groupBy('schools.id');
        }

        $schools = $query->get();
        $unbilledStats = $this->getUnbilledStatsBySchool($year);

        return $schools->map(function (School $school) use ($unbilledStats) {
            $stats = $unbilledStats->get($school->id);
            $school->unbilled_amount = $stats->unbilled_amount ?? 0;
            $school->unbilled_hours = $stats->unbilled_hours ?? 0;

            return $school;
        });
    }

    private function getUnbilledStatsBySchool($year)
    {
        $query = DB::table('plannings')
            ->join('courses', 'courses.id', '=', 'plannings.course_id')
            ->join('schools', 'schools.id', '=', 'courses.school_id')
            ->where('schools.company_id', '=', $this->company_id)
            ->where(function ($q) {
                $q->whereNull('plannings.invoice_id')
                    ->orWhere('plannings.invoice_id', '');
            })
            ->select(
                'schools.id',
                'plannings.begin',
                'plannings.end',
                'courses.rate',
                'plannings.billable_rate'
            );

        if ($year != 'all') {
            $query->whereYear('plannings.begin', $year);
        }

        $stats = [];

        foreach ($query->get() as $row) {
            if (! isset($stats[$row->id])) {
                $stats[$row->id] = (object) [
                    'id' => $row->id,
                    'unbilled_amount' => 0,
                    'unbilled_hours' => 0,
                ];
            }

            $stats[$row->id]->unbilled_hours += Tools::sessionDurationHours($row->begin, $row->end);
            $stats[$row->id]->unbilled_amount += Tools::planningGain(
                $row->begin,
                $row->end,
                $row->rate,
                $row->billable_rate
            );
        }

        foreach ($stats as $stat) {
            $stat->unbilled_amount = round($stat->unbilled_amount * 1.2, 2);
        }

        return collect($stats)->keyBy('id');
    }

    public function getCourses($current_year = 'all', $current_semester = 'all')
    {
        if (! isset($current_year)) {
            $current_year = now()->format('Y');
        }

        if (! isset($current_semester)) {
            $current_semester = 'all';
        }

        $company_id = $this->company_id;

        $schools = School::select(['schools.*'])
            ->where('schools.company_id', '=', $company_id)
            ->get();

        return $schools->getCourses($current_year, $current_semester);
    }

    public function getInvoices($year = 'all')
    {
        $query = Invoice::select(['invoices.*', 'schools.name as school'])->where('invoices.company_id', $this->company_id)
            ->join('schools', 'schools.id', '=', 'invoices.school_id')
            ->orderBy('invoices.id');

        if ($year !== 'all') {
            $query->whereYear('invoices.bill_date', $year);
        }

        return $query->get();
    }

    public function getPlannedAmountPerMonth($year)
    {
        $amount = [];
        $schools = $this->getSchools();

        for ($i = 1; $i <= 12; $i++) {
            $planning = $schools->getBillingPlanning($year, $i);
            if (! $planning) {
                $monthly_gain = 0;
            } else {
                [$tmp_schools, $monthly_gain, $tmp_monthly_hours] = Tools::getBillingInformation($planning);
            }

            $amount[] = $monthly_gain;
        }

        return $amount;
    }

    public function getInvoicesAmountPerMonth($year)
    {
        $amount = [];
        for ($i = 1; $i <= 12; $i++) {
            $amount[] = Invoice::where('company_id', $this->company_id)
                ->where('created_at', '>', "$year-$i-01")->where('created_at', '<', "$year-$i-31")->sum('amount');
        }

        return $amount;
    }

    public function getInvoicesAmountPerYear($year)
    {
        return Invoice::where('company_id', $this->company_id)
            ->where('created_at', '>', "$year-01-01")->where('created_at', '<', "$year-12-31")->sum('amount');
    }

    public function getInvoicesPayedAmountPerYear($year)
    {
        return Invoice::where('company_id', $this->company_id)
            ->where('created_at', '>', "$year-01-01")->where('created_at', '<', "$year-12-31")
            ->where('paid_at', '>', "$year-01-01")->where('paid_at', '<', "$year-12-31")
            ->sum('amount');
    }

    public function getInvoicesCountPerYear($year)
    {
        return Invoice::where('company_id', $this->company_id)
            ->where('created_at', '>', "$year-01-01")
            ->where('created_at', '<', "$year-12-31")
            ->count();
    }

    public function getGroups(bool $active = true)
    {
        return Group::where('company_id', $this->company_id)
            ->where('active', $active)
            ->orderBy('name')->get();
    }

    public function getGroupsQuery(?bool $active = true)
    {
        $query = Group::where('company_id', $this->company_id)
            ->orderBy('name');

        if (! is_null($active)) {
            $query->where('active', $active);
        }

        return $query;
    }

    public function getStatusName()
    {
        return Status::findOrFail($this->status_id)->name;
    }

    public function getMode()
    {
        if ($this->isAdmin() or $this->isEditor()) {
            if ($this->mode == '') {
                $this->mode = 'Edit';
            }
        } else {
            $this->mode = 'Browse';
        }

        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function isSuperAdmin()
    {
        return $this->status_id == Status::superAdminId();
    }

    public function isAdmin()
    {
        if ($this->isSuperAdmin() || $this->status_id == Status::ADMIN) {
            return true;
        }

        return false;
    }

    public function isEditor()
    {
        if ($this->status_id == Status::EDITOR) {
            return true;
        }

        return false;
    }

    public function isAuthor(string $id)
    {
        if ($this->status_id == Status::EDITOR && $id == $this->id) {
            return true;
        }

        return false;
    }

    public function isReader()
    {
        if ($this->status_id == Status::READER) {
            return true;
        }

        return false;
    }

    public function homePath(): string
    {
        return $this->isSuperAdmin()
            ? route('super-admin.companies.index', absolute: false)
            : route('home', absolute: false);
    }
}
