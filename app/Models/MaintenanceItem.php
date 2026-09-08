<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['maintenance_log_id', 'item_key', 'checked', 'note'])]
class MaintenanceItem extends Model
{
    protected function casts(): array
    {
        return ['checked' => 'boolean'];
    }

    public function maintenanceLog(): BelongsTo
    {
        return $this->belongsTo(MaintenanceLog::class);
    }
}
