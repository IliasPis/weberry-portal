<?php

namespace App\Http\Requests\Admin;

use App\Models\Reminder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Reminder::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:clients,id'],
            'website_id' => ['nullable', 'exists:websites,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'remind_at' => ['required', 'date'],
            'remind_time' => ['nullable', 'date_format:H:i'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'repeat_rule' => ['required', Rule::in(['none', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'])],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
