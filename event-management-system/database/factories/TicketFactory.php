<?php
// database/factories/TicketFactory.php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;  // add this


class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition()
    {
        return [
            'event_id' => Event::factory(),
            'name'     => $this->faker->word(),
            'price'    => $this->faker->randomFloat(2, 5, 500),
            'quantity' => $this->faker->numberBetween(10, 200),
            'created_by' => User::factory(),
        ];
    }
}
