<?php

namespace App\Services;

use App\Models\Group;
use App\Models\LoginEvent;
use App\Models\School;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Collection;

class GroupFollowUpQuery
{
    /**
     * @param  array{school_id?: string, q?: string, from?: string, to?: string}  $filters
     * @return array{
     *     members: Collection<int, object>,
     *     groups: Collection<int, Group>,
     *     schools: Collection<int, School>,
     *     connectedCount: int,
     *     idleCount: int,
     *     totalMembers: int,
     *     connectedPercent: float,
     *     idlePercent: float,
     *     schoolIds: array<int, int>
     * }
     */
    public function forViewer(User $viewer, array $filters = []): array
    {
        $assignedIds = $viewer->assignedSchoolIds();
        $restrictToAssigned = $assignedIds->isNotEmpty();

        $schoolsQuery = School::query()
            ->where('company_id', $viewer->company_id)
            ->orderBy('name');

        if ($restrictToAssigned) {
            $schoolsQuery->whereIn('id', $assignedIds->all());
        }

        $schools = $schoolsQuery->get(['id', 'name']);

        $schoolIds = $schools->pluck('id')->map(fn ($id) => (int) $id)->all();

        $requestedSchoolId = isset($filters['school_id']) && $filters['school_id'] !== ''
            ? (int) $filters['school_id']
            : null;

        $schoolFilterActive = $requestedSchoolId !== null && in_array($requestedSchoolId, $schoolIds, true);
        if ($schoolFilterActive) {
            $schoolIds = [$requestedSchoolId];
        }

        $filterBySchools = $restrictToAssigned || $schoolFilterActive;

        $members = $this->members($viewer, $filterBySchools ? $schoolIds : null, $filters);
        $groups = $this->groups($viewer, $filterBySchools ? $schoolIds : null, $filters);

        $connectedCount = $members->where('connected', true)->count();
        $idleCount = $members->count() - $connectedCount;
        $totalMembers = $members->count();
        $connectedPercent = $totalMembers > 0 ? ($connectedCount / $totalMembers) * 100 : 0;
        $idlePercent = $totalMembers > 0 ? 100 - $connectedPercent : 0;

        return [
            'members' => $members,
            'groups' => $groups,
            'schools' => $schools,
            'connectedCount' => $connectedCount,
            'idleCount' => $idleCount,
            'totalMembers' => $totalMembers,
            'connectedPercent' => $connectedPercent,
            'idlePercent' => $idlePercent,
            'schoolIds' => $schoolIds,
        ];
    }

    /**
     * @param  array<int, int>|null  $schoolIds
     * @param  array{q?: string, from?: string, to?: string}  $filters
     * @return Collection<int, object>
     */
    private function members(User $viewer, ?array $schoolIds, array $filters): Collection
    {
        $query = User::query()
            ->where('company_id', $viewer->company_id)
            ->where('status_id', Status::READER)
            ->orderBy('name');

        if ($schoolIds !== null) {
            $query->whereHas('schools', fn ($inner) => $inner->whereIn('schools.id', $schoolIds));
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $users = $query->get(['id', 'name', 'email']);

        $aggregates = $this->loginAggregates($users->pluck('id')->all(), $filters);

        return $users->map(function (User $user) use ($aggregates) {
            $stats = $aggregates->get($user->id);
            $successCount = (int) ($stats->success_count ?? 0);
            $failedCount = (int) ($stats->failed_count ?? 0);
            $lastSuccessAt = $stats->last_success_at ?? null;

            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'last_success_at' => $lastSuccessAt,
                'connected' => $successCount > 0,
            ];
        });
    }

    /**
     * @param  array<int, int|string>  $userIds
     * @param  array{from?: string, to?: string}  $filters
     */
    private function loginAggregates(array $userIds, array $filters): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        $query = LoginEvent::query()
            ->selectRaw('user_id')
            ->selectRaw('MAX(CASE WHEN success = 1 THEN occurred_at END) as last_success_at')
            ->selectRaw('SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count')
            ->selectRaw('SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed_count')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id');

        if (! empty($filters['from'])) {
            $query->where('occurred_at', '>=', $filters['from'].' 00:00:00');
        }

        if (! empty($filters['to'])) {
            $query->where('occurred_at', '<=', $filters['to'].' 23:59:59');
        }

        return $query->get()->keyBy('user_id');
    }

    /**
     * @param  array<int, int>|null  $schoolIds
     * @param  array{from?: string, to?: string}  $filters
     * @return Collection<int, Group>
     */
    private function groups(User $viewer, ?array $schoolIds, array $filters): Collection
    {
        $query = Group::query()
            ->where('company_id', $viewer->company_id)
            ->where('active', true)
            ->with(['courses' => fn ($courses) => $courses->orderBy('name')->with('school:id,name')])
            ->withCount(['plannings as sessions_count' => function ($plannings) use ($filters) {
                if (! empty($filters['from'])) {
                    $plannings->where('begin', '>=', $filters['from'].' 00:00:00');
                }
                if (! empty($filters['to'])) {
                    $plannings->where('begin', '<=', $filters['to'].' 23:59:59');
                }
            }])
            ->orderBy('name');

        if ($schoolIds !== null) {
            $query->whereHas('courses', fn ($courses) => $courses->whereIn('school_id', $schoolIds));
        }

        return $query->get();
    }
}
