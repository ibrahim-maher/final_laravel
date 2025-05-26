<?php
// database/factories/EventFactory.php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Venue;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;  // add this



class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('+1 days', '+30 days');
        return [
            'name'         => $this->faker->sentence(3),
            'description'  => $this->faker->paragraph(),
            'start_date'   => $start,
            'end_date'     => $this->faker->dateTimeBetween($start, '+60 days'),
            'venue_id'     => Venue::factory(),
            'category_id'  => Category::factory(),
            'is_active'    => $this->faker->boolean(70),
            'logo'         => null, // or ->image('logos', 200, 200)
        ];
    }
}
