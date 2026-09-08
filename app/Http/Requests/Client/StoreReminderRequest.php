<?php

namespace App\Http\Requests\Client;

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
        $clientId = $this->user()->client_id;

        return [
            'website_id' => ['nullable', Rule::exists('websites', 'id')->where('client_id', $clientId)],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('client_id', $clientId)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'remind_at' => ['required', 'date'],
            'remind_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
