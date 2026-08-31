<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.manage');
    }

    /**
     * An admin with permission can view any service; a client-side user
     * can only view services that belong to their own client. Financial
     * fields (internal_cost, margin) are never exposed here - that's a
     * Resource/serializer concern, not this Policy's job.
     */
    public function view(User $user, Service $service): bool
    {
        return $user->can('services.manage') || $user->client_id === $service->client_id;
    }

    public function create(User $user): bool
    {
        return $user->can('services.manage');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('services.manage');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->can('services.manage');
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->can('services.manage');
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $user->can('services.manage');
    }
}
