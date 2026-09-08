<?php

namespace App\Http\Requests\Admin;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
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
            'invoice_number' => ['required', 'string', 'max:64', 'unique:invoices,invoice_number'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'vat_amount' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'sent', 'paid', 'unpaid', 'overdue', 'cancelled'])],
            'pdf' => ['nullable', 'file', 'max:20480', 'mimes:pdf'],
        ];
    }
}
