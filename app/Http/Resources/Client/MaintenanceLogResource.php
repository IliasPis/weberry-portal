<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * notes_internal and performed_by_user_id never leave this class - the
 * client sees that maintenance happened and what was done, not who did
 * it internally or any internal commentary.
 */
class MaintenanceLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'performed_at' => $this->performed_at,
            'duration_minutes' => $this->duration_minutes,
            'notes_client' => $this->notes_client,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'item_key' => $item->item_key,
                'checked' => $item->checked,
            ])),
        ];
    }
}
