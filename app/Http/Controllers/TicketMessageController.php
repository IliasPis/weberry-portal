<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketMessageRequest;
use App\Models\Ticket;
use App\Services\FileUploadService;
use App\Support\PortalRoles;
use Illuminate\Http\RedirectResponse;

class TicketMessageController extends Controller
{
    public function __construct(private readonly FileUploadService $fileUploadService) {}

    /**
     * Shared by both the admin reply/internal-note composer and the
     * client's reply box - the message_type is inferred from who is
     * actually posting, never taken from client input at face value, so
     * a client can never end up authoring (or seeing) an internal_note.
     */
    public function store(StoreTicketMessageRequest $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if ($user->hasAnyRole(PortalRoles::ADMIN_SIDE)) {
            $wantsInternal = $validated['is_internal'] ?? false;
            $messageType = ($wantsInternal && $user->can('tickets.internalNotes.view'))
                ? 'internal_note'
                : 'admin_reply';
        } else {
            $messageType = 'client_message';
        }

        $message = $ticket->messages()->create([
            'author_user_id' => $user->id,
            'body' => $validated['body'],
            'message_type' => $messageType,
            'is_read_by_admin' => $messageType !== 'client_message',
            'is_read_by_client' => $messageType === 'client_message',
        ]);

        foreach ($request->file('attachments', []) as $uploadedFile) {
            $file = $this->fileUploadService->store($uploadedFile, $user, 'tickets');
            $message->attachments()->create(['file_id' => $file->id]);
        }

        if ($messageType === 'admin_reply' && ! $ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
        }

        return back();
    }
}
