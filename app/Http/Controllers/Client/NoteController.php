<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreNoteRequest;
use App\Models\Note;
use App\Services\ActivityLogger;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Note::class);

        $notes = Note::query()
            ->where('client_id', $request->user()->client_id)
            ->visibleToClient()
            ->with(['author:id,name', 'website:id,domain', 'ticket:id,title'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Client/Notes/Index', ['notes' => $notes]);
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $user = $request->user();
        $client = $user->client;

        $note = Note::create([
            ...$request->validated(),
            'client_id' => $client->id,
            'author_user_id' => $user->id,
            'visibility' => 'shared',
        ]);

        $this->notifications->notifyAdmins(
            'note.client',
            "Ο πελάτης {$client->company_name} πρόσθεσε σημείωση",
            str($note->body)->limit(120)->toString(),
        );

        $this->activity->record(
            $client,
            'note.client',
            'Ο πελάτης πρόσθεσε μια σημείωση.',
            clientVisible: true,
            websiteId: $note->website_id,
            entityType: Note::class,
            entityId: $note->id,
        );

        return back();
    }
}
