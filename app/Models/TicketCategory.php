<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['service_type_id', 'name', 'creates_task', 'is_active', 'sort_order'])]
class TicketCategory extends Model
{
    protected function casts(): array
    {
        return [
            'creates_task' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
