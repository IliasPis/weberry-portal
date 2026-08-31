<?php

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->client = Client::factory()->create();
    $this->website = Website::factory()->for($this->client)->create();
});

test('an admin can create a hosting service and its type-specific detail row', function () {
    $hostingType = ServiceType::create(['name' => 'Hosting', 'category' => 'hosting']);

    $response = $this->actingAs($this->admin)->post(route('admin.services.store'), [
        'client_id' => $this->client->id,
        'website_id' => $this->website->id,
        'service_type_id' => $hostingType->id,
        'name' => 'WooCommerce Pro Hosting',
        'billing_cycle' => 'yearly',
        'status' => 'active',
        'client_price' => 150,
        'hosting_package' => 'WooCommerce Pro',
        'hosting_server' => 'srv-gr-04',
    ]);

    $response->assertRedirect();

    $service = Service::where('name', 'WooCommerce Pro Hosting')->firstOrFail();
    $this->assertDatabaseHas('service_hosting_details', [
        'service_id' => $service->id,
        'package' => 'WooCommerce Pro',
        'server' => 'srv-gr-04',
    ]);
    $this->assertDatabaseMissing('service_domain_details', ['service_id' => $service->id]);
});

test('an admin can create a domain service and its type-specific detail row', function () {
    $domainType = ServiceType::create(['name' => 'Domain', 'category' => 'domain']);

    $this->actingAs($this->admin)->post(route('admin.services.store'), [
        'client_id' => $this->client->id,
        'website_id' => $this->website->id,
        'service_type_id' => $domainType->id,
        'name' => 'aboutfishing.gr Domain',
        'billing_cycle' => 'yearly',
        'status' => 'active',
        'domain_registrar' => 'Weberry Registrar',
    ])->assertRedirect();

    $service = Service::where('name', 'aboutfishing.gr Domain')->firstOrFail();
    $this->assertDatabaseHas('service_domain_details', [
        'service_id' => $service->id,
        'registrar' => 'Weberry Registrar',
    ]);
});

test('an admin can list services filtered by search across client and website', function () {
    $type = ServiceType::create(['name' => 'Hosting', 'category' => 'hosting']);
    Service::factory()->for($this->client)->for($this->website)->create([
        'service_type_id' => $type->id,
        'name' => 'Findable Service',
    ]);
    Service::factory()->create(['service_type_id' => $type->id, 'name' => 'Unrelated Service']);

    $response = $this->actingAs($this->admin)->get(route('admin.services.index', ['search' => 'Findable']));

    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Services/Index')
        ->has('services.data', 1)
        ->where('services.data.0.name', 'Findable Service')
    );
});

test('a client-side user is bounced away from the admin services routes', function () {
    $client = User::factory()->create(['client_id' => Client::factory()->create()->id]);
    $client->assignRole('client');

    $this->actingAs($client)->get(route('admin.services.index'))->assertRedirect(route('client.dashboard'));
});
