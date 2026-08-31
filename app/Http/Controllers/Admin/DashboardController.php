<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The admin work-queue dashboard. Placeholder content for now - the
     * full "today's work / next action / priority queue" view is built
     * once tickets, tasks, and renewals exist.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard');
    }
}
