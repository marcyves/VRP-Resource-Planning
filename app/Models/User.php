<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Utility\Tools;

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
        'password',
        'status_id',
        'company_id',
        'mode',
        'photo'
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

        if ($year == 'all') {
            return School::select(['schools.*'])
                ->where('schools.company_id', '=', $company_id)
                ->orderBy('schools.name')
                ->get();
        } else {
            return School::select([
                'schools.*',
                DB::raw('SUM(amount) as amount')
            ])
                ->where('schools.company_id', '=', $company_id)
                ->join('invoices', 'invoices.school_id', '=', 'schools.id')
                ->where('bill_date', '>', $year . '-01-01')
                ->groupBy('schools.id')
                ->orderBy('schools.name')
                ->get();
        }
    }


    public function getCourses($current_year = "all", $current_semester = "all")
    {
        if (!isset($current_year)) {
            $current_year = now()->format('Y');
        }

        if (!isset($current_semester)) {
            $current_semester = "all";
        }

        $company_id = $this->company_id;

        $schools = School::select(['schools.*'])
            ->where('schools.company_id', '=', $company_id)
            ->get();

        return $schools->getCourses($current_year, $current_semester);;
    }

    public function getInvoices()
    {
        return Invoice::select(['invoices.*', 'schools.name as school'])->where('invoices.company_id', $this->company_id)
            ->join('schools', 'schools.id', '=', 'invoices.school_id')
            ->orderBy('invoices.id')->get();
    }

    public function getPlannedAmountPerMonth($year)
    {
        $amount = [];
        $schools = $this->getSchools();

        for ($i = 1; $i <= 12; $i++) {
            $planning = $schools->getBillingPlanning($year, $i);
            if (!$planning) {
                $monthly_gain = 0;
            }else{
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
    public function getGroups(Bool $active = true)
    {
        return Group::where('company_id', $this->company_id)
            ->where('active', $active)
            ->orderBy('name')->get();
    }

    public function getStatusName()
    {
        return Status::findOrFail($this->status_id)->name;
    }

    public function getMode()
    {
        if ($this->isAdmin() or $this->isEditor()) {
            if ($this->mode == "") {
                $this->mode = "Edit";
            }
        } else {
            $this->mode = "Browse";
        }
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function isSuperAdmin()
    {
        if ($this->status_id == 4)
            return true;
        return false;
    }

    public function isAdmin()
    {
        if ($this->status_id == 1 or $this->status_id == 4)
            return true;
        return false;
    }

    public function isEditor()
    {
        if ($this->status_id == 2)
            return true;
        return false;
    }

    public function isAuthor(String $id)
    {
        if ($this->status_id == 2 && $id == $this->id)
            return true;
        return false;
    }

    public function isReader()
    {
        if ($this->status_id == 3)
            return true;
        return false;
    }
}
