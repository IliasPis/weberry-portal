<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Service::class);

        $services = Service::query()
            ->withTrashed()
            ->where('client_id', $request->user()->client_id)
            ->with(['serviceType', 'website'])
            ->orderBy('expiration_date')
            ->get();

        return Inertia::render('Client/Services/Index', [
            'services' => ServiceResource::collection($services),
        ]);
    }
}
