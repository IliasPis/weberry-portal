<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('ticket'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            // Only ever honored server-side for admin-side users who also
            // hold tickets.internalNotes.view - see TicketMessageController.
            'is_internal' => ['boolean'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,xls,xlsx,csv,txt,zip,mp4,mov'],
        ];
    }
}
