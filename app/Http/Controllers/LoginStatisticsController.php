<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginStatisticsController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();

        if ($viewer === null || ! $viewer->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'outcome' => ['nullable', 'in:all,success,failed'],
            'q' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'company_id' => ['nullable', 'string', 'max:20'],
        ]);

        $query = LoginEvent::query()
            ->with('company')
            ->visibleTo($viewer)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        $this->applyFilters($query, $validated, $viewer);

        $successCount = (clone $query)->where('success', true)->count();
        $failedCount = (clone $query)->where('success', false)->count();
        $totalCount = $successCount + $failedCount;
        $uniqueUsernames = (clone $query)->reorder()->distinct()->count('username');

        $events = $query->paginate(50)->withQueryString();

        $successPercent = $totalCount > 0 ? ($successCount / $totalCount) * 100 : 0;
        $failedPercent = $totalCount > 0 ? 100 - $successPercent : 0;

        $companies = $viewer->isSuperAdmin()
            ? Company::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('login-stats.index', [
            'events' => $events,
            'successCount' => $successCount,
            'failedCount' => $failedCount,
            'totalCount' => $totalCount,
            'uniqueUsernames' => $uniqueUsernames,
            'successPercent' => $successPercent,
            'failedPercent' => $failedPercent,
            'companies' => $companies,
            'isSuperAdmin' => $viewer->isSuperAdmin(),
            'filters' => [
                'outcome' => $validated['outcome'] ?? 'all',
                'q' => $validated['q'] ?? '',
                'from' => $validated['from'] ?? '',
                'to' => $validated['to'] ?? '',
                'company_id' => $validated['company_id'] ?? '',
            ],
            'formAction' => $viewer->isSuperAdmin()
                ? route('super-admin.login-stats.index')
                : route('login-stats.index'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters($query, array $filters, User $viewer): void
    {
        $outcome = $filters['outcome'] ?? 'all';

        if ($outcome === 'success') {
            $query->where('success', true);
        } elseif ($outcome === 'failed') {
            $query->where('success', false);
        }

        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('username', 'like', '%'.$search.'%')
                    ->orWhere('ip', 'like', '%'.$search.'%')
                    ->orWhere('geo_label', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['from'])) {
            $query->where('occurred_at', '>=', $filters['from'].' 00:00:00');
        }

        if (! empty($filters['to'])) {
            $query->where('occurred_at', '<=', $filters['to'].' 23:59:59');
        }

        if ($viewer->isSuperAdmin() && array_key_exists('company_id', $filters) && $filters['company_id'] !== null && $filters['company_id'] !== '') {
            if ($filters['company_id'] === 'none') {
                $query->whereNull('company_id');
            } else {
                $query->where('company_id', (int) $filters['company_id']);
            }
        }
    }
}
