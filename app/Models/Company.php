<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const PROFILE_EDUCATION = 'education';

    public const PROFILE_CONSULTING = 'consulting';

    public $fillable = [
        'name',
        'terminology_profile',
        'bill_prefix',
        'siren',
        'siret',
        'vat_number',
        'legal_form',
        'share_capital',
        'contact_user_id',
        'address',
        'city',
        'zip',
        'country',
        'phone',
        'email',
        'website',
        'logo',
        'description',
        'bank_name',
        'iban_name',
        'bank',
        'branch',
        'account',
        'key',
        'bic',
        'iban',
        'billing_bank_account_id',
    ];

    public function billingBankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'billing_bank_account_id');
    }

    /**
     * Coordonnées bancaires pour facturation (compte lié ou anciennes colonnes société).
     */
    public function billingDetails(): object
    {
        $account = $this->relationLoaded('billingBankAccount')
            ? $this->billingBankAccount
            : $this->billingBankAccount()->with('bank')->first();

        return (object) [
            'bank_name' => $account?->bank?->name ?? $this->bank_name,
            'iban_holder' => $account?->iban_holder ?? $this->iban_name,
            'rib_bank_code' => $account?->rib_bank_code ?? $this->bank,
            'rib_branch_code' => $account?->rib_branch_code ?? $this->branch,
            'rib_account_number' => $account?->rib_account_number ?? $this->account,
            'rib_key' => $account?->rib_key ?? $this->key,
            'iban' => $account?->iban ?? $this->iban,
            'bic' => $account?->bic ?? $this->bic,
            'account' => $account,
        ];
    }

    public function legalFooterLine(): ?string
    {
        $parts = array_filter([
            $this->legal_form,
            $this->share_capital ? __('messages.share_capital_label', ['amount' => $this->share_capital]) : null,
            $this->siren ? __('messages.siren_label', ['siren' => $this->siren]) : null,
        ]);

        return $parts !== [] ? implode(' - ', $parts) : null;
    }

    public function schools(): HasMany
    {
        return $this->HasMany(School::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function contactUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contact_user_id');
    }

    public function syncContactFromUser(?User $user): void
    {
        if ($user === null) {
            $this->contact_user_id = null;

            return;
        }

        $this->contact_user_id = $user->id;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->website = $user->website;
    }
}
