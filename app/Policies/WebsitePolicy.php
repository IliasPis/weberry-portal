<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Website;

class WebsitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('websites.manage');
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
