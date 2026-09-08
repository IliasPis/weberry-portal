<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use App\Support\PortalRoles;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(PortalRoles::ADMIN_SIDE) || $user->hasAnyRole(PortalRoles::CLIENT_SIDE);
    }

    /**
     * Admin sees everything (internal included); a client only ever sees
     * a shared/system note belonging to their own client - never internal,
     * never someone else's.
     */
    public function view(User $user, Note $note): bool
    {
        if ($user->hasAnyRole(PortalRoles::ADMIN_SIDE)) {
            return true;
        }

        return $user->client_id === $note->client_id && $note->visibility !== 'internal';
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(PortalRoles::ADMIN_SIDE) || $user->hasAnyRole(PortalRoles::CLIENT_SIDE);
    }

    public function update(User $user, Note $note): bool
    {
        return $user->hasAnyRole(PortalRoles::ADMIN_SIDE);
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->hasAnyRole(PortalRoles::ADMIN_SIDE);
    }
}
