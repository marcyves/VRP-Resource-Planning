<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BankReconciliation extends Model
{
    protected $fillable = [
        'bank_statement_line_id',
        'company_id',
        'reconcilable_type',
        'reconcilable_id',
        'matched_amount',
    ];

    protected $casts = [
        'matched_amount' => 'decimal:2',
    ];

    public function line(): BelongsTo
    {
        return $this->belongsTo(BankStatementLine::class, 'bank_statement_line_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function reconcilable(): MorphTo
    {
        return $this->morphTo();
    }
}
