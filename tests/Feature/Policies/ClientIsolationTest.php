<?php

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->clientA = Client::factory()->create();
    $this->clientB = Client::factory()->create();

    $this->websiteA = Website::factory()->for($this->clientA)->create();
    $this->websiteB = Website::factory()->for($this->clientB)->create();

    $serviceType = ServiceType::create(['name' => 'Hosting', 'category' => 'hosting']);
    $this->serviceA = Service::factory()->for($this->clientA)->for($this->websiteA)->create([
        'service_type_id' => $serviceType->id,
    ]);
    $this->serviceB = Service::factory()->for($this->clientB)->for($this->websiteB)->create([
        'service_type_id' => $serviceType->id,
    ]);

    $this->userA = User::factory()->create(['client_id' => $this->clientA->id]);
    $this->userA->assignRole('client');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('a client user can view their own client, website, and service', function () {
    expect($this->userA->can('view', $this->clientA))->toBeTrue();
    expect($this->userA->can('view', $this->websiteA))->toBeTrue();
    expect($this->userA->can('view', $this->serviceA))->toBeTrue();
});

test('a client user cannot view another client\'s client, website, or service', function () {
    expect($this->userA->can('view', $this->clientB))->toBeFalse();
    expect($this->userA->can('view', $this->websiteB))->toBeFalse();
    expect($this->userA->can('view', $this->serviceB))->toBeFalse();
});

test('a client user can never list clients, websites, or services', function () {
    expect($this->userA->can('viewAny', Client::class))->toBeFalse();
    expect($this->userA->can('viewAny', Website::class))->toBeFalse();
    expect($this->userA->can('viewAny', Service::class))->toBeFalse();
});

test('a client user can never manage any client, website, or service', function () {
    expect($this->userA->can('create', Client::class))->toBeFalse();
    expect($this->userA->can('update', $this->clientA))->toBeFalse();
    expect($this->userA->can('update', $this->websiteA))->toBeFalse();
    expect($this->userA->can('update', $this->serviceA))->toBeFalse();
    expect($this->userA->can('delete', $this->clientA))->toBeFalse();
});

test('an admin can view and manage any client, website, or service', function () {
    expect($this->admin->can('viewAny', Client::class))->toBeTrue();
    expect($this->admin->can('view', $this->clientA))->toBeTrue();
    expect($this->admin->can('view', $this->clientB))->toBeTrue();
    expect($this->admin->can('view', $this->websiteA))->toBeTrue();
    expect($this->admin->can('view', $this->serviceB))->toBeTrue();
    expect($this->admin->can('update', $this->clientA))->toBeTrue();
    expect($this->admin->can('create', Client::class))->toBeTrue();
});
