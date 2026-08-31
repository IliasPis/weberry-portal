<?php

namespace App\Http\Requests\Admin;

use App\Support\TicketOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(TicketOptions::STATUSES)],
            'priority' => ['required', Rule::in(TicketOptions::PRIORITIES)],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
