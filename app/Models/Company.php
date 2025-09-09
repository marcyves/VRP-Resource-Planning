<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = [
        'name',
        'bill_prefix',
        'address',
        'city',
        'zip',
        'country',
        'phone',
        'email',
        'website',
        'logo',
        'description',
        'iban_name', 'bank', 'branch', 'account', 'bic',
    ];

    public function schools(): HasMany
    {
        return $this->HasMany(School::class);
    }

    public function users(): HasMany
    {
        return $this->HasMany(User::class);
    }
}
