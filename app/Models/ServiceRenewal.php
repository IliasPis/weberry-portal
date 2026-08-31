<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id', 'previous_expiration', 'new_expiration', 'renewed_at', 'renewed_by_user_id',
    'amount_charged', 'status', 'notes',
])]
class ServiceRenewal extends Model
{
    protected function casts(): array
    {
        return [
            'previous_expiration' => 'date',
            'new_expiration' => 'date',
            'renewed_at' => 'datetime',
            'amount_charged' => 'decimal:2',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function renewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renewed_by_user_id');
    }
}
