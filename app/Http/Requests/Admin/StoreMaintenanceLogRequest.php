<?php

namespace App\Http\Requests\Admin;

use App\Models\MaintenanceLog;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MaintenanceLog::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'website_id' => ['required', 'exists:websites,id'],
            'performed_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'notes_internal' => ['nullable', 'string'],
            'notes_client' => ['nullable', 'string'],
            'client_visible' => ['boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['string'],
        ];
    }
}
