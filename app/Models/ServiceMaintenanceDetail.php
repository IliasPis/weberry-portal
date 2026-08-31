<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_id', 'plan', 'included_hours', 'used_hours', 'sla'])]
class ServiceMaintenanceDetail extends Model
{
    protected function casts(): array
    {
        return [
            'included_hours' => 'integer',
            'used_hours' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
