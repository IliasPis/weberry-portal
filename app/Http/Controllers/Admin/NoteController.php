<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNoteRequest;
use App\Models\Client;
use App\Models\Note;
use App\Services\ActivityLogger;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;

class NoteController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
        private readonly ActivityLogger $activity,
    ) {}

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $note = Note::create([
            ...$validated,
            'author_user_id' => $request->user()->id,
        ]);

        if ($note->visibility === 'shared') {
            $client = Client::findOrFail($note->client_id);

            $this->notifications->notifyClient(
                $client,
                'note.shared',
                'Νέα σημείωση από τον διαχειριστή σας',
                str($note->body)->limit(120)->toString(),
            );

            $this->activity->record(
                $client,
                'note.shared',
                'Ο διαχειριστής πρόσθεσε μια σημείωση.',
                clientVisible: true,
                websiteId: $note->website_id,
                entityType: Note::class,
                entityId: $note->id,
            );
        }

        return back();
    }
}
