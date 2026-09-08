<?php

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->client = Client::factory()->create();
    $this->website = Website::factory()->for($this->client)->create();
    $this->category = TicketCategory::create(['name' => 'Bug', 'creates_task' => true]);

    $this->clientUser = User::factory()->create(['client_id' => $this->client->id]);
    $this->clientUser->assignRole('client');
    ClientContact::create([
        'client_id' => $this->client->id,
        'user_id' => $this->clientUser->id,
        'contact_title' => 'owner',
        'is_primary' => true,
        'can_create_requests' => true,
    ]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('a client can create a request for their own website', function () {
    $response = $this->actingAs($this->clientUser)->post(route('client.requests.store'), [
        'website_id' => $this->website->id,
        'ticket_category_id' => $this->category->id,
        'priority' => 'high',
        'title' => 'Checkout is broken',
        'description' => 'Customers cannot complete checkout on the site.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'client_id' => $this->client->id,
        'website_id' => $this->website->id,
        'title' => 'Checkout is broken',
        'status' => 'new',
    ]);

    $ticket = Ticket::firstWhere('title', 'Checkout is broken');
    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'message_type' => 'client_message',
    ]);
});

test('a client contact without can_create_requests cannot open a ticket', function () {
    $restricted = User::factory()->create(['client_id' => $this->client->id]);
    $restricted->assignRole('client_contact');
    ClientContact::create([
        'client_id' => $this->client->id,
        'user_id' => $restricted->id,
        'contact_title' => 'accountant',
        'can_create_requests' => false,
    ]);

    $response = $this->actingAs($restricted)->post(route('client.requests.store'), [
        'ticket_category_id' => $this->category->id,
        'priority' => 'normal',
        'title' => 'Should be blocked',
        'description' => 'This should not be allowed.',
    ]);

    $response->assertForbidden();
});

test('a client cannot view another client\'s ticket', function () {
    $otherTicket = Ticket::factory()->create();

    $this->actingAs($this->clientUser)
        ->get(route('client.requests.show', $otherTicket))
        ->assertForbidden();
});

test('an internal note is never visible to the client, only to admin-side users', function () {
    $ticket = Ticket::factory()->for($this->client)->for($this->website)->create([
        'created_by_user_id' => $this->clientUser->id,
    ]);

    $this->actingAs($this->admin)->post(route('admin.tickets.messages.store', $ticket), [
        'body' => 'This looks like a bug in the custom checkout plugin.',
        'is_internal' => true,
    ])->assertRedirect();

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'message_type' => 'internal_note',
    ]);

    // Client-facing show response must not contain the internal note.
    $clientResponse = $this->actingAs($this->clientUser)->get(route('client.requests.show', $ticket));
    $clientResponse->assertOk();
    $clientResponse->assertInertia(fn ($page) => $page
        ->component('Client/Requests/Show')
        ->where('ticket.visible_messages', fn ($messages) => collect($messages)
            ->doesntContain(fn ($m) => $m['message_type'] === 'internal_note'))
    );

    // The admin-facing show response does include it.
    $adminResponse = $this->actingAs($this->admin)->get(route('admin.tickets.show', $ticket));
    $adminResponse->assertInertia(fn ($page) => $page
        ->component('Admin/Tickets/Show')
        ->where('ticket.messages', fn ($messages) => collect($messages)
            ->contains(fn ($m) => $m['message_type'] === 'internal_note'))
    );
});

test('a client reply is always saved as client_message even if is_internal is sent', function () {
    $ticket = Ticket::factory()->for($this->client)->for($this->website)->create([
        'created_by_user_id' => $this->clientUser->id,
    ]);

    $this->actingAs($this->clientUser)->post(route('client.requests.messages.store', $ticket), [
        'body' => 'Any update on this?',
        'is_internal' => true,
    ])->assertRedirect();

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'body' => 'Any update on this?',
        'message_type' => 'client_message',
    ]);
});

test('an admin can update ticket status and priority', function () {
    $ticket = Ticket::factory()->for($this->client)->for($this->website)->create([
        'created_by_user_id' => $this->clientUser->id,
        'status' => 'new',
    ]);

    $this->actingAs($this->admin)->put(route('admin.tickets.update', $ticket), [
        'status' => 'in_progress',
        'priority' => 'urgent',
    ])->assertRedirect();

    expect($ticket->fresh())
        ->status->toBe('in_progress')
        ->priority->toBe('urgent');
});
