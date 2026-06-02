<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'expense_report_id',
        'expense_date',
        'payment_date',
        'label',
        'vendor',
        'amount',
        'tax_amount',
        'category',
        'payment_method',
        'is_recurring',
        'recurring_frequency',
        'recurring_until',
        'notes',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class, 'expense_report_id');
    }

    public function bankReconciliation(): MorphOne
    {
        return $this->morphOne(BankReconciliation::class, 'reconcilable');
    }
}
