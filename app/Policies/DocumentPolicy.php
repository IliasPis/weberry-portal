<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.manage') || $user->client_id !== null;
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->can('documents.manage')) {
            return true;
        }

        return $user->client_id === $document->client_id && $document->visible_to_client;
    }

    public function create(User $user): bool
    {
        return $user->can('documents.manage');
    }

    public function update(User $user, Document $document): bool
    {
        return $user->can('documents.manage');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('documents.manage');
    }
}
