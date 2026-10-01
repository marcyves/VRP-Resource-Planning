<?php

namespace App\Services;

use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LoginEventRecorder
{
    public function __construct(private GeoLocator $geoLocator) {}

    public function recordSuccess(User $user, Request $request): void
    {
        $this->store([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'username' => $user->email,
            'ip' => $this->clientIp($request),
            'success' => true,
            'locked_out' => false,
            'occurred_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function recordFailure(?User $user, array $credentials, Request $request, bool $lockedOut = false): void
    {
        $username = $this->attemptedUsername($credentials, $request);
        $resolved = $user ?? $this->resolveUser($username);

        $this->store([
            'company_id' => $resolved?->company_id,
            'user_id' => $resolved?->id,
            'username' => $username !== '' ? $username : (string) ($resolved?->email ?? ''),
            'ip' => $this->clientIp($request),
            'success' => false,
            'locked_out' => $lockedOut,
            'occurred_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function store(array $attributes): void
    {
        $geoLabel = null;

        try {
            $geoLabel = $this->geoLocator->labelFor($attributes['ip']);
        } catch (Throwable $e) {
            Log::warning('login_stats.geo_failed', [
                'ip' => $attributes['ip'],
                'error' => $e->getMessage(),
            ]);
        }

        LoginEvent::query()->create([
            ...$attributes,
            'geo_label' => $geoLabel,
        ]);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function attemptedUsername(array $credentials, Request $request): string
    {
        $value = $credentials['email']
            ?? $credentials['username']
            ?? $request->input('email')
            ?? $request->input('username')
            ?? '';

        return is_string($value) ? trim($value) : '';
    }

    private function resolveUser(string $username): ?User
    {
        if ($username === '') {
            return null;
        }

        return User::query()->where('email', $username)->first();
    }

    private function clientIp(Request $request): string
    {
        return $request->ip() ?: '0.0.0.0';
    }
}
