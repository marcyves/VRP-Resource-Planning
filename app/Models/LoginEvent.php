<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const GEO_LOCAL = 'local';

    protected $fillable = [
        'company_id',
        'user_id',
        'username',
        'ip',
        'geo_label',
        'success',
        'locked_out',
        'occurred_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'locked_out' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        if ($viewer->isSuperAdmin()) {
            return $query;
        }

        return $query->where('company_id', $viewer->company_id);
    }

    public function geoDisplay(): string
    {
        if ($this->geo_label === self::GEO_LOCAL) {
            return __('messages.login_stats_geo_local');
        }

        if ($this->geo_label === null || $this->geo_label === '') {
            return __('messages.login_stats_geo_unknown');
        }

        return $this->geo_label;
    }

    public function outcomeLabel(): string
    {
        if ($this->success) {
            return __('messages.login_stats_outcome_success');
        }

        if ($this->locked_out) {
            return __('messages.login_stats_outcome_lockout');
        }

        return __('messages.login_stats_outcome_failed');
    }
}
