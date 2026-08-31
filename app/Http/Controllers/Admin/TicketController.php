<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Support\PortalRoles;
use App\Support\TicketOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $view = $request->string('view')->toString() ?: 'open';

        $tickets = Ticket::query()
            ->with(['client:id,company_name', 'website:id,domain', 'assignedTo:id,name'])
            ->when($view === 'open', fn ($query) => $query->whereIn('status', TicketOptions::OPEN_STATUSES))
            ->when($view === 'unassigned', fn ($query) => $query->whereNull('assigned_to_user_id'))
            ->when(! in_array($view, ['open', 'unassigned', 'all']), fn ($query) => $query->where('status', $view))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($term) {
                    $query->where('title', 'ilike', $term)
                        ->orWhereHas('client', fn ($q) => $q->where('company_name', 'ilike', $term));
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'normal' then 2 else 3 end")
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'category', 'priority', 'view']),
        ]);
    }

    public function show(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'client:id,company_name',
            'website:id,name,domain',
            'service:id,name',
            'createdBy:id,name',
            'assignedTo:id,name',
            'messages.author:id,name',
            'messages.attachments.file',
        ]);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'assignableUsers' => User::role(PortalRoles::ADMIN_SIDE)->get(['id', 'name']),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validated();

        $updates = $validated;
        if ($validated['status'] === 'completed' && $ticket->status !== 'completed') {
            $updates['resolved_at'] = now();
        }
        if ($validated['status'] === 'closed' && $ticket->status !== 'closed') {
            $updates['closed_at'] = now();
        }

        $ticket->update($updates);

        return back();
    }
}
