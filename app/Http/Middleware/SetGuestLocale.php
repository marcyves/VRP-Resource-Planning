<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetGuestLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        $locale = $request->session()->get('locale');
        $allowed = config('terminology.base_locales', ['fr', 'en', 'it']);

        if (is_string($locale) && in_array($locale, $allowed, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
