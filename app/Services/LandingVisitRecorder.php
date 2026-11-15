<?php

namespace App\Services;

use App\Models\LandingVisit;
use App\Support\SearchKeywordExtractor;
use App\Support\TerminologyLocale;
use App\Support\UserAgentParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LandingVisitRecorder
{
    public const PAGES = ['landing', 'login', 'register'];

    public const CTAS = ['login', 'register', 'pricing'];

    public function __construct(
        private UserAgentParser $userAgentParser,
        private SearchKeywordExtractor $keywords,
        private GeoLocator $geoLocator,
    ) {}

    public function declined(Request $request): bool
    {
        if (! config('landing_traffic.enabled', true)) {
            return true;
        }

        if ($request->user()) {
            return true;
        }

        if (! config('landing_traffic.respect_dnt', true)) {
            return false;
        }

        $dnt = strtolower((string) $request->header('DNT', ''));
        $gpc = strtolower((string) $request->header('Sec-GPC', ''));

        return in_array($dnt, ['1', 'yes'], true) || $gpc === '1';
    }

    public function ensureVisitorCookie(Request $request): ?string
    {
        if ($this->declined($request) || $this->userAgentParser->isBot($request->userAgent())) {
            return null;
        }

        $name = (string) config('landing_traffic.cookie', 'vrp_lv');
        $existing = $request->cookies->get($name);
        $returning = is_string($existing) && Str::isUuid($existing);
        $id = $returning ? $existing : (string) Str::uuid();

        $request->attributes->set('landing_visitor_id', $id);
        $request->attributes->set('landing_is_returning', $returning);

        Cookie::queue(cookie(
            $name,
            $id,
            (int) config('landing_traffic.cookie_minutes', 60 * 24 * 365),
            '/',
            null,
            $request->secure(),
            true,
            false,
            'lax'
        ));

        return $id;
    }

    public function record(Request $request, ?string $page = null): ?LandingVisit
    {
        if ($this->declined($request)) {
            return null;
        }

        if ($this->userAgentParser->isBot($request->userAgent())) {
            return null;
        }

        $visitorId = $request->attributes->get('landing_visitor_id')
            ?? $this->ensureVisitorCookie($request);

        if (! is_string($visitorId) || $visitorId === '') {
            return null;
        }

        $page = $page ?? $this->pageFromRequest($request);
        $parsed = $this->userAgentParser->parse($request->userAgent());
        $referer = $this->header($request, 'referer');
        $query = $request->query();
        $utm = $this->keywords->utmFromLandingOrReferer($query, $referer);
        $cta = in_array($page, ['login', 'register'], true) ? $page : null;

        try {
            $visit = LandingVisit::query()->create([
                'visited_at' => now(),
                'visitor_id' => $visitorId,
                'is_returning' => (bool) $request->attributes->get('landing_is_returning', false),
                'page' => $page,
                'locale' => TerminologyLocale::normalizeBaseLocale((string) app()->getLocale()),
                'accept_language' => $this->truncate($this->header($request, 'accept-language'), 255),
                'os' => $parsed['os'],
                'device_type' => $parsed['device_type'],
                'browser' => $parsed['browser'],
                'referer_url' => $this->truncate($referer, 2048),
                'referer_host' => $this->keywords->hostFromUrl($referer),
                'keywords' => $this->keywords->fromLandingOrReferer($query, $referer),
                'utm_source' => $utm['utm_source'],
                'utm_medium' => $utm['utm_medium'],
                'utm_campaign' => $utm['utm_campaign'],
                'utm_content' => $utm['utm_content'],
                'utm_term' => $utm['utm_term'],
                'landing_path' => $this->truncate($request->getPathInfo() ?: '/', 255) ?? '/',
                'landing_query' => $this->truncate($request->getQueryString(), 2048),
                'country' => $this->country($request),
                'ip_hash' => $this->hashIp($request->ip()),
                'cta' => $cta,
            ]);
        } catch (Throwable $e) {
            Log::warning('landing_traffic.record_failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($cta !== null) {
            $this->markCta($request, $cta, $visitorId);
        }

        return $visit;
    }

    public function markCta(Request $request, string $cta, ?string $visitorId = null): void
    {
        if (! in_array($cta, self::CTAS, true) || $this->declined($request)) {
            return;
        }

        $visitorId ??= $request->attributes->get('landing_visitor_id')
            ?? $request->cookies->get((string) config('landing_traffic.cookie', 'vrp_lv'));

        if (! is_string($visitorId) || ! Str::isUuid($visitorId)) {
            return;
        }

        try {
            $visit = LandingVisit::query()
                ->where('visitor_id', $visitorId)
                ->where('page', 'landing')
                ->whereNull('cta')
                ->orderByDesc('visited_at')
                ->orderByDesc('id')
                ->first();

            $visit?->update(['cta' => $cta]);
        } catch (Throwable $e) {
            Log::warning('landing_traffic.cta_failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function pageFromRequest(Request $request): string
    {
        $route = $request->route()?->getName();

        return match ($route) {
            'login' => 'login',
            'register', 'account-request.create' => 'register',
            default => 'landing',
        };
    }

    private function country(Request $request): ?string
    {
        $ip = $request->ip();

        if (! is_string($ip) || $ip === '') {
            return null;
        }

        try {
            return $this->geoLocator->countryFor($ip);
        } catch (Throwable $e) {
            Log::debug('landing_traffic.geo_failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function hashIp(?string $ip): ?string
    {
        $ip = trim((string) $ip);

        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        return hash('sha256', $ip.'|'.(string) config('app.key'));
    }

    private function header(Request $request, string $name): ?string
    {
        $value = $request->headers->get($name);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function truncate(?string $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }
}
