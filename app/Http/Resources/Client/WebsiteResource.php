<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The explicit whitelist for what a client is ever allowed to see about
 * their own website - server, IP, database name, PHP version, and
 * internal_notes never leave this class. Business rule #7/#9 from the
 * spec: private is the safe default wherever there's doubt.
 */
class WebsiteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'domain' => $this->domain,
            'production_url' => $this->production_url,
            'status' => $this->status,
            'cms' => $this->cms,
            'hosting_provider' => $this->hosting_provider,
            'ssl_provider' => $this->ssl_provider,
            'ssl_expires_at' => $this->ssl_expires_at,
            'client_visible_notes' => $this->client_visible_notes,
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'services_count' => $this->whenCounted('services'),
            'open_requests_count' => $this->whenCounted('openTickets'),
        ];
    }
}
