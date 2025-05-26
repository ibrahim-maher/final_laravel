<?php
// database/factories/RegistrationFactory.php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;


class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition()
    {
        return [
            'event_id'         => Event::factory(),
            'user_id'          => User::factory(),
            'ticket_type_id'   => Ticket::factory(),
            'registration_data'=> ['notes' => $this->faker->sentence()],
            'status'           => $this->faker->randomElement(array_keys(Registration::STATUSES)),
        ];
    }
}
