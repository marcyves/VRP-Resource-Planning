<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_id',
        'company_id',
        'account_number',
        'label',
        'iban_holder',
        'rib_bank_code',
        'rib_branch_code',
        'rib_account_number',
        'rib_key',
        'iban',
        'bic',
        'opening_date',
        'opening_amount',
        'active',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'opening_amount' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function imports(): HasMany
    {
        return $this->hasMany(BankStatementImport::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class);
    }

    public function companiesForBilling(): HasMany
    {
        return $this->hasMany(Company::class, 'billing_bank_account_id');
    }

    public function hasBillingDetails(): bool
    {
        return collect([
            $this->iban,
            $this->bic,
            $this->iban_holder,
            $this->rib_bank_code,
            $this->rib_branch_code,
            $this->rib_account_number,
            $this->rib_key,
        ])->contains(fn ($value) => filled($value));
    }

    public function displayName(): string
    {
        $parts = array_filter([
            $this->bank?->name,
            $this->label,
            $this->account_number,
        ]);

        return implode(' · ', array_unique($parts)) ?: $this->account_number;
    }
}
