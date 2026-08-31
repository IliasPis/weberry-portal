<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id', 'user_id', 'contact_title', 'is_primary', 'can_view_billing', 'can_view_invoices',
    'can_view_requests', 'can_view_documents', 'can_create_requests', 'can_manage_account',
])]
class ClientContact extends Model
{
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'can_view_billing' => 'boolean',
            'can_view_invoices' => 'boolean',
            'can_view_requests' => 'boolean',
            'can_view_documents' => 'boolean',
            'can_create_requests' => 'boolean',
            'can_manage_account' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
