<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWebsiteRequest;
use App\Http\Requests\Admin\UpdateWebsiteRequest;
use App\Models\Client;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Website::class);

        $sort = in_array($request->string('sort')->toString(), ['name', 'domain', 'status', 'ssl_expires_at', 'created_at'])
            ? $request->string('sort')->toString()
            : 'name';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';

        $websites = Website::query()
            ->with('client:id,company_name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($term) {
                    $query->whereLike('name', $term)
                        ->orWhereLike('domain', $term)
                        ->orWhereHas('client', fn ($q) => $q->whereLike('company_name', $term));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Websites/Index', [
            'websites' => $websites,
            'filters' => $request->only(['search', 'status', 'sort', 'direction']),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }

    public function store(StoreWebsiteRequest $request): RedirectResponse
    {
        Website::create($request->validated());

        return back();
    }

    public function show(Website $website): Response
    {
        $this->authorize('view', $website);

        $website->load([
            'client:id,company_name',
            'services' => fn ($query) => $query->with('serviceType')->orderBy('expiration_date'),
            'maintenanceLogs.items',
            'notes' => fn ($query) => $query->with('author:id,name')->orderByDesc('created_at'),
        ]);

        return Inertia::render('Admin/Websites/Show', [
            'website' => $website,
        ]);
    }

    public function update(UpdateWebsiteRequest $request, Website $website): RedirectResponse
    {
        $website->update($request->validated());

        return back();
    }

    /**
     * "also_for_client" false (default) soft-deletes: gone from the admin's
     * own lists/pages, but the client-facing queries include trashed rows so
     * it stays visible to them. true force-deletes: gone everywhere.
     */
    public function destroy(Request $request, Website $website): RedirectResponse
    {
        $this->authorize('delete', $website);

        $request->boolean('also_for_client') ? $website->forceDelete() : $website->delete();

        return redirect()->route('admin.websites.index');
    }
}
