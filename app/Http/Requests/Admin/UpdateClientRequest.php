<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('client'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'vat_number' => [
                'nullable', 'string', 'max:32',
                Rule::unique('clients', 'vat_number')->ignore($this->route('client')),
            ],
            'tax_office' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'address' => ['nullable', 'string'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'technical_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:lead,active,inactive,suspended,archived'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'start_date' => ['nullable', 'date'],
            'internal_notes' => ['nullable', 'string'],
            'client_visible_notes' => ['nullable', 'string'],
            'next_action' => ['nullable', 'string', 'max:255'],
        ];
    }
}
