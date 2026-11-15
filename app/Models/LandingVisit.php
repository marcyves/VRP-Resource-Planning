<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingVisit extends Model
{
    use HasFactory;

    public $timestamps = false;

    /** @var list<string> */
    protected $hidden = [
        'ip_hash',
    ];

    protected $fillable = [
        'visited_at',
        'visitor_id',
        'is_returning',
        'page',
        'locale',
        'accept_language',
        'os',
        'device_type',
        'browser',
        'referer_url',
        'referer_host',
        'keywords',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'landing_path',
        'landing_query',
        'country',
        'ip_hash',
        'cta',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'is_returning' => 'boolean',
    ];

    public function visitedAtDisplay(): string
    {
        $timezone = (string) config('landing_traffic.display_timezone', 'Europe/Rome');

        return $this->visited_at?->timezone($timezone)->translatedFormat('d/m/Y H:i:s') ?? '';
    }

    public function visitorLabel(): string
    {
        return $this->is_returning
            ? __('messages.landing_traffic_returning')
            : __('messages.landing_traffic_first');
    }

    public function deviceLabel(): string
    {
        $device = $this->deviceTypeLabel();
        $parts = array_filter([
            $device,
            $this->os && $this->os !== 'unknown' ? $this->os : null,
            $this->browser && $this->browser !== 'unknown' ? $this->browser : null,
        ]);

        return implode(' · ', $parts);
    }

    public function deviceTypeLabel(): string
    {
        return match ($this->device_type) {
            'mobile' => __('messages.landing_traffic_device_mobile'),
            'tablet' => __('messages.landing_traffic_device_tablet'),
            'desktop' => __('messages.landing_traffic_device_desktop'),
            default => __('messages.landing_traffic_unknown'),
        };
    }

    public function pageLabel(): string
    {
        return match ($this->page) {
            'login' => __('messages.landing_traffic_page_login'),
            'register' => __('messages.landing_traffic_page_register'),
            default => __('messages.landing_traffic_page_landing'),
        };
    }

    public function ctaLabel(): string
    {
        return match ($this->cta) {
            'login' => __('messages.landing_traffic_cta_login'),
            'register' => __('messages.landing_traffic_cta_register'),
            'pricing' => __('messages.landing_traffic_cta_pricing'),
            default => '—',
        };
    }

    public function countryLabel(): string
    {
        if ($this->country === null || $this->country === '') {
            return __('messages.landing_traffic_unknown');
        }

        return $this->country;
    }

    public function utmSummary(): string
    {
        $parts = array_filter([
            $this->utm_source,
            $this->utm_medium,
            $this->utm_campaign,
        ]);

        return $parts === [] ? '—' : implode(' / ', $parts);
    }
}
