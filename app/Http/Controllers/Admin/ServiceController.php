<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Website;
use App\Services\ServiceDetailSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceDetailSynchronizer $detailSynchronizer) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Service::class);

        $sort = in_array($request->string('sort')->toString(), ['name', 'status', 'expiration_date', 'client_price', 'created_at'])
            ? $request->string('sort')->toString()
            : 'expiration_date';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';

        $services = Service::query()
            ->with(['client:id,company_name', 'website:id,domain', 'serviceType'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($term) {
                    $query->whereLike('name', $term)
                        ->orWhereHas('client', fn ($q) => $q->whereLike('company_name', $term))
                        ->orWhereHas('website', fn ($q) => $q->whereLike('domain', $term));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'serviceType',
                fn ($q) => $q->where('category', $request->string('category')),
            ))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Services/Index', [
            'services' => $services,
            'filters' => $request->only(['search', 'status', 'category', 'sort', 'direction']),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'websites' => Website::query()->orderBy('domain')->get(['id', 'client_id', 'domain']),
            'serviceTypes' => ServiceType::query()->orderBy('name')->get(['id', 'name', 'category']),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $service = Service::create($validated);

        $category = ServiceType::findOrFail($validated['service_type_id'])->category;
        $this->detailSynchronizer->sync($service, $validated, $category);

        return back();
    }

    public function show(Service $service): Response
    {
        $this->authorize('view', $service);

        $service->load([
            'client:id,company_name',
            'website:id,name,domain',
            'serviceType.ticketCategories',
            'hostingDetail',
            'domainDetail',
            'maintenanceDetail',
            'licenseDetail',
        ]);

        return Inertia::render('Admin/Services/Show', [
            'service' => $service,
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $validated = $request->validated();

        $service->update($validated);

        $category = ServiceType::findOrFail($validated['service_type_id'])->category;
        $this->detailSynchronizer->sync($service, $validated, $category);

        return back();
    }

    public function destroy(Request $request, Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $request->boolean('also_for_client') ? $service->forceDelete() : $service->delete();

        return redirect()->route('admin.services.index');
    }
}
