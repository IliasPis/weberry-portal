<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

/**
 * Feeds the per-client timeline (both the admin "Activity" tab and the
 * client-visible "Recent Activity" widget read from the same table,
 * client_visible is just a column). Only meaningful business events call
 * this, not every field edit.
 */
class ActivityLogger
{
    public function record(
        Client $client,
        string $action,
        string $description,
        bool $clientVisible = false,
        ?int $websiteId = null,
        ?string $entityType = null,
        ?int $entityId = null,
    ): void {
        ActivityLog::create([
            'client_id' => $client->id,
            'website_id' => $websiteId,
            'actor_user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'client_visible' => $clientVisible,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }
}
