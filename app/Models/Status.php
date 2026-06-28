<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Status extends Model
{
    use HasFactory;

    public const ADMIN = 1;

    public const EDITOR = 2;

    public const READER = 3;

    public const SUPER_ADMIN = 4;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public static function superAdminId(): int
    {
        return (int) static::query()
            ->where('name', 'super admin')
            ->value('id') ?: self::SUPER_ADMIN;
    }
}
