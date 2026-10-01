<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\LoginEventRecorder;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecordLoginStatistics
{
    public function __construct(private LoginEventRecorder $recorder) {}

    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
            Lockout::class => 'handleLockout',
        ];
    }

    public function handleLogin(Login $event): void
    {
        $this->safe(function () use ($event) {
            if (! $event->user instanceof User) {
                return;
            }

            $this->recorder->recordSuccess($event->user, request());
        });
    }

    public function handleFailed(Failed $event): void
    {
        $this->safe(function () use ($event) {
            $user = $event->user instanceof User ? $event->user : null;

            $this->recorder->recordFailure($user, $event->credentials, request());
        });
    }

    public function handleLockout(Lockout $event): void
    {
        $this->safe(function () use ($event) {
            $request = $event->request instanceof Request ? $event->request : request();

            $this->recorder->recordFailure(null, $request->all(), $request, true);
        });
    }

    private function safe(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            Log::warning('login_stats.record_failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
