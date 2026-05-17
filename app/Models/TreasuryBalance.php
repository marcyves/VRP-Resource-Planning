<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreasuryBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'year',
        'opening_date',
        'opening_amount',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'opening_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
