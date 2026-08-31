<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'website_id' => Website::factory(),
            'service_type_id' => ServiceType::factory(),
            'name' => fake()->words(2, true),
            'provider' => fake()->company(),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'expiration_date' => fake()->dateTimeBetween('now', '+1 year'),
            'billing_cycle' => 'yearly',
            'internal_cost' => fake()->randomFloat(2, 10, 100),
            'client_price' => fake()->randomFloat(2, 50, 300),
            'auto_renewal' => true,
            'status' => 'active',
        ];
    }
}
