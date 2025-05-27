<?php
// database/factories/VisitorLogFactory.php

namespace Database\Factories;

use App\Models\VisitorLog;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorLogFactory extends Factory
{
    protected $model = VisitorLog::class;

    public function definition()
    {
        return [
            'registration_id' => Registration::factory(),
            'action'          => $this->faker->randomElement([
                                            VisitorLog::ACTION_CHECKIN,
                                            VisitorLog::ACTION_CHECKOUT,
                                        ]),   
            'admin_note'      => $this->faker->optional()->sentence(),
            'created_by'      => User::factory(),
            'visited_at'      => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
