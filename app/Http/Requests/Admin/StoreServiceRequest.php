<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Service::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'website_id' => ['nullable', 'exists:websites,id'],
            'service_type_id' => ['required', 'exists:service_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'renewal_date' => ['nullable', 'date'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly,one_time'],
            'internal_cost' => ['nullable', 'numeric', 'min:0'],
            'client_price' => ['nullable', 'numeric', 'min:0'],
            'auto_renewal' => ['boolean'],
            'status' => ['required', 'in:active,pending,expired,cancelled'],
            'notes_internal' => ['nullable', 'string'],
            'notes_client' => ['nullable', 'string'],

            // Type-specific detail fields - only the ones matching the
            // selected service type's category are persisted.
            'hosting_package' => ['nullable', 'string', 'max:255'],
            'hosting_server' => ['nullable', 'string', 'max:255'],
            'hosting_ip' => ['nullable', 'string', 'max:64'],
            'hosting_storage' => ['nullable', 'string', 'max:64'],
            'domain_registrar' => ['nullable', 'string', 'max:255'],
            'domain_nameservers' => ['nullable', 'string'],
            'domain_dns_provider' => ['nullable', 'string', 'max:255'],
            'maintenance_plan' => ['nullable', 'string', 'max:255'],
            'maintenance_included_hours' => ['nullable', 'integer', 'min:0'],
            'maintenance_used_hours' => ['nullable', 'integer', 'min:0'],
            'maintenance_sla' => ['nullable', 'string', 'max:255'],
            'license_type' => ['nullable', 'string', 'max:255'],
            'license_purchased_by' => ['nullable', 'in:us,client'],
            'license_status' => ['nullable', 'string', 'max:64'],
        ];
    }
}
