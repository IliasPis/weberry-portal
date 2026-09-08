<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use App\Models\ServiceType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $sort = in_array($request->string('sort')->toString(), ['company_name', 'status', 'priority', 'start_date', 'created_at'])
            ? $request->string('sort')->toString()
            : 'company_name';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';

        $clients = Client::query()
            ->withCount(['websites', 'services'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($term) {
                    $query->where('company_name', 'ilike', $term)
                        ->orWhere('vat_number', 'ilike', $term)
                        ->orWhere('email', 'ilike', $term)
                        ->orWhere('phone', 'ilike', $term)
                        ->orWhereHas('websites', fn ($q) => $q->where('domain', 'ilike', $term));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status', 'priority', 'sort', 'direction']),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($request->validated());

        return back();
    }

    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load([
            'websites' => fn ($query) => $query->orderBy('name'),
            'services' => fn ($query) => $query->with('serviceType')->orderBy('expiration_date'),
            'tickets' => fn ($query) => $query->with('assignedTo:id,name')->orderByDesc('created_at'),
            'notes' => fn ($query) => $query->with('author:id,name', 'website:id,domain')->orderByDesc('created_at'),
            'reminders' => fn ($query) => $query->with('assignedTo:id,name')->orderBy('remind_at'),
            'tasks' => fn ($query) => $query->with('assignedTo:id,name')->orderByDesc('created_at'),
            'invoices' => fn ($query) => $query->orderByDesc('issue_date'),
            'documents' => fn ($query) => $query->with('file', 'uploadedBy:id,name')->orderByDesc('created_at'),
            'activityLogs' => fn ($query) => $query->with('actor:id,name')->orderByDesc('created_at')->limit(50),
            'contacts' => fn ($query) => $query->with('user:id,name,email'),
            'tags',
        ]);

        return Inertia::render('Admin/Clients/Show', [
            'client' => $client,
            'serviceTypes' => ServiceType::query()->orderBy('name')->get(['id', 'name', 'category']),
            'assignableUsers' => User::role(['super_admin', 'admin', 'staff'])->get(['id', 'name']),
            'allTags' => Tag::query()->orderBy('name')->get(['id', 'name', 'color']),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return back();
    }

    public function archive(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->update(['status' => 'archived']);

        return back();
    }

    public function attachTag(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validate(['tag_id' => ['required', 'exists:tags,id']]);
        $client->tags()->syncWithoutDetaching([$validated['tag_id']]);

        return back();
    }

    public function detachTag(Client $client, Tag $tag): RedirectResponse
    {
        $this->authorize('update', $client);

        $client->tags()->detach($tag->id);

        return back();
    }
}
