<?php
// database/factories/BadgeTemplateFactory.php

namespace Database\Factories;

use App\Models\BadgeTemplate;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeTemplateFactory extends Factory
{
    protected $model = BadgeTemplate::class;

    public function definition()
    {
        return [
            'ticket_id'        => Ticket::factory(),
            'name'             => $this->faker->word(),
            'width'            => $this->faker->randomFloat(2, 5, 20),
            'height'           => $this->faker->randomFloat(2, 5, 20),
            'background_image' => null, // or ->image('templates', 400, 300)
            'created_by'       => User::factory(),
            'default_font'     => $this->faker->randomElement(array_keys(BadgeTemplate::FONT_CHOICES)),
        ];
    }
}
