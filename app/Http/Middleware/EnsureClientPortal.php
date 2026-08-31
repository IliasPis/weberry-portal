<?php

namespace App\Http\Middleware;

use App\Support\PortalRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientPortal
{
    /**
     * Restrict /portal/* to client-side roles. An admin-side user is
     * redirected to their own portal rather than shown a 403.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->hasAnyRole(PortalRoles::CLIENT_SIDE)) {
            return $next($request);
        }

        if ($user?->hasAnyRole(PortalRoles::ADMIN_SIDE)) {
            return redirect()->route('admin.dashboard');
        }

        abort(403);
    }
}
