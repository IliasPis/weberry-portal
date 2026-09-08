<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Writes to the admin-only, immutable audit trail. Called explicitly at
 * the handful of sensitive mutation points (credential access, price
 * changes, status changes on financial records) rather than via a global
 * model observer, so the log stays meaningful instead of recording every
 * touch of every model.
 */
class AuditLogger
{
    public function record(string $action, Model $entity, ?array $previous = null, ?array $new = null): void
    {
        AuditLog::create([
            'actor_user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entity::class,
            'entity_id' => $entity->getKey(),
            'previous_value' => $previous,
            'new_value' => $new,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
