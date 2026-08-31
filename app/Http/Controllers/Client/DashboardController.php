<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The client self-service dashboard. Placeholder content for now -
     * the full overview (services, requests, renewals, invoices) is
     * built once those entities exist.
     */
    public function index(): Response
    {
        return Inertia::render('Client/Dashboard');
    }
}
