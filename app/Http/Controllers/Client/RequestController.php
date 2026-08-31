<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreRequestRequest;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\Website;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequestController extends Controller
{
    public function __construct(private readonly FileUploadService $fileUploadService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $view = $request->string('view')->toString() ?: 'open';
        $clientId = $request->user()->client_id;

        $tickets = Ticket::query()
            ->where('client_id', $clientId)
            ->with(['website:id,domain'])
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

        return Inertia::render('Client/Requests/Create', [
            'websites' => Website::where('client_id', $clientId)->get(['id', 'name', 'domain']),
            'services' => Service::where('client_id', $clientId)->get(['id', 'name', 'website_id']),
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

        return redirect()->route('client.requests.show', $ticket);
    }

    public function show(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'website:id,name,domain',
            'service:id,name',
            'visibleMessages.author:id,name',
            'visibleMessages.attachments.file',
        ]);

        return Inertia::render('Client/Requests/Show', [
            'ticket' => $ticket,
        ]);
    }
}
