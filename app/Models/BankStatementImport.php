<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatementImport extends Model
{
    protected $fillable = [
        'company_id',
        'bank_account_id',
        'user_id',
        'file_name',
        'account_number',
        'account_label',
        'period_start',
        'period_end',
        'statement_balance',
        'lines_count',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'statement_balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class)->orderBy('row_index');
    }

    public function reconciledCount(): int
    {
        return $this->lines()->fullyReconciled()->count();
    }
}
