<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Admin-only, immutable. There is deliberately no #[Fillable] beyond what
 * AuditLogger::record() writes, no update/delete anywhere in the app, and
 * this model is never returned to the Client Portal.
 */
class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'previous_value' => 'array',
            'new_value' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
