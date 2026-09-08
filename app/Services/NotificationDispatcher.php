<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\PortalRoles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notifiable;

/**
 * Centralizes the two recurring notification patterns - "tell the admin
 * side something happened for this client" and "tell this client's users
 * something happened" - so every event (ticket, note, reminder, invoice,
 * maintenance) triggers the same way instead of each controller reaching
 * for ->notify() with its own logic.
 */
class NotificationDispatcher
{
    public function notifyAdmins(string $type, string $title, ?string $body = null, ?string $url = null): void
    {
        /** @var Collection<int, User&Notifiable> $admins */
        $admins = User::role(PortalRoles::ADMIN_SIDE)->get();

        foreach ($admins as $admin) {
            $admin->notify(new GenericNotification($type, $title, $body, $url));
        }
    }

    public function notifyClient(Client $client, string $type, string $title, ?string $body = null, ?string $url = null): void
    {
        /** @var Collection<int, User&Notifiable> $users */
        $users = User::where('client_id', $client->id)->get();

        foreach ($users as $user) {
            $user->notify(new GenericNotification($type, $title, $body, $url));
        }
    }
}
