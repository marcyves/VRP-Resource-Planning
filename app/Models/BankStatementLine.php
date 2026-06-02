<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class BankStatementLine extends Model
{
    protected $fillable = [
        'bank_statement_import_id',
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

    public function reconciliation(): HasOne
    {
        return $this->hasOne(BankReconciliation::class);
    }

    public function isReconciled(): bool
    {
        if ($this->relationLoaded('reconciliation')) {
            return $this->reconciliation !== null;
        }

        return $this->reconciliation()->exists();
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
}
