<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Website>
 */
class WebsiteFactory extends Factory
{
    protected $model = Website::class;

    public function definition(): array
    {
        $domain = fake()->unique()->domainName();

        return [
            'client_id' => Client::factory(),
            'name' => fake()->company(),
            'domain' => $domain,
            'production_url' => "https://{$domain}",
            'cms' => 'wordpress',
            'status' => 'active',
        ];
    }
}
