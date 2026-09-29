<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'superpdp_webhook_secret',
    ];

    protected $hidden = [
        'superpdp_webhook_secret',
    ];

    protected $casts = [
        'superpdp_webhook_secret' => 'encrypted',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? new static;
    }
}
