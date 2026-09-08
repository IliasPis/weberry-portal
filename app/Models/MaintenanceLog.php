<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'website_id', 'performed_by_user_id', 'performed_at', 'duration_minutes', 'notes_internal',
    'notes_client', 'client_visible',
])]
class MaintenanceLog extends Model
{
    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'client_visible' => 'boolean',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaintenanceItem::class);
    }
}
