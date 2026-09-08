<?php

namespace App\Http\Requests\Admin;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Document::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'website_id' => ['nullable', 'exists:websites,id'],
            'type' => ['required', Rule::in([
                'invoice', 'contract', 'quote', 'proposal', 'technical_report', 'agreement',
                'hosting_document', 'domain_document', 'maintenance_report', 'other',
            ])],
            'description' => ['nullable', 'string'],
            'visible_to_client' => ['boolean'],
            'file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip'],
        ];
    }
}
