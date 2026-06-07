<?php

namespace App\Http\Middleware;

use App\Support\DatabaseAvailability;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseIsAvailable
{
    public function __construct(
        private DatabaseAvailability $databaseAvailability,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->databaseAvailability->isAvailable()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.maintenance'),
                ], 503);
            }

            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }
}
