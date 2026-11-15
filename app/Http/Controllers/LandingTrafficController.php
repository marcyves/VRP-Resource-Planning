<?php

namespace App\Http\Controllers;

use App\Models\LandingVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LandingTrafficController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();

        if ($viewer === null || ! $viewer->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'locale' => ['nullable', 'in:all,fr,en,it'],
            'device' => ['nullable', 'in:all,desktop,tablet,mobile'],
            'referer_host' => ['nullable', 'string', 'max:255'],
            'utm' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'in:all,landing,login,register'],
        ]);

        $query = LandingVisit::query();
        $this->applyFilters($query, $validated);

        $visitCount = (clone $query)->count();
        $uniqueCount = (clone $query)->reorder()->distinct()->count('visitor_id');
        $ctaCount = (clone $query)->whereNotNull('cta')->count();
        $returningCount = (clone $query)->where('is_returning', true)->count();

        $visits = (clone $query)
            ->orderByDesc('visited_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $deviceCounts = $this->groupedCounts($validated, 'device_type');
        $desktopCount = (int) ($deviceCounts['desktop'] ?? 0);
        $tabletCount = (int) ($deviceCounts['tablet'] ?? 0);
        $mobileCount = (int) ($deviceCounts['mobile'] ?? 0);
        $deviceTotal = max(1, $desktopCount + $tabletCount + $mobileCount);

        return view('landing-traffic.index', [
            'visits' => $visits,
            'visitCount' => $visitCount,
            'uniqueCount' => $uniqueCount,
            'ctaCount' => $ctaCount,
            'returningCount' => $returningCount,
            'topReferers' => $this->topColumn($validated, 'referer_host', $this->ownHost()),
            'topCountries' => $this->topColumn($validated, 'country'),
            'topLocales' => $this->topColumn($validated, 'locale'),
            'desktopCount' => $desktopCount,
            'tabletCount' => $tabletCount,
            'mobileCount' => $mobileCount,
            'desktopPercent' => ($desktopCount / $deviceTotal) * 100,
            'tabletPercent' => ($tabletCount / $deviceTotal) * 100,
            'mobilePercent' => ($mobileCount / $deviceTotal) * 100,
            'filters' => [
                'from' => $validated['from'] ?? '',
                'to' => $validated['to'] ?? '',
                'locale' => $validated['locale'] ?? 'all',
                'device' => $validated['device'] ?? 'all',
                'referer_host' => $validated['referer_host'] ?? '',
                'utm' => $validated['utm'] ?? '',
                'page' => $validated['page'] ?? 'all',
            ],
            'formAction' => route('super-admin.landing-traffic.index'),
            'displayTimezone' => (string) config('landing_traffic.display_timezone', 'Europe/Rome'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters($query, array $filters): void
    {
        $timezone = (string) config('landing_traffic.display_timezone', 'Europe/Rome');

        if (! empty($filters['from'])) {
            $query->where(
                'visited_at',
                '>=',
                Carbon::parse($filters['from'], $timezone)->startOfDay()->utc()
            );
        }

        if (! empty($filters['to'])) {
            $query->where(
                'visited_at',
                '<=',
                Carbon::parse($filters['to'], $timezone)->endOfDay()->utc()
            );
        }

        $locale = $filters['locale'] ?? 'all';
        if (is_string($locale) && $locale !== 'all' && $locale !== '') {
            $query->where('locale', $locale);
        }

        $device = $filters['device'] ?? 'all';
        if (is_string($device) && $device !== 'all' && $device !== '') {
            $query->where('device_type', $device);
        }

        $page = $filters['page'] ?? 'all';
        if (is_string($page) && $page !== 'all' && $page !== '') {
            $query->where('page', $page);
        }

        $host = trim((string) ($filters['referer_host'] ?? ''));
        if ($host !== '') {
            $query->where('referer_host', 'like', '%'.$host.'%');
        }

        $utm = trim((string) ($filters['utm'] ?? ''));
        if ($utm !== '') {
            $like = '%'.$utm.'%';
            $query->where(function ($inner) use ($like) {
                $inner->where('utm_source', 'like', $like)
                    ->orWhere('utm_medium', 'like', $like)
                    ->orWhere('utm_campaign', 'like', $like)
                    ->orWhere('utm_content', 'like', $like)
                    ->orWhere('utm_term', 'like', $like);
            });
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, object>
     */
    private function topColumn(array $filters, string $column, ?string $exclude = null): Collection
    {
        $query = LandingVisit::query()
            ->whereNotNull($column)
            ->where($column, '!=', '');

        $this->applyFilters($query, $filters);

        if ($exclude !== null && $exclude !== '') {
            $query->where($column, '!=', $exclude);
        }

        return $query
            ->selectRaw($column.' as label, count(*) as aggregate')
            ->groupBy($column)
            ->orderByDesc('aggregate')
            ->limit(5)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, int>
     */
    private function groupedCounts(array $filters, string $column): array
    {
        $query = LandingVisit::query();
        $this->applyFilters($query, $filters);

        return $query
            ->selectRaw($column.' as label, count(*) as aggregate')
            ->groupBy($column)
            ->pluck('aggregate', 'label')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function ownHost(): ?string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }
}
