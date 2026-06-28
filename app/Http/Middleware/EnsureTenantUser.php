<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.companies.index');
        }

        if ($user->company_id === null) {
            abort(403, __('messages.tenant_company_required'));
        }

        return $next($request);
    }
}
