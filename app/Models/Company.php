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

    public $fillable = [
        'name',
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
    ];

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
