<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Business rule #1/#2: a client never sees internal_cost or margin, so
 * this class simply never reads those columns - not a matter of hiding
 * them on the frontend, they never leave the server.
 */
class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->serviceType->category,
            'service_type_name' => $this->serviceType->name,
            'website' => $this->whenLoaded('website', fn () => $this->website ? [
                'id' => $this->website->id,
                'domain' => $this->website->domain,
            ] : null),
            'start_date' => $this->start_date,
            'expiration_date' => $this->expiration_date,
            'renewal_date' => $this->renewal_date,
            'billing_cycle' => $this->billing_cycle,
            'client_price' => $this->client_price,
            'auto_renewal' => $this->auto_renewal,
            'status' => $this->status,
            'notes_client' => $this->notes_client,
        ];
    }
}
