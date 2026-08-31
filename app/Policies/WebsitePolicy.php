<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Website;
use App\Support\PortalRoles;

class WebsitePolicy
{
    /**
     * Admin-side lists every website; a client-side user lists their own
     * (scoped by client_id in the controller query, same pattern as
     * TicketPolicy) - both are legitimate "list" cases, unlike Client
     * itself where a client user never lists other clients.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('websites.manage') || $user->hasAnyRole(PortalRoles::CLIENT_SIDE);
    }

    /**
     * An admin with permission can view any website; a client-side user
     * can only view websites that belong to their own client.
     */
    public function view(User $user, Website $website): bool
    {
        return $user->can('websites.manage') || $user->client_id === $website->client_id;
    }

    public function create(User $user): bool
    {
        return $user->can('websites.manage');
    }

    public function update(User $user, Website $website): bool
    {
        return $user->can('websites.manage');
    }

    public function delete(User $user, Website $website): bool
    {
        return $user->can('websites.manage');
    }

    public function restore(User $user, Website $website): bool
    {
        return $user->can('websites.manage');
    }

    public function forceDelete(User $user, Website $website): bool
    {
        return $user->can('websites.manage');
    }
}
