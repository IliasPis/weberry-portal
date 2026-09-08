<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invoices.manage') || $user->client_id !== null;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.manage') || $user->client_id === $invoice->client_id;
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.manage');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.manage');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.manage');
    }
}
