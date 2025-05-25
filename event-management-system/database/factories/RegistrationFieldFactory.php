<?php
// database/factories/RegistrationFieldFactory.php

namespace Database\Factories;

use App\Models\RegistrationField;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;  // add this


class RegistrationFieldFactory extends Factory
{
    protected $model = RegistrationField::class;

    public function definition()
    {
        return [
            'event_id'   => Event::factory(),
            'field_name' => $this->faker->word(),
            'field_type' => $this->faker->randomElement(['text', 'number', 'email', 'date']),
            'order'      => $this->faker->numberBetween(1, 10),
        ];
    }
}
