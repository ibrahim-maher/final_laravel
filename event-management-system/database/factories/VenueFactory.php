<?php
// database/factories/VenueFactory.php

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;  // add this


class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition()
    {
        return [
            'name'     => $this->faker->company(),
            'address'  => $this->faker->address(),
            'capacity' => $this->faker->numberBetween(10, 1000),
        ];
    }
}
