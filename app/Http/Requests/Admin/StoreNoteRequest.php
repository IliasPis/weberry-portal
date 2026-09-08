<?php

namespace App\Http\Requests\Admin;

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
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'website_id' => ['nullable', 'exists:websites,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'visibility' => ['required', Rule::in(['internal', 'shared'])],
            'body' => ['required', 'string'],
        ];
    }
}
