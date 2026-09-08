<?php

namespace App\Http\Requests\Client;

use App\Models\Ticket;
use App\Support\TicketOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Ticket::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'website_id' => [
                'nullable',
                Rule::exists('websites', 'id')->where('client_id', $this->user()->client_id),
            ],
            'service_id' => [
                'nullable',
                Rule::exists('services', 'id')->where('client_id', $this->user()->client_id),
            ],
            'ticket_category_id' => ['required', Rule::exists('ticket_categories', 'id')->where('is_active', true)],
            'priority' => ['required', Rule::in(TicketOptions::PRIORITIES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'url' => ['nullable', 'string', 'max:255'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,xls,xlsx,csv,txt,zip,mp4,mov'],
        ];
    }
}
