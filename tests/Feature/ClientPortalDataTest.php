<?php

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->client = Client::factory()->create();
    $this->website = Website::factory()->for($this->client)->create([
        'server' => 'srv-secret-01',
        'server_ip' => '10.0.0.99',
        'database_name' => 'secret_db',
        'internal_notes' => 'Never show this to the client.',
    ]);

    $serviceType = ServiceType::create(['name' => 'Hosting', 'category' => 'hosting']);
    $this->service = Service::factory()->for($this->client)->for($this->website)->create([
        'service_type_id' => $serviceType->id,
        'internal_cost' => 42.00,
        'client_price' => 150.00,
        'notes_internal' => 'Margin is huge on this one, never mention it.',
    ]);

    $this->clientUser = User::factory()->create(['client_id' => $this->client->id]);
    $this->clientUser->assignRole('client');
});

test('a client can see their own website list without any internal/technical fields', function () {
    $response = $this->actingAs($this->clientUser)->get(route('client.websites.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Client/Websites/Index')
        ->has('websites.0', fn ($site) => $site
            ->where('domain', $this->website->domain)
            ->etc()
        )
    );

    // The raw response must never contain the internal fields at all -
    // not hidden by the frontend, never sent by the server in the first place.
    $content = $response->getContent();
    expect($content)->not->toContain('srv-secret-01');
    expect($content)->not->toContain('10.0.0.99');
    expect($content)->not->toContain('secret_db');
    expect($content)->not->toContain('Never show this to the client');
});

test('a client can see their own service with client_price but never internal_cost or internal notes', function () {
    $response = $this->actingAs($this->clientUser)->get(route('client.services.index'));

    $response->assertOk();

    $content = $response->getContent();
    expect($content)->toContain('150'); // client_price is fine
    // internal_cost (42.00) must never appear - checked as the decimal-cast
    // serialized form, not the bare digits, since those could coincidentally
    // match an unrelated record's autoincrement id.
    expect($content)->not->toContain('42.00');
    expect($content)->not->toContain('Margin is huge');
});

test('a client cannot see another client\'s websites in their own list', function () {
    Website::factory()->create(['domain' => 'someone-elses-site.gr']);

    $response = $this->actingAs($this->clientUser)->get(route('client.websites.index'));

    $response->assertOk()->assertDontSee('someone-elses-site.gr');
});

test('a client cannot open another client\'s website detail page', function () {
    $otherWebsite = Website::factory()->create();

    $this->actingAs($this->clientUser)
        ->get(route('client.websites.show', $otherWebsite))
        ->assertForbidden();
});
