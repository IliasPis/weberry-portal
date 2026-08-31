<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('website'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => [
                'required', 'string', 'max:255',
                Rule::unique('websites', 'domain')->ignore($this->route('website')),
            ],
            'production_url' => ['nullable', 'url', 'max:255'],
            'staging_url' => ['nullable', 'url', 'max:255'],
            'cms' => ['nullable', 'string', 'max:64'],
            'wp_version' => ['nullable', 'string', 'max:32'],
            'woo_version' => ['nullable', 'string', 'max:32'],
            'theme' => ['nullable', 'string', 'max:255'],
            'hosting_provider' => ['nullable', 'string', 'max:255'],
            'hosting_package' => ['nullable', 'string', 'max:255'],
            'server' => ['nullable', 'string', 'max:255'],
            'server_ip' => ['nullable', 'string', 'max:64'],
            'php_version' => ['nullable', 'string', 'max:16'],
            'database_name' => ['nullable', 'string', 'max:255'],
            'cdn_provider' => ['nullable', 'string', 'max:255'],
            'cloudflare_enabled' => ['boolean'],
            'ssl_provider' => ['nullable', 'string', 'max:255'],
            'ssl_expires_at' => ['nullable', 'date'],
            'analytics_id' => ['nullable', 'string', 'max:255'],
            'search_console' => ['nullable', 'string', 'max:255'],
            'smtp_provider' => ['nullable', 'string', 'max:255'],
            'backup_provider' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'internal_notes' => ['nullable', 'string'],
            'client_visible_notes' => ['nullable', 'string'],
        ];
    }
}
