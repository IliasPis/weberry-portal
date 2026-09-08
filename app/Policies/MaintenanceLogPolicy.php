<?php

namespace App\Policies;

use App\Models\MaintenanceLog;
use App\Models\User;

class MaintenanceLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('maintenance.manage') || $user->client_id !== null;
    }

    public function view(User $user, MaintenanceLog $maintenanceLog): bool
    {
        if ($user->can('maintenance.manage')) {
            return true;
        }

        return $user->client_id === $maintenanceLog->website?->client_id && $maintenanceLog->client_visible;
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.manage');
    }

    public function update(User $user, MaintenanceLog $maintenanceLog): bool
    {
        return $user->can('maintenance.manage');
    }

    public function delete(User $user, MaintenanceLog $maintenanceLog): bool
    {
        return $user->can('maintenance.manage');
    }
}
