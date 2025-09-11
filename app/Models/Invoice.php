<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    protected $fillable =   [
        'id',
        'description',
        'amount',
        'paid_at',
        'company_id',
        'school_id'
        ];

    public static function getAmount($year){
        return Invoice::where('created_at', '>', "$year-01-01")->where('created_at', '<', "$year-12-31")->sum('amount');
    }

    public static function getPayedAmount($year){
        return Invoice::where('created_at', '>', "$year-01-01")->where('created_at', '<', "$year-12-31")
            ->where('paid_at', '>', "$year-01-01")->where('paid_at', '<', "$year-12-31")
            ->sum('amount');
    }

    public static function getCount($year){
        return Invoice::where('created_at', '>', "$year-01-01")->where('created_at', '<', "$year-12-31")->count();
    }
}
