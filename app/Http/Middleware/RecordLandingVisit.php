<?php

namespace App\Http\Middleware;

use App\Services\LandingVisitRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordLandingVisit
{
    public function __construct(private LandingVisitRecorder $recorder) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $this->recorder->declined($request)) {
            $this->recorder->ensureVisitorCookie($request);
        }

        $response = $next($request);

        if ($this->shouldRecord($request, $response)) {
            $this->recorder->record($request);
        }

        return $response;
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $purpose = strtolower((string) $request->headers->get('Sec-Purpose', $request->headers->get('Purpose', '')));

        if (str_contains($purpose, 'prefetch')) {
            return false;
        }

        return true;
    }
}
