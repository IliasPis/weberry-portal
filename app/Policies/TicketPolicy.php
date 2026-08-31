<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Support\PortalRoles;

class TicketPolicy
{
    /**
     * Both sides "list" tickets - admins see everything they have
     * permission for, clients only ever see their own client's tickets
     * (enforced by scoping the query, not by this Policy).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('tickets.viewAny') || $user->hasAnyRole(PortalRoles::CLIENT_SIDE);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.viewAny') || $user->client_id === $ticket->client_id;
    }

    /**
     * An admin/staff member can always open a ticket on a client's behalf.
     * A client-side user can only if their own contact record allows it -
     * this is the Accountant-can't-file-technical-requests style control.
     */
    public function create(User $user): bool
    {
        if ($user->can('tickets.viewAny')) {
            return true;
        }

        return $user->hasAnyRole(PortalRoles::CLIENT_SIDE)
            && (bool) $user->clientContact?->can_create_requests;
    }

    /**
     * Status/priority/assignment changes are admin-only - a client can
     * only add messages to the conversation, never edit ticket metadata.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.viewAny');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.viewAny');
    }
}
