<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\WebsiteResource;
use App\Models\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Website::class);

        $websites = Website::query()
            ->where('client_id', $request->user()->client_id)
            ->withCount(['services', 'openTickets'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Client/Websites/Index', [
            'websites' => WebsiteResource::collection($websites),
        ]);
    }

    public function show(Website $website): Response
    {
        $this->authorize('view', $website);

        $website->load(['services.serviceType', 'services.website'])
            ->loadCount('openTickets');

        return Inertia::render('Client/Websites/Show', [
            'website' => new WebsiteResource($website),
        ]);
    }
}
