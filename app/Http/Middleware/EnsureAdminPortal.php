<?php

namespace App\Http\Middleware;

use App\Support\PortalRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPortal
{
    /**
     * Restrict /admin/* to admin-side roles. A client-side user is
     * redirected to their own portal rather than shown a 403, so a
     * mismatched link never leaks that the admin area exists.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->hasAnyRole(PortalRoles::ADMIN_SIDE)) {
            return $next($request);
        }

        if ($user?->hasAnyRole(PortalRoles::CLIENT_SIDE)) {
            return redirect()->route('client.dashboard');
        }

        abort(403);
    }
}
