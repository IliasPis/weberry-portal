<?php

use App\Models\Client;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('a client-side user cannot reach the admin websites list', function () {
    $client = User::factory()->create(['client_id' => Client::factory()->create()->id]);
    $client->assignRole('client');

    $this->actingAs($client)->get(route('admin.websites.index'))->assertRedirect(route('client.dashboard'));
});

test('an admin can list and search websites by domain', function () {
    Website::factory()->create(['domain' => 'aboutfishing.gr']);
    Website::factory()->create(['domain' => 'unrelated.gr']);

    $response = $this->actingAs($this->admin)->get(route('admin.websites.index', ['search' => 'aboutfishing']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Websites/Index')
        ->has('websites.data', 1)
        ->where('websites.data.0.domain', 'aboutfishing.gr')
    );
});

test('an admin can create a website for a client', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.websites.store'), [
        'client_id' => $client->id,
        'name' => 'Test Site',
        'domain' => 'test-site.gr',
        'status' => 'active',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('websites', ['domain' => 'test-site.gr', 'client_id' => $client->id]);
});

test('creating a website requires a unique domain', function () {
    Website::factory()->create(['domain' => 'taken.gr']);
    $client = Client::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.websites.store'), [
        'client_id' => $client->id,
        'name' => 'Test Site',
        'domain' => 'taken.gr',
        'status' => 'active',
    ]);

    $response->assertSessionHasErrors('domain');
});

test('a client-side user is bounced away before reaching any admin website route', function () {
    // The portal.admin middleware gates the whole /admin/* group, so a
    // client-side user never even reaches the Policy check inside the
    // controller - they're redirected to their own portal first.
    $ownClient = Client::factory()->create();
    $otherWebsite = Website::factory()->create();

    $user = User::factory()->create(['client_id' => $ownClient->id]);
    $user->assignRole('client');

    $this->actingAs($user)
        ->get(route('admin.websites.show', $otherWebsite))
        ->assertRedirect(route('client.dashboard'));
});

test('a staff member without websites.manage cannot view a website via the Policy', function () {
    Role::findByName('staff', 'web')->revokePermissionTo('websites.manage');

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $website = Website::factory()->create();

    $this->actingAs($staff)->get(route('admin.websites.show', $website))->assertForbidden();
});
