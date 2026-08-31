<?php

namespace App\Http\Controllers;

use App\Models\File as FileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    /**
     * A single download endpoint for every attachment in the app. Access
     * is re-checked here on every request against whatever the file is
     * attached to - never a bare signed URL that could be shared onward.
     */
    public function __invoke(Request $request, FileModel $file): StreamedResponse
    {
        $attachment = $file->load('ticketAttachment.ticketMessage.ticket')->ticketAttachment;

        if ($attachment) {
            $this->authorize('view', $attachment->ticketMessage->ticket);

            if ($attachment->ticketMessage->message_type === 'internal_note'
                && ! $request->user()->can('tickets.internalNotes.view')) {
                abort(403);
            }
        } else {
            // A file not attached to anything recognizable is nobody's to download.
            abort(404);
        }

        return Storage::disk($file->storage_disk)->download($file->storage_key, $file->original_filename);
    }
}
