<?php

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('a client-side user cannot reach the admin clients list', function () {
    $client = User::factory()->create(['client_id' => Client::factory()->create()->id]);
    $client->assignRole('client');

    $this->actingAs($client)->get(route('admin.clients.index'))->assertRedirect(route('client.dashboard'));
});

test('an admin can list clients and search by company name', function () {
    Client::factory()->create(['company_name' => 'ABC Fishing Ltd.']);
    Client::factory()->create(['company_name' => 'Unrelated Co']);

    $response = $this->actingAs($this->admin)->get(route('admin.clients.index', ['search' => 'ABC Fishing']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Clients/Index')
        ->has('clients.data', 1)
        ->where('clients.data.0.company_name', 'ABC Fishing Ltd.')
    );
});

test('an admin can create a client', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.clients.store'), [
        'company_name' => 'New Client Ltd.',
        'status' => 'lead',
        'priority' => 'normal',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('clients', ['company_name' => 'New Client Ltd.', 'status' => 'lead']);
});

test('creating a client requires a unique vat number', function () {
    Client::factory()->create(['vat_number' => 'EL123456789']);

    $response = $this->actingAs($this->admin)->post(route('admin.clients.store'), [
        'company_name' => 'Duplicate VAT Co',
        'vat_number' => 'EL123456789',
        'status' => 'lead',
        'priority' => 'normal',
    ]);

    $response->assertSessionHasErrors('vat_number');
});

test('an admin can update a client', function () {
    $client = Client::factory()->create(['company_name' => 'Old Name']);

    $response = $this->actingAs($this->admin)->put(route('admin.clients.update', $client), [
        'company_name' => 'New Name',
        'status' => $client->status,
        'priority' => $client->priority,
    ]);

    $response->assertRedirect();
    expect($client->fresh()->company_name)->toBe('New Name');
});

test('an admin can archive a client', function () {
    $client = Client::factory()->create(['status' => 'active']);

    $this->actingAs($this->admin)->patch(route('admin.clients.archive', $client))->assertRedirect();

    expect($client->fresh()->status)->toBe('archived');
});

test('a role without clients.manage cannot create a client', function () {
    // staff normally has clients.manage; revoke it at the role level (not
    // the user, since permissions here flow through the role) to prove the
    // FormRequest's authorize() check is actually enforced, not bypassed.
    Role::findByName('staff', 'web')->revokePermissionTo('clients.manage');

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $response = $this->actingAs($staff)->post(route('admin.clients.store'), [
        'company_name' => 'Should Fail',
        'status' => 'lead',
        'priority' => 'normal',
    ]);

    $response->assertForbidden();
});
