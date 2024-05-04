<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'password',
        'status_id',
        'company_id',
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

    public function getSchools()
    {
        $company_id = $this->company_id;

        return School::select(['schools.*'])->where('schools.company_id', '=', $company_id)->get();
    }

    public function getCourses($current_year, $current_semester)
    {
        $company_id = $this->company_id;

        $schools = School::select(['schools.*'])->where('schools.company_id', '=', $company_id)->get();
        return $schools->getCourses($current_year, $current_semester);;
    }

    public function getCompany()
    {
        return Company::findOrFail($this->company_id);
    }

    public function getCompanyBillPrefix()
    {
        return Company::findOrFail($this->company_id)->bill_prefix;
    }

    public function getBills()
    {
        return Bill::all();
    }

    public function getStatusName()
    {
        return Status::findOrFail($this->status_id)->name;
    }

    public function getMode()
    {
        if($this->isAdmin() or $this->isEditor()){
            if($this->mode == ""){
                $this->mode = "Edit";
            }
        }else{
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
