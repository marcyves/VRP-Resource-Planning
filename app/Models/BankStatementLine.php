<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatementLine extends Model
{
    protected $fillable = [
        'bank_statement_import_id',
        'bank_account_id',
        'company_id',
        'operation_date',
        'label',
        'debit',
        'credit',
        'amount',
        'line_hash',
        'row_index',
    ];

    protected $casts = [
        'operation_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(BankStatementImport::class, 'bank_statement_import_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    public function matchedAmount(): float
    {
        if ($this->relationLoaded('reconciliations')) {
            return (float) $this->reconciliations->sum('matched_amount');
        }

        return (float) $this->reconciliations()->sum('matched_amount');
    }

    public function isReconciled(): bool
    {
        $matched = $this->matchedAmount();

        if ($matched <= 0) {
            return false;
        }

        return abs($matched - $this->absoluteAmount()) <= 0.02;
    }

    public function isCredit(): bool
    {
        return (float) $this->amount > 0;
    }

    public function isDebit(): bool
    {
        return (float) $this->amount < 0;
    }

    public function absoluteAmount(): float
    {
        return abs((float) $this->amount);
    }

    public function scopeFullyReconciled($query)
    {
        return $query->whereRaw(
            'ABS((
                SELECT COALESCE(SUM(matched_amount), 0)
                FROM bank_reconciliations
                WHERE bank_reconciliations.bank_statement_line_id = bank_statement_lines.id
            ) - ABS(bank_statement_lines.amount)) <= 0.02'
        );
    }
}
