<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'category' => 'general_question',
            'priority' => 'normal',
            'status' => 'new',
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'created_by_user_id' => User::factory(),
        ];
    }
}
