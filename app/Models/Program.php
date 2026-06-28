<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Program extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = ['name', 'short_description', 'company_id'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function courses(): HasMany
    {
        return $this->HasMany(Course::class);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public static function forCurrentCompany(): Builder
    {
        return static::forCompany((int) Auth::user()->company_id);
    }

    public static function labelFrom(?string $shortDescription, ?string $name): string
    {
        $short = trim((string) $shortDescription);

        return $short !== '' ? $short : (string) $name;
    }

    public function listLabel(): string
    {
        return self::labelFrom($this->short_description, $this->name);
    }
}
