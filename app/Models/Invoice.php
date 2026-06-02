<?php

namespace App\Models;

use App\Enums\ElectronicInvoiceStatus;
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
