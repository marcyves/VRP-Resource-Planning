<?php

namespace App\Models;

use App\Enums\ElectronicInvoiceStatus;
use App\Http\Utility\Tools;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Invoice extends Model
{
    use HasFactory;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'description',
        'bill_date',
        'amount',
        'paid_at',
        'company_id',
        'school_id',
        'electronic_invoice_status',
        'pdp_reference',
        'electronic_status_at',
        'rejection_reason',
    ];

    protected $casts = [
        'electronic_invoice_status' => ElectronicInvoiceStatus::class,
        'electronic_status_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bankReconciliation(): MorphOne
    {
        return $this->morphOne(BankReconciliation::class, 'reconcilable');
    }

    // accéder aux sessions de cours liées
    public function plannings()
    {
        return $this->hasMany(Planning::class);
    }

    public function electronicStatusLabel(): string
    {
        return $this->electronic_invoice_status?->label()
            ?? ElectronicInvoiceStatus::Draft->label();
    }

    /** @var array<string, float|null> */
    protected static array $planningTotalHtCache = [];

    /** Montant TTC (affichage, rapprochement bancaire, PDF). */
    public function amountTtc(): float
    {
        $amount = (float) $this->amount;

        if ($this->amountStoredAsHt()) {
            return round($amount * 1.2, 2);
        }

        return $amount;
    }

    public function amountHt(): float
    {
        $amount = (float) $this->amount;

        if ($this->amountStoredAsHt()) {
            return $amount;
        }

        return round($amount / 1.2, 2);
    }

    /**
     * Certaines factures ont été enregistrées en HT (fallback planning, édition « gain »).
     */
    public function amountStoredAsHt(): bool
    {
        $amount = round((float) $this->amount, 2);
        if ($amount <= 0) {
            return false;
        }

        $planningHt = $this->resolvePlanningTotalHt();
        if ($planningHt !== null && abs($amount - $planningHt) < 0.02) {
            return true;
        }

        return false;
    }

    protected function resolvePlanningTotalHt(): ?float
    {
        if (! $this->school_id || ! $this->bill_date) {
            return null;
        }

        $key = (string) $this->id;
        if (array_key_exists($key, self::$planningTotalHtCache)) {
            return self::$planningTotalHtCache[$key];
        }

        $this->loadMissing('school.company');
        if (! $this->school) {
            self::$planningTotalHtCache[$key] = null;

            return null;
        }

        $date = Carbon::parse($this->bill_date);
        $invoiceName = ($this->school->company->bill_prefix ?? '').$this->id;

        [, $ht] = Tools::getInvoiceDetails(
            $this->school_id,
            $date->month,
            $date->year,
            $invoiceName,
            false
        );

        $ht = round((float) $ht, 2);
        self::$planningTotalHtCache[$key] = $ht > 0 ? $ht : null;

        return self::$planningTotalHtCache[$key];
    }

    public static function getAmount($year)
    {
        return Invoice::where('created_at', '>', "$year-01-01")->where('created_at', '<', "$year-12-31")->sum('amount');
    }

    public static function getPayedAmount($year)
    {
        return Invoice::where('created_at', '>', "$year-01-01")->where('created_at', '<', "$year-12-31")
            ->where('paid_at', '>', "$year-01-01")->where('paid_at', '<', "$year-12-31")
            ->sum('amount');
    }

    public static function getCount($year)
    {
        return Invoice::where('created_at', '>', "$year-01-01")->where('created_at', '<', "$year-12-31")->count();
    }
}
