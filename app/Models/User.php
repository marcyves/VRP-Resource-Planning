<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function isAdmin()
    {
        if ($this->status_id == 1)
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
