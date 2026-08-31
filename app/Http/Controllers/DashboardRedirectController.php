<?php

namespace App\Http\Controllers;

use App\Support\PortalRoles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    /**
     * The single, stable post-login destination. It exists purely to
     * forward the user to the dashboard for their side of the app, so
     * nothing else needs to know a user's role ahead of time.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->user()->hasAnyRole(PortalRoles::ADMIN_SIDE)) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('client.dashboard');
    }
}
