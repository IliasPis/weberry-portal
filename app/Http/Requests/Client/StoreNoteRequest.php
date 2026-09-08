<?php

namespace App\Http\Requests\Client;

use App\Models\Note;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Note::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $clientId = $this->user()->client_id;

        return [
            'website_id' => ['nullable', Rule::exists('websites', 'id')->where('client_id', $clientId)],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('client_id', $clientId)],
            'ticket_id' => ['nullable', Rule::exists('tickets', 'id')->where('client_id', $clientId)],
            'body' => ['required', 'string'],
        ];
    }
}
