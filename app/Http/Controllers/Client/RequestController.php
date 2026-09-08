<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreRequestRequest;
use App\Models\Service;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Website;
use App\Notifications\GenericNotification;
use App\Services\ActivityLogger;
use App\Services\FileUploadService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequestController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly NotificationDispatcher $notifications,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $view = $request->string('view')->toString() ?: 'open';
        $clientId = $request->user()->client_id;

        $tickets = Ticket::query()
            ->withTrashed()
            ->where('client_id', $clientId)
            ->with(['website:id,domain', 'category:id,name'])
            ->when($view === 'open', fn ($query) => $query->whereIn('status', ['new', 'open', 'in_progress', 'scheduled']))
            ->when($view === 'waiting', fn ($query) => $query->whereIn('status', ['waiting_client', 'waiting_third_party']))
            ->when($view === 'completed', fn ($query) => $query->where('status', 'completed'))
            ->when($view === 'closed', fn ($query) => $query->whereIn('status', ['closed', 'cancelled']))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Client/Requests/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['view']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Ticket::class);

        $clientId = $request->user()->client_id;
        $services = Service::where('client_id', $clientId)->get(['id', 'name', 'website_id', 'service_type_id']);
        $serviceTypeIds = $services->pluck('service_type_id')->unique();

        return Inertia::render('Client/Requests/Create', [
            'websites' => Website::where('client_id', $clientId)->get(['id', 'name', 'domain']),
            'services' => $services,
            'categories' => TicketCategory::query()
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('service_type_id')->orWhereIn('service_type_id', $serviceTypeIds))
                ->orderBy('sort_order')
                ->get(['id', 'service_type_id', 'name']),
        ]);
    }

    public function store(StoreRequestRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $ticket = Ticket::create([
            ...$validated,
            'client_id' => $user->client_id,
            'created_by_user_id' => $user->id,
            'status' => 'new',
        ]);

        $message = $ticket->messages()->create([
            'author_user_id' => $user->id,
            'body' => $validated['description'],
            'message_type' => 'client_message',
            'is_read_by_client' => true,
        ]);

        foreach ($request->file('attachments', []) as $uploadedFile) {
            $file = $this->fileUploadService->store($uploadedFile, $user, 'tickets');
            $message->attachments()->create(['file_id' => $file->id]);
        }

        $client = $user->client;

        $this->notifications->notifyAdmins(
            'ticket.created',
            "Νέο αίτημα από {$client->company_name}",
            $ticket->title,
            route('admin.tickets.show', $ticket),
        );

        // Confirm receipt to the person who actually submitted it (not the
        // whole client account), separately from the admin-facing notice.
        $user->notify(new GenericNotification(
            'ticket.confirmation',
            'Λάβαμε το αίτημά σας',
            $ticket->title,
            route('client.requests.show', $ticket),
        ));

        $this->activity->record(
            $client,
            'ticket.created',
            'Ο πελάτης υπέβαλε νέο αίτημα: '.$ticket->title,
            clientVisible: true,
            websiteId: $ticket->website_id,
            entityType: Ticket::class,
            entityId: $ticket->id,
        );

        // Categories flagged "creates_task" (configured per-service by the
        // admin) mean "build/fix something" rather than a question - those
        // get a linked, client-visible Task automatically, so the work is
        // tracked from the moment it comes in.
        if ($ticket->category?->creates_task) {
            $task = Task::create([
                'client_id' => $ticket->client_id,
                'website_id' => $ticket->website_id,
                'related_ticket_id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'priority' => $ticket->priority,
                'status' => 'todo',
                'client_visible' => true,
            ]);

            $this->activity->record(
                $client,
                'task.created',
                'Δημιουργήθηκε εργασία από το αίτημα: '.$task->title,
                clientVisible: true,
                websiteId: $task->website_id,
                entityType: Task::class,
                entityId: $task->id,
            );
        }

        return redirect()->route('client.requests.show', $ticket);
    }

    public function show(int $ticket): Response
    {
        $ticket = Ticket::withTrashed()->findOrFail($ticket);
        $this->authorize('view', $ticket);

        $ticket->load([
            'website:id,name,domain',
            'service:id,name',
            'category:id,name',
            'visibleMessages.author:id,name',
            'visibleMessages.attachments.file',
        ]);

        return Inertia::render('Client/Requests/Show', [
            'ticket' => $ticket,
        ]);
    }
}
