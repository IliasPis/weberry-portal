<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.manage') || $user->client_id !== null;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->can('tasks.manage')) {
            return true;
        }

        return $user->client_id === $task->client_id && $task->client_visible;
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.manage');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.manage');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.manage');
    }
}
