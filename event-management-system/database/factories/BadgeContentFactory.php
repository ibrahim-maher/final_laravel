<?php
// database/factories/BadgeContentFactory.php

namespace Database\Factories;

use App\Models\BadgeContent;
use App\Models\BadgeTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;  // add this


class BadgeContentFactory extends Factory
{
    protected $model = BadgeContent::class;

    public function definition()
    {
        return [
            'template_id'  => BadgeTemplate::factory(),
            'field_name'   => $this->faker->randomElement(array_keys(BadgeContent::FIELD_CHOICES)),
            'position_x'   => $this->faker->randomFloat(2, 0, 10),
            'position_y'   => $this->faker->randomFloat(2, 0, 10),
            'font_size'    => $this->faker->numberBetween(8, 36),
            'font_color'   => $this->faker->hexColor(),
            'font_family'  => $this->faker->randomElement(array_keys(BadgeTemplate::FONT_CHOICES)),
            'is_bold'      => $this->faker->boolean(20),
            'is_italic'    => $this->faker->boolean(10),
            'image_width'  => $this->faker->randomFloat(2, 1, 5),
            'image_height' => $this->faker->randomFloat(2, 1, 5),
            'created_by' => User::factory(),
        ];
    }
}
