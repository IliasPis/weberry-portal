<?php

namespace App\Http\Requests\Admin;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Client::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:32', 'unique:clients,vat_number'],
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
            'create_login' => ['sometimes', 'boolean'],
            'login_name' => ['required_if:create_login,true', 'nullable', 'string', 'max:255'],
            'login_email' => ['required_if:create_login,true', 'nullable', 'email', 'max:255', 'unique:users,email'],
            'login_password' => ['required_if:create_login,true', 'nullable', 'string', 'min:8', 'max:255'],
        ];
    }
}
