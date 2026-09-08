<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\TicketCategory;
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
            ->with(['client:id,company_name', 'website:id,domain', 'assignedTo:id,name', 'category:id,name'])
            ->when($view === 'open', fn ($query) => $query->whereIn('status', TicketOptions::OPEN_STATUSES))
            ->when($view === 'unassigned', fn ($query) => $query->whereNull('assigned_to_user_id'))
            ->when(! in_array($view, ['open', 'unassigned', 'all']), fn ($query) => $query->where('status', $view))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($term) {
                    $query->whereLike('title', $term)
                        ->orWhereHas('client', fn ($q) => $q->whereLike('company_name', $term));
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('ticket_category_id', $request->integer('category')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'normal' then 2 else 3 end")
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'category', 'priority', 'view']),
            'categories' => TicketCategory::query()->orderBy('sort_order')->get(['id', 'name', 'service_type_id']),
        ]);
    }

    public function show(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'client:id,company_name',
            'website:id,name,domain',
            'service:id,name',
            'category:id,name',
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

    public function destroy(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $request->boolean('also_for_client') ? $ticket->forceDelete() : $ticket->delete();

        return redirect()->route('admin.tickets.index');
    }
}
