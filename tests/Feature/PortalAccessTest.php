<?php

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guests are redirected to login from both portals', function () {
    $this->get('/admin/dashboard')->assertRedirect(route('login'));
    $this->get('/portal/dashboard')->assertRedirect(route('login'));
});

test('an admin-side user reaches the admin dashboard', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
});

test('an admin-side user is bounced away from the client portal', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)->get('/portal/dashboard')->assertRedirect(route('admin.dashboard'));
});

test('a client-side user reaches the client portal', function () {
    $company = Client::factory()->create();
    $client = User::factory()->create(['email_verified_at' => now(), 'client_id' => $company->id]);
    $client->assignRole('client');

    $this->actingAs($client)->get('/portal/dashboard')->assertOk();
});

test('a client-side user is bounced away from the admin portal', function () {
    $client = User::factory()->create(['email_verified_at' => now()]);
    $client->assignRole('client');

    $this->actingAs($client)->get('/admin/dashboard')->assertRedirect(route('client.dashboard'));
});

test('the dashboard redirector sends each role to its own portal', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('staff');
    $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));

    $client = User::factory()->create(['email_verified_at' => now()]);
    $client->assignRole('client_contact');
    $this->actingAs($client)->get('/dashboard')->assertRedirect(route('client.dashboard'));
});
