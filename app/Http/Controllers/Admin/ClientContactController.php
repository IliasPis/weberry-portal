<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientContactController extends Controller
{
    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'contact_title' => ['required', Rule::in(['owner', 'accountant', 'marketing', 'employee', 'other'])],
            'can_view_billing' => ['boolean'],
            'can_view_invoices' => ['boolean'],
            'can_view_requests' => ['boolean'],
            'can_view_documents' => ['boolean'],
            'can_create_requests' => ['boolean'],
            'can_manage_account' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'client_id' => $client->id,
            'locale' => 'el',
        ]);
        $user->assignRole('client_contact');

        ClientContact::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'contact_title' => $validated['contact_title'],
            'is_primary' => false,
            'can_view_billing' => $validated['can_view_billing'] ?? false,
            'can_view_invoices' => $validated['can_view_invoices'] ?? false,
            'can_view_requests' => $validated['can_view_requests'] ?? true,
            'can_view_documents' => $validated['can_view_documents'] ?? true,
            'can_create_requests' => $validated['can_create_requests'] ?? true,
            'can_manage_account' => $validated['can_manage_account'] ?? false,
        ]);

        return back();
    }

    public function resetPassword(Request $request, ClientContact $contact): RedirectResponse
    {
        $this->authorize('update', $contact->client);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $contact->user->update(['password' => $validated['password']]);

        return back();
    }
}
