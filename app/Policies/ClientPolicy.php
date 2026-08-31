<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Only the admin side lists clients - a client user never sees a list
     * of clients, including their own (they just land straight on their
     * own dashboard).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('clients.viewAny');
    }

    /**
     * An admin with permission can view any client; a client-side user can
     * only ever view their own client record (e.g. for an Account page).
     */
    public function view(User $user, Client $client): bool
    {
        return $user->can('clients.viewAny') || $user->client_id === $client->id;
    }

    public function create(User $user): bool
    {
        return $user->can('clients.manage');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->can('clients.manage');
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->can('clients.manage');
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->can('clients.manage');
    }

    public function forceDelete(User $user, Client $client): bool
    {
        return $user->can('clients.manage');
    }
}
