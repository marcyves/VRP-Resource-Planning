<?php

namespace App\Http\Middleware;

use App\Support\TerminologyLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetTerminologyLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale(
            TerminologyLocale::resolve($request->user()?->company)
        );

        return $next($request);
    }
}
