<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ExpenseReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'year',
        'month',
        'status',
        'submitted_at',
        'reimbursed_at',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function bankReconciliation(): MorphOne
    {
        return $this->morphOne(BankReconciliation::class, 'reconcilable');
    }
}
