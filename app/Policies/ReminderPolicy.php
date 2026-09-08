<?php

namespace App\Policies;

use App\Models\Reminder;
use App\Models\User;
use App\Support\PortalRoles;

class ReminderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(PortalRoles::ADMIN_SIDE) || $user->hasAnyRole(PortalRoles::CLIENT_SIDE);
    }

    public function view(User $user, Reminder $reminder): bool
    {
        if ($user->hasAnyRole(PortalRoles::ADMIN_SIDE)) {
            return true;
        }

        return $user->client_id !== null && $user->client_id === $reminder->client_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(PortalRoles::ADMIN_SIDE) || $user->hasAnyRole(PortalRoles::CLIENT_SIDE);
    }

    public function update(User $user, Reminder $reminder): bool
    {
        if ($user->hasAnyRole(PortalRoles::ADMIN_SIDE)) {
            return true;
        }

        return $user->client_id !== null
            && $user->client_id === $reminder->client_id
            && $reminder->owner_type === 'client';
    }

    public function delete(User $user, Reminder $reminder): bool
    {
        return $this->update($user, $reminder);
    }
}
